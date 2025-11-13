<x-layouts.auth :title="__('Verify your email to unlock Booknotes')">
    <div class="flex flex-col gap-6">
        <div class="space-y-3 text-center">
            <flux:heading size="lg">{{ __('Verify your email to unlock Booknotes') }}</flux:heading>
            <flux:text>
                {{ __('We sent a secure link to :email. Click it to activate your account.', ['email' => auth()->user()->email]) }}
            </flux:text>
        </div>

        @if (session('status') == 'verification-link-sent')
            <flux:callout
                icon="check-circle"
                variant="success"
                heading="{{ __('Verification link sent again!') }}"
            >
                {{ __('Check your inbox or spam folder. The previous link remains valid for one hour.') }}
            </flux:callout>
        @endif

        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-5 text-sm text-neutral-700">
            {{ __('Open your email app and search for "Booknotes". If nothing arrives within a few minutes, request a new email below.') }}
        </div>

        <form method="POST" action="{{ route('verification.send') }}" class="space-y-3">
            @csrf
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Resend verification email') }}
            </flux:button>
            <flux:text class="text-center text-xs text-neutral-500">
                {{ __('Need to change the address? Sign out and create your account again with the correct email.') }}
            </flux:text>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <flux:button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">
                {{ __('Sign out') }}
            </flux:button>
        </form>
    </div>
</x-layouts.auth>
