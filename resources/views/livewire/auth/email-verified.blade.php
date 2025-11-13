<x-layouts.auth :title="__('Email verified successfully')">
    <div class="flex flex-col items-center gap-6 text-center">
        <div class="flex size-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <div class="space-y-3">
            <flux:heading size="lg">{{ __('Email verified successfully') }}</flux:heading>
            <flux:text>
                {{ __('Your account is confirmed. You can now access every notebook, discipline, and PDF in your study hub.') }}
            </flux:text>
        </div>

        <div class="flex w-full flex-col gap-3">
            <flux:button :href="route('dashboard')" variant="primary" wire:navigate>
                {{ __('Go to dashboard') }}
            </flux:button>
            <flux:button :href="route('home')" variant="ghost" wire:navigate>
                {{ __('Back to homepage') }}
            </flux:button>
        </div>
    </div>
</x-layouts.auth>
