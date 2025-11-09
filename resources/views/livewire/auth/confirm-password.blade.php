<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Confirme sua senha')"
            :description="__('Precisamos garantir que é você antes de acessar áreas sensíveis como exportações, 2FA e configurações de segurança.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('Senha')"
                type="password"
                autocomplete="current-password"
                :placeholder="__('Senha')"
                viewable
            />
            <x-form.error name="password" />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('Confirmar acesso') }}
            </flux:button>
        </form>
    </div>
</x-layouts.auth>
