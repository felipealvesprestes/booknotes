<?php

use App\Livewire\Settings\Language;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('language settings page is displayed', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/settings/language')->assertOk();
});

test('language preference can be updated', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user);

    Livewire::test(Language::class)
        ->set('locale', 'pt_BR')
        ->call('updateLanguage')
        ->assertHasNoErrors();

    expect($user->refresh()->locale)->toBe('pt_BR');
});
