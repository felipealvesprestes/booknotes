<?php

use App\Models\Discipline;
use App\Models\FlashcardSession;
use App\Models\Notebook;
use App\Models\Note;
use App\Models\PdfDocument;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription as CashierSubscription;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('loads user academic and library relations', function (): void {
    $user = User::factory()->create();

    $notebook = new Notebook([
        'title' => 'Notebook',
        'description' => null,
    ]);
    $notebook->user()->associate($user);
    $notebook->save();

    $discipline = new Discipline([
        'title' => 'Physics',
        'description' => null,
        'notebook_id' => $notebook->id,
    ]);
    $discipline->user()->associate($user);
    $discipline->save();

    $note = new Note([
        'title' => 'Kinematics',
        'content' => 'Motion basics',
        'is_flashcard' => false,
        'discipline_id' => $discipline->id,
    ]);
    $note->user()->associate($user);
    $note->save();

    $session = new FlashcardSession([
        'status' => 'active',
        'total_cards' => 1,
        'current_index' => 0,
        'correct_count' => 0,
        'incorrect_count' => 0,
        'accuracy' => 0,
        'note_ids' => [$note->id],
        'studied_at' => now(),
        'discipline_id' => $discipline->id,
    ]);
    $session->user()->associate($user);
    $session->save();

    $pdfDocument = new PdfDocument([
        'title' => 'Cheat Sheet',
        'original_name' => 'sheet.pdf',
        'path' => 'documents/sheet.pdf',
        'size' => 2048,
    ]);
    $pdfDocument->user()->associate($user);
    $pdfDocument->save();

    $user->load('disciplines', 'notes', 'flashcardSessions', 'pdfDocuments');

    expect($user->disciplines)->toHaveCount(1)
        ->and($user->disciplines->first()->is($discipline))->toBeTrue()
        ->and($user->notes)->toHaveCount(1)
        ->and($user->notes->first()->is($note))->toBeTrue()
        ->and($user->flashcardSessions)->toHaveCount(1)
        ->and($user->flashcardSessions->first()->is($session))->toBeTrue()
        ->and($user->pdfDocuments)->toHaveCount(1)
        ->and($user->pdfDocuments->first()->is($pdfDocument))->toBeTrue();
});

it('treats lifetime accounts as active access', function (): void {
    $user = new User();
    $user->is_lifetime = true;

    expect($user->hasActiveSubscriptionOrTrial())->toBeTrue();
});

it('treats active subscriptions as active access', function (): void {
    $user = User::factory()->create(['is_lifetime' => false]);

    CashierSubscription::create([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => (string) Str::uuid(),
        'stripe_status' => 'active',
        'stripe_price' => 'price_basic',
        'quantity' => 1,
    ]);

    expect($user->fresh()->hasActiveSubscriptionOrTrial())->toBeTrue();
});

it('falls back to the generic trial period when no subscription exists', function (): void {
    $user = User::factory()->create([
        'is_lifetime' => false,
        'trial_starts_at' => now()->subDay(),
        'trial_ends_at' => now()->addDay(),
    ]);

    // Ensure subscriptions relation is treated as empty to skip cached data.
    $user->setRelation('subscriptions', new EloquentCollection());

    expect($user->hasActiveSubscriptionOrTrial())->toBeTrue();
});

it('returns the configured subscription plan name with fallback to the app name', function (): void {
    $originalStripeConfig = config('services.stripe');

    config(['services.stripe.plan_name' => 'Plano Premium']);
    config(['app.name' => 'Booknotes']);

    $user = new User();

    expect($user->subscriptionPlanName())->toBe('Plano Premium');

    config(['services.stripe' => Arr::except(config('services.stripe'), 'plan_name')]);

    expect($user->subscriptionPlanName())->toBe('Booknotes');

    config(['services.stripe' => $originalStripeConfig]);
});

it('lists only support tickets owned by the user', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $firstTicket = createSupportTicketForOwner($owner, ['subject' => 'Billing question']);
    $secondTicket = createSupportTicketForOwner($owner, ['subject' => 'Bug report']);
    createSupportTicketForOwner($otherUser, ['subject' => 'Other user ticket']);

    $owner->load('supportTickets');

    expect($owner->supportTickets)->toHaveCount(2)
        ->and($owner->supportTickets->contains(fn (SupportTicket $ticket) => $ticket->is($firstTicket)))->toBeTrue()
        ->and($owner->supportTickets->contains(fn (SupportTicket $ticket) => $ticket->is($secondTicket)))->toBeTrue()
        ->and($owner->supportTickets->contains(fn (SupportTicket $ticket) => $ticket->user_id === $otherUser->id))->toBeFalse();
});

function createSupportTicketForOwner(User $user, array $attributes = []): SupportTicket
{
    $ticket = new SupportTicket(array_merge([
        'subject' => 'Help with my account',
        'category' => 'general',
        'reference' => SupportTicket::generateReference(),
        'status' => SupportTicket::STATUS_OPEN,
    ], $attributes));

    $ticket->user()->associate($user);
    $ticket->save();

    return $ticket->fresh();
}
