<?php

use App\Livewire\Support\Tickets;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates a new support ticket with the initial message', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(Tickets::class)
        ->set('subject', 'Need help with subscription')
        ->set('category', 'Billing')
        ->set('message', 'I was charged twice this month, please check.')
        ->call('createTicket')
        ->assertSet('subject', '')
        ->assertSet('category', '')
        ->assertSet('message', '')
        ->assertSet('statusFilter', SupportTicket::STATUS_OPEN)
        ->assertSet('flashMessage', __('Ticket sent successfully. We will keep you updated here.'));

    $ticket = SupportTicket::firstOrFail();

    $component->assertSet('selectedTicketId', $ticket->id);

    expect($ticket->messages)->toHaveCount(1)
        ->and($ticket->messages->first()->message)->toBe('I was charged twice this month, please check.');
});

it('sends a reply and updates ticket status and flash message', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $ticket = createSupportTicket($user, [
        'status' => SupportTicket::STATUS_WAITING_USER,
    ]);

    $component = Livewire::test(Tickets::class)
        ->set('selectedTicketId', $ticket->id)
        ->set('replyMessage', 'Thanks for the update, still experiencing this.')
        ->call('sendReply')
        ->assertSet('replyMessage', '')
        ->assertSet('statusFilter', SupportTicket::STATUS_OPEN)
        ->assertSet('flashMessage', __('Your update was sent to support.'));

    $ticket->refresh();
    $component->assertSet('selectedTicketId', $ticket->id);

    expect($ticket->messages)->toHaveCount(2)
        ->and($ticket->status)->toBe(SupportTicket::STATUS_OPEN);
});

it('closes and reopens a ticket updating status accordingly', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $ticket = createSupportTicket($user, [
        'status' => SupportTicket::STATUS_OPEN,
    ]);

    $component = Livewire::test(Tickets::class)
        ->set('selectedTicketId', $ticket->id)
        ->call('closeTicket')
        ->assertSet('statusFilter', SupportTicket::STATUS_RESOLVED)
        ->assertSet('flashMessage', __('Ticket marked as resolved.'))
        ->call('reopenTicket')
        ->assertSet('statusFilter', SupportTicket::STATUS_OPEN)
        ->assertSet('flashMessage', __('Ticket reopened and waiting for support.'));

    $ticket->refresh();
    $component->assertSet('selectedTicketId', $ticket->id);

    expect($ticket->status)->toBe(SupportTicket::STATUS_OPEN)
        ->and($ticket->resolved_at)->toBeNull();
});

it('applies category suggestions directly to the form field', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Tickets::class)
        ->call('useCategorySuggestion', 'Technical issue')
        ->assertSet('category', 'Technical issue');
});

function createSupportTicket(User $user, array $attributes = []): SupportTicket
{
    $ticket = new SupportTicket(array_merge([
        'subject' => 'Existing ticket',
        'category' => 'General',
        'status' => SupportTicket::STATUS_OPEN,
        'last_message_at' => Carbon::now(),
    ], $attributes));

    $ticket->user()->associate($user);
    $ticket->save();

    $ticket->messages()->create([
        'user_id' => $user->id,
        'author_type' => 'user',
        'message' => 'Initial message',
        'created_at' => $ticket->last_message_at,
    ]);

    return $ticket->fresh();
}
