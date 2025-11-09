<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Billing extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.settings.billing', [
            'user' => $user,
            'subscription' => $user?->subscription('default'),
            'trialDaysRemaining' => $this->trialDaysRemaining($user),
            'priceConfigured' => filled(config('services.stripe.price_id')),
            'statusMessage' => session('subscription_required'),
        ]);
    }

    public function startCheckout()
    {
        $user = Auth::user();

        if (! $user || $user->hasLifetimeAccess()) {
            return null;
        }

        $priceId = config('services.stripe.price_id');

        if (! $priceId) {
            $this->addError('billing', __('Configure o STRIPE_PRICE_ID no arquivo .env antes de iniciar o checkout.'));

            return null;
        }

        $builder = $user->newSubscription('default', $priceId)
            ->allowPromotionCodes();

        if ($user->onGenericTrial() && ($user->trial_ends_at instanceof Carbon)) {
            $builder->trialUntil($user->trial_ends_at);
        }

        $checkout = $builder->checkout([
            'success_url' => route('settings.billing', ['checkout' => 'success']),
            'cancel_url' => route('settings.billing', ['checkout' => 'cancelled']),
            'automatic_tax' => ['enabled' => false],
            'billing_address_collection' => 'auto',
            'locale' => 'auto',
        ]);

        return redirect()->away($checkout->url);
    }

    public function openBillingPortal()
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        $portalUrl = $user->billingPortalUrl(route('settings.billing'));

        return redirect()->away($portalUrl);
    }

    public function cancelSubscription(): void
    {
        $subscription = Auth::user()?->subscription('default');

        if (! $subscription || ! $subscription->active()) {
            return;
        }

        $subscription->cancel();

        session()->flash('billingMessage', __('Sua assinatura será encerrada ao final do ciclo atual. Você continuará tendo acesso até lá.'));
    }

    public function resumeSubscription(): void
    {
        $subscription = Auth::user()?->subscription('default');

        if (! $subscription || ! $subscription->onGracePeriod()) {
            return;
        }

        $subscription->resume();

        session()->flash('billingMessage', __('Assinatura reativada com sucesso.'));
    }

    private function trialDaysRemaining(?User $user): ?int
    {
        if (! $user || ! ($user->trial_ends_at instanceof Carbon)) {
            return null;
        }

        $diff = now()->diffInDays($user->trial_ends_at, false);

        return max(0, $diff);
    }
}
