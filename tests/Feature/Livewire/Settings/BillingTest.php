<?php

use App\Livewire\Settings\Billing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('prevents checkout when user has lifetime access or missing price id', function (): void {
    $user = User::factory()->create(['is_lifetime' => true]);

    $component = Livewire::actingAs($user)->test(Billing::class);
    expect($component->instance()->startCheckout())->toBeNull();

    $user->is_lifetime = false;
    $user->save();

    Config::set('services.stripe.price_id', null);

    $component = Livewire::actingAs($user)->test(Billing::class);
    expect($component->instance()->startCheckout())->toBeNull();
});

it('cancels and resumes subscriptions only in valid states', function (): void {
    $subscriptionActive = \Mockery::mock(\Laravel\Cashier\Subscription::class);
    $subscriptionActive->shouldReceive('active')->andReturn(true);
    $subscriptionActive->shouldReceive('cancel')->once();
    $subscriptionActive->shouldReceive('onGracePeriod')->andReturn(false);

    $subscriptionGrace = \Mockery::mock(\Laravel\Cashier\Subscription::class);
    $subscriptionGrace->shouldReceive('active')->andReturn(false);
    $subscriptionGrace->shouldReceive('onGracePeriod')->andReturn(true);
    $subscriptionGrace->shouldReceive('resume')->once();

    $user = \Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('subscription')->with('default')->andReturn($subscriptionActive, $subscriptionGrace, null);

    Auth::shouldReceive('user')->andReturn($user);

    $component = app(Billing::class);
    $component->cancelSubscription();
    expect(session('billingMessage'))->toBe(__('Sua assinatura será encerrada ao final do ciclo atual. Você continuará tendo acesso até lá.'));

    $component->resumeSubscription();
    expect(session('billingMessage'))->toBe(__('Assinatura reativada com sucesso.'));

    $component->cancelSubscription();
});
