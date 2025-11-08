<x-layouts.auth>
    <div class="space-y-8">
        <div class="space-y-3">
            <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-indigo-600">
                {{ __('Welcome back') }}
            </span>
            <h2 class="text-3xl font-semibold tracking-tight text-neutral-900">
                {{ __('Access your study hub') }}
            </h2>
            <p class="text-sm leading-relaxed text-neutral-600">
                {{ __('Keep progressing with dashboards, revisões e insights atualizados em tempo real.') }}
            </p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="space-y-6">
            @csrf

            <div class="space-y-4">
                <flux:input
                    name="email"
                    :label="__('Email address')"
                    type="email"
                    autofocus
                    autocomplete="email"
                    placeholder="email@exemplo.com"
                    value="{{ old('email') }}"
                />
                <x-form.error name="email" />

                <div class="space-y-2">
                    <div class="relative">
                        <flux:input
                            name="password"
                            :label="__('Password')"
                            type="password"
                            autocomplete="current-password"
                            :placeholder="__('Enter your password')"
                            viewable
                        />

                        @if (Route::has('password.request'))
                            <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                                {{ __('Forgot your password?') }}
                            </flux:link>
                        @endif
                    </div>
                    <x-form.error name="password" />

                    <flux:checkbox name="remember" :label="__('Keep me signed in on this device')" :checked="old('remember')" />
                </div>
            </div>

            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                {{ __('Log in') }}
            </flux:button>
        </form>

        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-5 text-sm text-neutral-600">
            <p class="font-medium text-neutral-900">{{ __('Dica rápida') }}</p>
            <p class="mt-2">
                {{ __('Use o mesmo email das suas leituras para importar highlights automaticamente em questão de segundos.') }}
            </p>
        </div>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-neutral-600">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link :href="route('register')" wire:navigate>{{ __('Create yours now') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts.auth>
