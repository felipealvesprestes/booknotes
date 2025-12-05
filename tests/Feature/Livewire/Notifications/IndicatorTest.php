<?php

use App\Livewire\Notifications\Indicator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows no unread indicator for guests', function (): void {
    Livewire::test(Indicator::class)
        ->assertViewHas('hasUnread', false);
});

it('detects when the authenticated user has unread notifications', function (): void {
    $user = User::factory()->create();

    DatabaseNotification::create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\GenericSystemNotification',
        'notifiable_id' => $user->id,
        'notifiable_type' => User::class,
        'data' => ['message' => 'Test'],
    ]);

    Livewire::actingAs($user)
        ->test(Indicator::class)
        ->assertViewHas('hasUnread', true);
});
