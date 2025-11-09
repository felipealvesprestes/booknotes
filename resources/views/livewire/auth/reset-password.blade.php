<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Definir nova senha')"
            :description="__('Escolha uma nova senha para voltar a acessar seus notebooks, flashcards e exportações.')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ old('email', request('email')) }}"
                :label="__('Email')"
                type="email"
                autocomplete="email"
            />
            <x-form.error name="email" />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Senha')"
                type="password"
                autocomplete="new-password"
                :placeholder="__('Senha')"
                viewable
            />
            <x-form.error name="password" />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirmar senha')"
                type="password"
                autocomplete="new-password"
                :placeholder="__('Confirmar senha')"
                viewable
            />
            <x-form.error name="password_confirmation" />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    {{ __('Salvar nova senha') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts.auth>
