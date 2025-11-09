<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-neutral-950 antialiased text-neutral-50">
        <div class="relative isolate min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-br from-indigo-500 via-indigo-700 to-neutral-900 opacity-90"></div>
            <div class="pointer-events-none absolute -top-24 right-24 -z-10 size-[520px] rounded-full bg-indigo-400/20 blur-3xl"></div>

            <div class="mx-auto flex min-h-screen w-full max-w-6xl flex-col gap-12 px-6 py-12 md:flex-row md:items-center md:justify-between md:py-16">
                <aside class="flex max-w-xl flex-col gap-8 md:gap-10">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 text-sm font-semibold uppercase tracking-[0.3em] text-indigo-200" wire:navigate>
                        <span class="flex size-10 items-center justify-center rounded-xl border border-white/20 bg-white/10 backdrop-blur">
                            <x-app-logo-icon class="size-8 fill-white" />
                        </span>
                        {{ config('app.name', 'Laravel') }}
                    </a>

                    <div class="space-y-5">
                        <h1 class="text-4xl font-semibold leading-snug tracking-tight text-white md:text-5xl">
                            Seu hub inteligente para transformar leitura em aprendizado contínuo.
                        </h1>
                        <p class="text-base leading-relaxed text-indigo-100">
                            Capture insights de livros, organize resumos e mantenha uma rotina de revisão guiada. Concentre-se em aprender; nós cuidamos da estrutura.
                        </p>
                    </div>

                    <dl class="grid gap-6 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <dt class="text-sm font-semibold uppercase tracking-[0.24em] text-indigo-200">Rotina guiada</dt>
                            <dd class="mt-3 text-sm text-indigo-100">
                                Receba lembretes e planos automáticos de revisão focados no que você precisa reforçar.
                            </dd>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <dt class="text-sm font-semibold uppercase tracking-[0.24em] text-indigo-200">Notas conectadas</dt>
                            <dd class="mt-3 text-sm text-indigo-100">
                                Vincule resumos, highlights e fichamentos sem perder o contexto original.
                            </dd>
                        </div>
                    </dl>
                </aside>

                <main class="w-full max-w-md rounded-3xl bg-white p-8 text-neutral-900 shadow-2xl shadow-indigo-900/20 sm:p-10">
                    <div class="flex flex-col gap-8">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
