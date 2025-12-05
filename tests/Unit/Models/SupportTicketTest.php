<?php

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('generates references with SUP prefix and uppercase random tokens', function (): void {
    $reference = SupportTicket::generateReference();

    expect($reference)->toMatch('/^SUP-[A-Z0-9]{8}$/');
});

it('assigns default reference and open status on creation', function (): void {
    $user = User::factory()->create();

    $ticket = new SupportTicket([
        'subject' => 'Need help with my plan',
    ]);
    $ticket->user()->associate($user);
    $ticket->save();

    expect($ticket->reference)->toMatch('/^SUP-[A-Z0-9]{8}$/')
        ->and($ticket->status)->toBe(SupportTicket::STATUS_OPEN);
});

it('preserves existing reference and status values when provided', function (): void {
    $user = User::factory()->create();

    $ticket = new SupportTicket([
        'subject' => 'Need a follow-up',
        'reference' => 'SUP-CUSTOM',
        'status' => SupportTicket::STATUS_WAITING_USER,
    ]);
    $ticket->user()->associate($user);
    $ticket->save();

    expect($ticket->reference)->toBe('SUP-CUSTOM')
        ->and($ticket->status)->toBe(SupportTicket::STATUS_WAITING_USER);
});

it('filters tickets by status only when a value is provided', function (): void {
    $user = User::factory()->create();

    makeSupportTicket($user, ['status' => SupportTicket::STATUS_OPEN]);
    $waiting = makeSupportTicket($user, ['status' => SupportTicket::STATUS_WAITING_USER]);
    makeSupportTicket($user, ['status' => SupportTicket::STATUS_RESOLVED]);

    expect(SupportTicket::withStatus(null)->count())->toBe(3);

    $filtered = SupportTicket::withStatus(SupportTicket::STATUS_WAITING_USER)->get();

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->is($waiting))->toBeTrue();
});

it('exposes human readable labels for each status', function (): void {
    $ticket = new SupportTicket(['status' => SupportTicket::STATUS_OPEN]);
    $waiting = new SupportTicket(['status' => SupportTicket::STATUS_WAITING_USER]);
    $resolved = new SupportTicket(['status' => SupportTicket::STATUS_RESOLVED]);
    $custom = new SupportTicket(['status' => 'escalated']);

    expect($ticket->statusLabel())->toBe(__('Open'))
        ->and($waiting->statusLabel())->toBe(__('Waiting for you'))
        ->and($resolved->statusLabel())->toBe(__('Resolved'))
        ->and($custom->statusLabel())->toBe('Escalated');
});

it('provides ui metadata for the supported statuses', function (): void {
    $metadata = SupportTicket::statusMetadata();

    expect($metadata)->toBe([
        SupportTicket::STATUS_OPEN => [
            'label' => __('Waiting for support'),
            'dot' => 'bg-indigo-500',
            'badge' => 'border border-indigo-200 bg-indigo-50 text-indigo-700',
        ],
        SupportTicket::STATUS_WAITING_USER => [
            'label' => __('Waiting for you'),
            'dot' => 'bg-amber-500',
            'badge' => 'border border-amber-200 bg-amber-50 text-amber-700',
        ],
        SupportTicket::STATUS_RESOLVED => [
            'label' => __('Resolved'),
            'dot' => 'bg-emerald-500',
            'badge' => 'border border-emerald-200 bg-emerald-50 text-emerald-700',
        ],
    ]);
});

it('exposes messages ordered by newest identifier first', function (): void {
    $ticket = makeSupportTicket();

    $first = createSupportTicketMessage($ticket, [
        'message' => 'Initial inquiry',
    ]);
    $second = createSupportTicketMessage($ticket, [
        'message' => 'Follow-up context',
    ]);

    $messages = $ticket->messages()->get();

    expect($messages->pluck('id')->all())->toBe([$second->id, $first->id]);
});

it('syncs status to waiting for user responses when team replies last', function (): void {
    $ticket = makeSupportTicket();

    $timestamp = Carbon::parse('2024-01-05 12:00:00');
    $message = new SupportTicketMessage([
        'author_type' => 'team',
        'message' => 'We are on it',
    ]);
    $message->created_at = $timestamp;

    $ticket->syncStatusFromMessage($message);
    $ticket->refresh();

    expect($ticket->status)->toBe(SupportTicket::STATUS_WAITING_USER)
        ->and($ticket->last_message_at)->not->toBeNull()
        ->and($ticket->last_message_at->equalTo($timestamp))->toBeTrue();
});

it('syncs status back to open after the user replies', function (): void {
    $ticket = makeSupportTicket(attributes: [
        'status' => SupportTicket::STATUS_WAITING_USER,
        'last_message_at' => Carbon::parse('2024-01-01 08:00:00'),
    ]);

    $timestamp = Carbon::parse('2024-01-06 09:30:00');
    $message = new SupportTicketMessage([
        'author_type' => 'user',
        'message' => 'Any news?',
    ]);
    $message->created_at = $timestamp;

    $ticket->syncStatusFromMessage($message);
    $ticket->refresh();

    expect($ticket->status)->toBe(SupportTicket::STATUS_OPEN)
        ->and($ticket->last_message_at->equalTo($timestamp))->toBeTrue();
});

it('keeps resolved tickets untouched when already closed', function (): void {
    $closedAt = Carbon::parse('2024-01-10 10:00:00');
    $ticket = makeSupportTicket(attributes: [
        'status' => SupportTicket::STATUS_RESOLVED,
        'last_message_at' => $closedAt,
    ]);

    $message = new SupportTicketMessage([
        'author_type' => 'team',
        'message' => 'Follow-up detail',
    ]);
    $message->created_at = Carbon::parse('2024-01-11 12:00:00');

    $ticket->syncStatusFromMessage($message);
    $ticket->refresh();

    expect($ticket->status)->toBe(SupportTicket::STATUS_RESOLVED)
        ->and($ticket->last_message_at->equalTo($closedAt))->toBeTrue();
});

it('keeps status untouched when no messages exist to sync', function (): void {
    $ticket = makeSupportTicket(attributes: [
        'status' => SupportTicket::STATUS_OPEN,
        'last_message_at' => null,
    ]);

    $ticket->syncStatusFromMessage();
    $ticket->refresh();

    expect($ticket->status)->toBe(SupportTicket::STATUS_OPEN)
        ->and($ticket->last_message_at)->toBeNull();
});

it('loads the latest stored message when none is injected', function (): void {
    $ticket = makeSupportTicket();

    createSupportTicketMessage($ticket, [
        'author_type' => 'user',
        'author_name' => 'Customer',
        'created_at' => Carbon::parse('2024-01-02 09:00:00'),
        'message' => 'Initial description',
    ]);

    $latestTimestamp = Carbon::parse('2024-01-03 15:30:00');
    createSupportTicketMessage($ticket, [
        'author_type' => 'team',
        'author_name' => 'Agent',
        'user_id' => null,
        'created_at' => $latestTimestamp,
        'message' => 'Please try again',
    ]);

    $ticket->forceFill([
        'status' => SupportTicket::STATUS_OPEN,
        'last_message_at' => Carbon::parse('2024-01-01 08:00:00'),
    ])->saveQuietly();

    $ticket->syncStatusFromMessage();
    $ticket->refresh();

    expect($ticket->status)->toBe(SupportTicket::STATUS_WAITING_USER)
        ->and($ticket->last_message_at->equalTo($latestTimestamp))->toBeTrue();
});

function makeSupportTicket(?User $owner = null, array $attributes = []): SupportTicket
{
    $owner ??= User::factory()->create();

    $ticket = new SupportTicket(array_merge([
        'subject' => 'Help me understand my subscription',
        'category' => 'general',
        'reference' => SupportTicket::generateReference(),
        'status' => SupportTicket::STATUS_OPEN,
        'last_message_at' => null,
        'resolved_at' => null,
    ], $attributes));

    $ticket->user()->associate($owner);
    $ticket->save();

    return $ticket->fresh();
}

function createSupportTicketMessage(SupportTicket $ticket, array $attributes = []): SupportTicketMessage
{
    $attributes = array_merge([
        'author_type' => 'user',
        'author_name' => 'Customer',
        'message' => 'Need assistance',
        'created_at' => now(),
    ], $attributes);

    $message = new SupportTicketMessage([
        'author_type' => $attributes['author_type'],
        'author_name' => $attributes['author_name'],
        'message' => $attributes['message'],
    ]);

    $message->ticket()->associate($ticket);

    if (array_key_exists('user_id', $attributes)) {
        $message->user_id = $attributes['user_id'];
    } elseif ($attributes['author_type'] === 'user') {
        $message->user()->associate($ticket->user);
    }

    $message->created_at = $attributes['created_at'];
    $message->updated_at = $attributes['created_at'];

    $message->save();

    return $message->fresh();
}
