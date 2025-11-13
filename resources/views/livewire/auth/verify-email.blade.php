<x-layouts.auth :title="__('Verifique seu e-mail para liberar o Booknotes')">
    <div class="flex flex-col gap-6">
        <div class="space-y-3 text-center">
            <flux:heading size="lg">{{ __('Verifique seu e-mail para liberar o Booknotes') }}</flux:heading>
            <flux:text>
                {{ __('Enviamos um link seguro para :email. Clique nele para ativar sua conta.', ['email' => auth()->user()->email]) }}
            </flux:text>
        </div>

        @if (session('status') == 'verification-link-sent')
            <flux:callout
                icon="check-circle"
                variant="success"
                heading="{{ __('Enviamos outro link de verificação!') }}"
            >
                {{ __('Confira sua caixa de entrada ou a pasta de spam/lixeira. O link anterior continua válido por uma hora.') }}
            </flux:callout>
        @endif

        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-5 text-sm text-neutral-700">
            {{ __('Abra seu aplicativo de e-mail, procure por "Booknotes" e lembre-se de verificar as pastas de spam e lixo eletrônico. Se nada chegar em alguns minutos, solicite um novo envio abaixo.') }}
        </div>

        <form method="POST" action="{{ route('verification.send') }}" class="space-y-3">
            @csrf
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Reenviar e-mail de verificação') }}
            </flux:button>
            <flux:text class="text-center text-xs text-neutral-500">
                {{ __('Precisa alterar o endereço? Saia da conta e cadastre-se novamente com o e-mail correto.') }}
            </flux:text>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <flux:button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">
                {{ __('Sair') }}
            </flux:button>
        </form>
    </div>
</x-layouts.auth>
