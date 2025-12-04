<?php

use App\Livewire\Notifications\Index as NotificationsIndex;
use App\Models\User;
use App\Notifications\GenericSystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function notifyUser(User $user, string $title, bool $markAsRead = false)
{
    $user->notify(new GenericSystemNotification(
        title: $title,
        message: 'Body for '.$title,
        tag: 'System',
        meta: ['origem' => 'Sistema'],
        level: 'info',
    ));

    $notification = $user->notifications()
        ->where('data->title', $title)
        ->latest()
        ->first();

    if ($markAsRead && $notification) {
        $notification->markAsRead();
    }

    return $notification;
}

it('lists unread and read notifications separately', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    notifyUser($user, 'Unread Reminder');
    notifyUser($user, 'Welcome Back', markAsRead: true);

    Livewire::test(NotificationsIndex::class)
        ->assertSee('Unread Reminder')
        ->assertSee('Welcome Back');
});

it('allows marking notifications as read or unread from the component', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $notification = notifyUser($user, 'Needs attention');

    Livewire::test(NotificationsIndex::class)
        ->call('markAsRead', $notification?->id);

    expect($notification?->fresh()?->read())->toBeTrue();

    Livewire::test(NotificationsIndex::class)
        ->call('markAsUnread', $notification?->id);

    expect($notification?->fresh()?->read())->toBeFalse();
});

it('sends an onboarding notification when a user is created', function (): void {
    $user = User::factory()->create();

    $notification = $user->notifications()->first();

    expect($notification)->not->toBeNull();
    expect($notification?->data['title'] ?? null)->toBe('Bem-vindo ao Booknotes!');
    expect($notification?->data['tag'] ?? null)->toBe('Onboarding');
});
