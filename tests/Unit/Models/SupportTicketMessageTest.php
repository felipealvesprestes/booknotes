<?php

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('fills author name from the associated user when missing', function (): void {
    $user = User::factory()->create(['name' => 'Customer Example']);
    $ticket = createSupportTicketForMessage($user);

    $message = new SupportTicketMessage([
        'author_type' => 'user',
        'message' => 'Need some help',
    ]);

    $message->ticket()->associate($ticket);
    $message->user()->associate($user);
    $message->save();
    $message->refresh();

    expect($message->author_name)->toBe('Customer Example');
});

it('detects when a message was authored by the user', function (): void {
    $userMessage = new SupportTicketMessage(['author_type' => 'user']);
    $teamMessage = new SupportTicketMessage(['author_type' => 'team']);

    expect($userMessage->isFromUser())->toBeTrue()
        ->and($teamMessage->isFromUser())->toBeFalse();
});

function createSupportTicketForMessage(User $user, array $attributes = []): SupportTicket
{
    $ticket = new SupportTicket(array_merge([
        'subject' => 'Ticket subject',
        'category' => 'general',
        'reference' => SupportTicket::generateReference(),
        'status' => SupportTicket::STATUS_OPEN,
    ], $attributes));

    $ticket->user()->associate($user);
    $ticket->save();

    return $ticket->fresh();
}
