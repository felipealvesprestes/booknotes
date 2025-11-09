<x-layouts.auth>
    <x-slot name="aside">
        <ul class="space-y-4 rounded-2xl border border-white/10 bg-white/5 p-6 text-sm text-indigo-100 backdrop-blur">
            <li class="flex items-start gap-3">
                <span class="mt-1 inline-flex size-6 items-center justify-center rounded-full bg-emerald-400/20 text-emerald-200">
                    <svg class="size-3.5" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M6.667 10.114 4.553 8l-.943.943 3.057 3.057 6-6-.943-.943-5.057 5.057Z" />
                    </svg>
                </span>
                <span>{{ __('Monte notebooks e disciplinas para cada prova, curso ou projeto e acompanhe os contadores automaticamente.') }}</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="mt-1 inline-flex size-6 items-center justify-center rounded-full bg-emerald-400/20 text-emerald-200">
                    <svg class="size-3.5" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M6.667 10.114 4.553 8l-.943.943 3.057 3.057 6-6-.943-.943-5.057 5.057Z" />
                    </svg>
                </span>
                <span>{{ __('Transforme notas em flashcards e estude no hub dedicado com fila embaralhada e modo foco.') }}</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="mt-1 inline-flex size-6 items-center justify-center rounded-full bg-emerald-400/20 text-emerald-200">
                    <svg class="size-3.5" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M6.667 10.114 4.553 8l-.943.943 3.057 3.057 6-6-.943-.943-5.057 5.057Z" />
                    </svg>
                </span>
                <span>{{ __('Envie PDFs importantes e gere exportações em PDF para compartilhar conteúdo com sua turma ou mentoria.') }}</span>
            </li>
        </ul>
    </x-slot>

    <div class="space-y-8">
        <div class="space-y-3">
            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-emerald-600">
                {{ __('Create your account') }}
            </span>
            <h2 class="text-3xl font-semibold tracking-tight text-neutral-900">
                {{ __('Construa seu hub de notas, flashcards e PDFs') }}
            </h2>
            <p class="text-sm leading-relaxed text-neutral-600">
                {{ __('Preencha seus dados e comece a organizar notebooks, disciplinas, notas e sessões de estudo com métricas em tempo real.') }}
            </p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="space-y-6">
            @csrf

            <div class="space-y-4">
                <flux:input
                    name="name"
                    :label="__('Name')"
                    type="text"
                    autofocus
                    autocomplete="name"
                    :placeholder="__('Seu nome completo')"
                    value="{{ old('name') }}"
                />
                <x-form.error name="name" />

                <flux:input
                    name="email"
                    :label="__('Email address')"
                    type="email"
                    autocomplete="email"
                    placeholder="email@exemplo.com"
                    value="{{ old('email') }}"
                />
                <x-form.error name="email" />

                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    autocomplete="new-password"
                    :placeholder="__('Crie uma senha segura')"
                    viewable
                />
                <x-form.error name="password" />

                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    autocomplete="new-password"
                    :placeholder="__('Repita a senha para confirmar')"
                    viewable
                />
                <x-form.error name="password_confirmation" />
            </div>

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            <p class="font-medium text-emerald-900">{{ __('Segurança em primeiro lugar') }}</p>
            <p class="mt-2">
                {{ __('Ative a autenticação em duas etapas, acompanhe o log de atividades e controle senha, idioma e exportações em um único painel.') }}
            </p>
        </div>

            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-neutral-600">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts.auth>
