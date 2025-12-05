<?php

use App\Livewire\Settings\TwoFactor\RecoveryCodes;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

uses(RefreshDatabase::class);

it('loads existing recovery codes on mount', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => encrypt(json_encode(['code-one', 'code-two'])),
    ]);

    $this->actingAs($user);

    Livewire::test(RecoveryCodes::class)
        ->assertSet('recoveryCodes', ['code-one', 'code-two']);
});

it('handles invalid encrypted payload gracefully', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => 'invalid',
    ]);

    $this->actingAs($user);

    Livewire::test(RecoveryCodes::class)
        ->assertHasErrors('recoveryCodes')
        ->assertSet('recoveryCodes', []);
});

it('regenerates recovery codes through the Fortify action', function (): void {
    $user = User::factory()->create([
        'two_factor_secret' => 'secret',
        'two_factor_recovery_codes' => encrypt(json_encode(['old-code'])),
    ]);

    $this->actingAs($user);

    $fakeAction = new class extends GenerateNewRecoveryCodes
    {
        public function __invoke($user): void
        {
            $user->forceFill([
                'two_factor_recovery_codes' => encrypt(json_encode(['new-code'])),
            ])->save();
        }
    };

    Livewire::test(RecoveryCodes::class)
        ->call('regenerateRecoveryCodes', $fakeAction)
        ->assertSet('recoveryCodes', ['new-code']);
});
