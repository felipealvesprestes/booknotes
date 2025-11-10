<x-layouts.auth>
    <div class="space-y-8">
        <div class="space-y-3">
            <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-indigo-600">
                {{ __('Bem-vindo de volta') }}
            </span>
            <h2 class="text-3xl font-semibold tracking-tight text-neutral-900">
                {{ __('Retome seus painéis e flashcards') }}
            </h2>
            <p class="text-sm leading-relaxed text-neutral-600">
                {{ __('Acesse o dashboard com métricas, continue suas sessões de estudo, faça downloads em PDF e gerencie a segurança da conta com 2FA.') }}
            </p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="space-y-6">
            @csrf

            <div class="space-y-4">
                <flux:input
                    name="email"
                    :label="__('Email')"
                    type="email"
                    autofocus
                    autocomplete="email"
                    placeholder="email@exemplo.com"
                    value="{{ old('email') }}"
                />

                <div class="space-y-2">
                    <div class="relative">
                        <flux:input
                            name="password"
                            :label="__('Senha')"
                            type="password"
                            autocomplete="current-password"
                            :placeholder="__('Entre com sua senha')"
                            viewable
                        />

                        @if (Route::has('password.request'))
                            <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                                {{ __('Esqueceu a senha?') }}
                            </flux:link>
                        @endif
                    </div>

                    <flux:checkbox name="remember" :label="__('Manter-me conectado neste dispositivo')" :checked="old('remember')" />
                </div>
            </div>

            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                {{ __('Log in') }}
            </flux:button>
        </form>

        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-5 text-sm text-neutral-600">
            <p class="font-medium text-neutral-900">{{ __('Dica rápida') }}</p>
            <p class="mt-2">
                {{ __('Ative a autenticação em duas etapas e acompanhe o log de atividades em Configurações > Segurança sempre que fizer login em um novo dispositivo.') }}
            </p>
        </div>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-neutral-600">
                <span>{{ __('Não tem uma conta?') }}</span>
                <flux:link :href="route('register')" wire:navigate>{{ __('Crie a sua agora') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts.auth>
