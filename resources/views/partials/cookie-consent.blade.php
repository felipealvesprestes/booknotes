@if (! request()->cookie('booknotes_cookie_preferences'))
    <div
        data-cookie-banner
        class="fixed inset-x-0 bottom-3 z-50 flex justify-center px-4"
        hidden
    >
        <div class="w-full max-w-4xl rounded-2xl border border-zinc-200 bg-white/95 p-5 shadow-2xl shadow-zinc-900/10 backdrop-blur">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="space-y-3 text-sm text-zinc-700">
                    <div class="inline-flex items-center gap-2 rounded-full bg-zinc-100/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-600">
                        {{ __('Preferências de privacidade') }}
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-zinc-900">{{ __('Gerencie o uso de cookies') }}</p>
                        <p class="mt-2 leading-relaxed">
                            {{ __('Utilizamos cookies essenciais para manter o Booknotes funcionando e, com sua autorização, cookies complementares para métricas e melhorias. Você pode aceitar todos ou manter apenas os estritamente necessários, conforme a LGPD.') }}
                        </p>
                    </div>
                </div>

                <div class="flex w-full flex-col gap-3 md:max-w-sm">
                    <button
                        type="button"
                        data-cookie-action="necessary"
                        class="group w-full rounded-2xl border border-zinc-200 bg-zinc-50/60 px-5 py-3 text-left text-sm font-medium text-zinc-800 transition hover:border-zinc-300 hover:bg-white"
                    >
                        <span class="flex items-start justify-between gap-3">
                            <span>
                                {{ __('Manter apenas os necessários') }}
                                <span class="block text-xs font-normal text-zinc-500">
                                    {{ __('Essenciais para login e segurança') }}
                                </span>
                            </span>
                            <span class="rounded-full border border-zinc-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-zinc-500">
                                {{ __('Recomendado') }}
                            </span>
                        </span>
                    </button>

                    <button
                        type="button"
                        data-cookie-action="all"
                        class="group w-full rounded-2xl bg-zinc-900/95 px-5 py-3 text-left text-sm font-semibold text-white transition hover:bg-black"
                    >
                        <span class="flex items-start justify-between gap-3">
                            <span>
                                {{ __('Aceitar todos os cookies') }}
                                <span class="block text-xs font-normal text-white/70">
                                    {{ __('Inclui cookies opcionais para métricas e melhorias') }}
                                </span>
                            </span>
                            <svg class="mt-0.5 size-4 text-white/70" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M3 8.5L6.5 12L13 4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>

            <p class="mt-4 text-xs text-zinc-500">
                {{ __('Você poderá revisar sua escolha a qualquer momento limpando as preferências de cookies do navegador.') }}
            </p>
        </div>
    </div>
@endif
