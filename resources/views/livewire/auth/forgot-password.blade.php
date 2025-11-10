<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Recuperar acesso')"
            :description="__('Informe o email cadastrado e enviaremos um link para redefinir sua senha e voltar ao dashboard.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="email"
                :label="__('Email cadastrado')"
                type="email"
                autofocus
                placeholder="email@exemplo.com"
                value="{{ old('email') }}"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Enviar link de redefinição') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Ou volte para a tela de') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('login') }}</flux:link>
        </div>
    </div>
</x-layouts.auth>
