<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-neutral-950 antialiased text-neutral-50">
        <div class="relative isolate min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-br from-indigo-500 via-indigo-700 to-neutral-900 opacity-90"></div>
            <div class="pointer-events-none absolute -top-24 right-24 -z-10 size-[520px] rounded-full bg-indigo-400/20 blur-3xl"></div>

            <div class="mx-auto flex min-h-screen w-full max-w-6xl flex-col gap-12 px-6 py-12 md:flex-row md:items-center md:justify-between">
                <aside class="flex max-w-xl flex-col gap-8 md:gap-10">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 text-sm font-semibold uppercase tracking-[0.3em] text-indigo-200" wire:navigate>
                        <span class="flex size-10 items-center justify-center">
                            <x-app-logo-icon class="size- text-white" />
                        </span>
                        {{ config('app.name', 'Laravel') }}
                    </a>

                    <div class="space-y-5">
                        <h1 class="text-4xl font-semibold leading-snug tracking-tight text-white md:text-5xl">
                            Painel completo para suas leituras, notas e flashcards.
                        </h1>
                        <p class="text-base leading-relaxed text-indigo-100">
                            Acompanhe métricas de estudo, envie PDFs, gere exportações e mantenha tudo seguro com autenticação em duas etapas e registro de atividades.
                        </p>
                    </div>

                    @isset($aside)
                        {{ $aside }}
                    @else
                        <dl class="grid gap-6 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <dt class="text-sm font-semibold uppercase tracking-[0.24em] text-indigo-200">Estudos com contexto</dt>
                                <dd class="mt-3 text-sm text-indigo-100">
                                    Organize notebooks, disciplinas, notas e PDFs com a mesma taxonomia que aparece em toda a área logada.
                                </dd>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <dt class="text-sm font-semibold uppercase tracking-[0.24em] text-indigo-200">Segurança e registros</dt>
                                <dd class="mt-3 text-sm text-indigo-100">
                                    2FA opcional, confirmação por e-mail e log detalhado para acompanhar cada exportação ou exclusão.
                                </dd>
                            </div>
                        </dl>
                    @endisset
                </aside>

                <main class="w-full max-w-md rounded-3xl bg-white p-8 text-neutral-900 shadow-2xl shadow-indigo-900/20 sm:p-10">
                    <div class="flex flex-col gap-8">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <x-cookie-consent />
        <x-toast />
        @fluxScripts
    </body>
</html>
