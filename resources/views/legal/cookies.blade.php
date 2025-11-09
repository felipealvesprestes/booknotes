<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php($title = 'Política de Cookies · ' . config('app.name'))
        @include('partials.head')
    </head>
    <body class="bg-neutral-950 text-neutral-50 antialiased">
        <div class="relative isolate min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-neutral-900 via-neutral-950 to-black opacity-90"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-gradient-to-b from-indigo-600/40 to-transparent blur-3xl"></div>

            <header class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-6 pt-10 md:flex-row md:items-center md:justify-between">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 text-sm font-semibold text-indigo-100 transition hover:text-white" wire:navigate>
                    <span class="flex size-10 items-center justify-center rounded-2xl border border-white/10 bg-white/10 backdrop-blur">
                        <x-app-logo-icon class="size-7 fill-white" />
                    </span>
                    {{ config('app.name') }}
                </a>

                <div class="flex flex-col items-start gap-3 text-sm font-semibold text-indigo-100 md:flex-row md:items-center">
                    <span class="text-xs uppercase tracking-[0.3em] text-white/70">
                        Transparência em primeiro lugar
                    </span>

                    <div class="flex flex-wrap items-center gap-3">
                        @cookieconsentbutton(
                            action: 'reset',
                            label: __('cookieConsent::cookies.manage'),
                            attributes: [
                                'class' => 'cookiereset inline-flex items-center gap-2 rounded-full border border-white/30 px-4 py-2 text-sm font-semibold text-white transition hover:border-white hover:bg-white/5',
                            ]
                        )
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-4xl space-y-10 px-6 py-12 md:py-20">
                <section class="rounded-3xl border border-white/10 bg-white/5 p-8 backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-indigo-200">Política de Cookies</p>
                    <h1 class="mt-4 text-3xl font-semibold text-white md:text-4xl">
                        Como o {{ config('app.name') }} utiliza cookies para entregar uma experiência inteligente e segura.
                    </h1>
                    <p class="mt-5 text-base leading-relaxed text-indigo-100">
                        Cookies são pequenos arquivos armazenados no seu navegador. Utilizamos apenas o necessário para manter sua conta segura,
                        lembrar preferências e, quando autorizado, coletar métricas anônimas de uso para melhorar o produto.
                    </p>
                </section>

                <section class="grid gap-6 md:grid-cols-2">
                    <article class="rounded-3xl border border-white/5 bg-white/5 p-6 text-indigo-100 backdrop-blur">
                        <h2 class="text-lg font-semibold text-white">Essenciais</h2>
                        <p class="mt-3 text-sm leading-relaxed text-indigo-100">
                            Mantêm você autenticado, protegem os formulários contra ataques e registram o consentimento dado.
                            Sem eles a plataforma não funciona corretamente.
                        </p>
                    </article>
                    <article class="rounded-3xl border border-white/5 bg-white/5 p-6 text-indigo-100 backdrop-blur">
                        <h2 class="text-lg font-semibold text-white">Analíticos</h2>
                        <p class="mt-3 text-sm leading-relaxed text-indigo-100">
                            Quando ativados, ajudam a compreender como os recursos são utilizados para priorizar melhorias.
                            Nunca vendemos nem compartilhamos seus dados pessoais.
                        </p>
                    </article>
                </section>

                <section class="rounded-3xl border border-white/10 bg-white/5 p-0 text-sm text-indigo-50 backdrop-blur">
                    <div class="border-b border-white/10 px-6 py-5">
                        <h2 class="text-xl font-semibold text-white">Detalhamento técnico</h2>
                        <p class="mt-2 text-sm leading-relaxed text-indigo-200">
                            A tabela abaixo é gerada automaticamente pelo pacote do consentimento e reflete exatamente quais cookies estão ativos,
                            a finalidade de cada um e por quanto tempo ficam habilitados.
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <div class="min-w-full divide-y divide-white/5" data-cookie-consent-table>
                            @cookieconsentinfo
                        </div>
                    </div>
                </section>
            </main>
        </div>

        @include('partials.cookie-consent')
    </body>
</html>
