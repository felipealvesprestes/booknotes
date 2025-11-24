<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php($title = config('app.name') . ' - Plataforma de estudos com flashcards, resumos e PDFs')
    @php($metaDescription = 'Use o ' . config('app.name') . ' para criar resumos, organizar cadernos, estudar com flashcards online e acompanhar métricas. Ideal para concursos, ENEM, faculdade e certificações.')
    @php($metaRobots = 'index,follow')
    @php($canonical = url('/'))
    @php($ogTitle = 'Flashcards online, resumos e PDFs em um hub de estudos completo')
    @php($ogDescription = 'Organize, revise e memorize conteúdos com uma plataforma de estudos feita para quem leva o aprendizado a sério.')
    @php($ogImage = asset('img/share-default.jpg'))
    @include('partials.head')
</head>

<body class="bg-white text-neutral-900 antialiased">
    <div class="relative isolate overflow-hidden">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[420px] bg-gradient-to-b from-indigo-100 via-white to-transparent"></div>

        @php($siteNavigation = [
        ['label' => 'Recursos', 'href' => '#features'],
        ['label' => 'Como funciona', 'href' => '#workflow'],
        ['label' => 'Plano', 'href' => '#pricing'],
        ['label' => 'Depoimentos', 'href' => '#testimonials'],
        ['label' => 'Perguntas', 'href' => '#faq'],
        ['label' => 'Blog', 'href' => route('blog.index')],
        ['label' => 'Privacidade', 'href' => route('privacy')],
        ])

        <header
            x-data="{ mobileMenuOpen: false }"
            @keydown.escape.window="mobileMenuOpen = false"
            class="mx-auto max-w-6xl px-6 py-8 sm:py-10">
            <div class="flex flex-wrap items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="text-lg font-semibold text-neutral-800">{{ config('app.name') }}</a>
                </div>

                <nav class="hidden text-sm font-medium text-neutral-600 lg:flex lg:flex-wrap lg:items-center lg:gap-6">
                    @foreach ($siteNavigation as $item)
                    <a href="{{ $item['href'] }}" class="transition hover:text-neutral-900">{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <div class="hidden items-center gap-3 text-sm font-semibold lg:flex">
                    @if (Route::has('login'))
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center rounded-lg px-4 py-2 text-neutral-600 transition hover:text-neutral-900">
                        Entrar
                    </a>
                    @endif

                    @if (Route::has('register'))
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white transition hover:bg-indigo-500">
                        Criar conta gratuita
                    </a>
                    @endif
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-neutral-200 p-2 text-neutral-700 transition hover:border-neutral-300 hover:text-neutral-900 lg:hidden"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    x-bind:aria-expanded="mobileMenuOpen ? 'true' : 'false'"
                    aria-controls="site-mobile-menu">
                    <span class="sr-only">Alternar menu</span>
                    <svg
                        x-show="!mobileMenuOpen"
                        class="size-5"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                        fill="none"
                        aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    <svg
                        x-show="mobileMenuOpen"
                        x-cloak
                        class="size-5"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                        fill="none"
                        aria-hidden="true">
                        <path d="M6 6l12 12M6 18L18 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <div
                id="site-mobile-menu"
                x-cloak
                x-show="mobileMenuOpen"
                x-transition:enter="origin-top motion-safe:transition motion-safe:duration-200 motion-safe:ease-out"
                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="motion-safe:transition motion-safe:duration-150 motion-safe:ease-in"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                @click.away="mobileMenuOpen = false"
                class="mt-6 grid gap-5 rounded-2xl border border-neutral-200 bg-white p-5 shadow-lg shadow-neutral-900/5 lg:hidden">
                <nav class="grid gap-3 text-sm font-medium text-neutral-700">
                    @foreach ($siteNavigation as $item)
                    <a
                        href="{{ $item['href'] }}"
                        class="flex items-center justify-between rounded-xl px-3 py-2 transition hover:bg-neutral-50 hover:text-neutral-900"
                        @click="mobileMenuOpen = false">
                        <span>{{ $item['label'] }}</span>
                        <svg class="size-4 text-current" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path
                                fill-rule="evenodd"
                                d="M7.23 4.21a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 1 1-1.06-1.06L10.94 10 7.23 6.27a.75.75 0 0 1 0-1.06Z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                    @endforeach
                </nav>

                <div class="grid gap-3 text-sm font-semibold">
                    @if (Route::has('login'))
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-neutral-200 px-4 py-2 text-neutral-700 transition hover:border-neutral-300 hover:text-neutral-900"
                        @click="mobileMenuOpen = false">
                        Entrar
                    </a>
                    @endif

                    @if (Route::has('register'))
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-white transition hover:bg-indigo-500"
                        @click="mobileMenuOpen = false">
                        Criar conta gratuita
                    </a>
                    @endif
                </div>

                <p class="text-xs leading-relaxed text-neutral-500">
                    Explore o painel, gere PDFs e teste flashcards gratuitos antes de assumir qualquer compromisso.
                </p>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6">
            <section class="relative overflow-hidden rounded-3xl border border-indigo-100 bg-white px-8 py-16 shadow-lg shadow-indigo-100/30 sm:px-12">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-indigo-600">
                        Novo módulo de exercícios
                    </span>
                    <h1 class="mt-6 text-3xl font-semibold tracking-tight text-neutral-900 uppercase leading-12 sm:text-4xl">
                        Um só lugar para anotar, revisar, aprender e evoluir
                    </h1>
                    <p class="mt-6 text-lg leading-relaxed text-neutral-600">
                        {{ config('app.name') }} conecta notebooks, disciplinas, notas e flashcards para que você acompanhe métricas em tempo real, estude no modo foco e, agora, pratique com exercícios de Verdadeiro ou Falso, Complete as Lacunas e Múltipla Escolha — tudo protegido com autenticação em duas etapas.
                    </p>
                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold text-white transition hover:bg-indigo-500">
                            Começar agora mesmo
                        </a>
                        @endif

                        <a
                            href="#features"
                            class="inline-flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-base font-semibold text-indigo-600 transition hover:text-indigo-500">
                            Ver recursos principais
                            <svg class="size-4 text-current" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                <path d="M8.00065 1.33331C7.63246 1.33331 7.33398 1.63179 7.33398 1.99998V11.7243L3.4716 7.86195C3.21025 7.6006 2.78792 7.6006 2.52657 7.86195C2.26521 8.12331 2.26521 8.54564 2.52657 8.80699L7.52824 13.8087C7.78959 14.07 8.21192 14.07 8.47327 13.8087L13.4749 8.80699C13.7363 8.54564 13.7363 8.12331 13.4749 7.86195C13.2136 7.6006 12.7912 7.6006 12.5299 7.86195L8.66732 11.7243V1.99998C8.66732 1.63179 8.36884 1.33331 8.00065 1.33331Z" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="mt-14 grid gap-6 sm:grid-cols-3">
                    <div class="rounded-2xl border border-indigo-100/60 bg-indigo-50/80 p-6 text-left">
                        <h3 class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Organização inteligente</h3>
                        <p class="mt-3 text-sm text-neutral-600">
                            Organize livros e projetos em notebooks, disciplinas e notas com filtros rápidos e contadores automáticos.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100/60 bg-white p-6 text-left shadow-sm shadow-indigo-100/50">
                        <h3 class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Biblioteca conectada</h3>
                        <p class="mt-3 text-sm text-neutral-600">
                            Envie PDFs, visualize referências e converta qualquer nota em flashcard para estudar direto no hub de revisões.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100/60 bg-indigo-50/80 p-6 text-left">
                        <h3 class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Modos de estudo</h3>
                        <p class="mt-3 text-sm text-neutral-600">
                            Verdadeiro ou Falso, Complete as Lacunas e Múltipla Escolha equilibram memorização, vocabulário e análise com base na sua própria base de flashcards.
                        </p>
                    </div>
                </div>
            </section>

            <section class="mt-20 overflow-hidden rounded-3xl border border-neutral-200 bg-neutral-950/95 px-8 py-18 text-white shadow-xl shadow-neutral-900/10 sm:px-12 sm:py-20">
                <div class="grid gap-10 lg:grid-cols-[1.2fr_1fr] lg:items-center">
                    <div class="space-y-6">
                        <span class="inline-flex items-center rounded-full bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-indigo-200">
                            Segurança e confiança
                        </span>
                        <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                            Controle total sobre dados, acessos e histórico
                        </h2>
                        <p class="text-base leading-relaxed text-neutral-300">
                            Toda atividade do {{ config('app.name') }} é registrada: exclusões, exportações e estudos entram no log com data, horário e dispositivo. Some a isso autenticação em duas etapas e você tem uma plataforma pronta para mentores, equipes e instituições exigentes.
                        </p>
                        <ul class="space-y-3 text-sm text-neutral-300">
                            <li class="flex items-start gap-3">
                                <span class="mt-1 size-2.5 rounded-full bg-indigo-400"></span>
                                <span>Autenticação 2FA com apps TOTP e recuperação por códigos únicos.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 size-2.5 rounded-full bg-indigo-400"></span>
                                <span>Log completo de ações, com filtros e identificação do conteúdo impactado.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 size-2.5 rounded-full bg-indigo-400"></span>
                                <span>Criptografia em repouso/uso de backups redundantes e monitoramento contínuo.</span>
                            </li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-gray-100/10 bg-white/5 p-6 backdrop-blur">
                        <div class="space-y-4 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-neutral-300">Últimas atividades</span>
                                <span class="text-xs text-indigo-200">Atualizado agora</span>
                            </div>
                            <div class="space-y-3">
                                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                    <p class="text-white">Exportação PDF — Biologia</p>
                                    <p class="text-xs text-neutral-300">Hoje, 08:42 · Safari no macOS</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                    <p class="text-white">Nova sessão de flashcards</p>
                                    <p class="text-xs text-neutral-300">Ontem, 21:10 · iOS App</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                    <p class="text-white">2FA verificada</p>
                                    <p class="text-xs text-neutral-300">Ontem, 18:22 · Chrome no Windows</p>
                                </div>
                            </div>
                            <p class="text-xs uppercase tracking-[0.2em] text-neutral-400">
                                Log completo disponível no painel autenticado
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="py-24">
                <div class="relative overflow-hidden rounded-3xl border border-neutral-100 bg-gradient-to-b from-white via-indigo-50/40 to-white px-8 py-20 shadow-[0_18px_70px_rgba(15,23,42,0.08)] sm:px-14">
                    <div class="pointer-events-none absolute inset-x-0 top-0 mx-auto h-52 max-w-5xl bg-gradient-to-r from-indigo-200/50 via-white to-sky-200/40 blur-[120px]"></div>
                    <div class="pointer-events-none absolute -bottom-12 -left-16 h-40 w-40 rounded-full bg-gradient-to-br from-sky-200/60 to-purple-200/50 blur-[110px]"></div>
                    <div class="relative space-y-16">
                        <div class="mx-auto max-w-3xl space-y-6 text-center">
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-indigo-600">
                                Recursos exclusivos
                            </span>
                            <h2 class="text-3xl font-semibold tracking-tight text-neutral-900 sm:text-4xl">
                                Recursos que impulsionam o seu estudo
                            </h2>
                            <p class="text-base leading-relaxed text-neutral-600">
                                Clareza e tempo de qualidade: organize leituras, transforme em flashcards e revise em modos inteligentes com o mesmo fluxo visual limpo em qualquer dispositivo.
                            </p>
                        </div>
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div class="rounded-xl border border-gray-100 bg-white/80 p-6 shadow-sm shadow-indigo-50/40 sm:flex sm:items-center sm:gap-6">
                                <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="M5 5h14v14H5z" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M8 9h8M8 12h5M8 15h3" stroke-linecap="round" />
                                    </svg>
                                </div>
                                <div class="mt-4 sm:mt-0">
                                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-indigo-500">Rotina com contexto</p>
                                    <p class="mt-2 text-xl font-semibold text-neutral-900">Painel 360º</p>
                                    <p class="mt-2 text-sm text-neutral-600">Veja hábitos, progresso, taxa de acerto e logs importantes em um dashboard com filtros personalizáveis.</p>
                                </div>
                            </div>
                            <div class="rounded-xl border border-gray-100 bg-white/80 p-6 shadow-sm shadow-purple-50/40 sm:flex sm:items-center sm:gap-6">
                                <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-purple-50 text-purple-600">
                                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="m7 12 3 3 7-7" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M5 5h14v14H5z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="mt-4 sm:mt-0">
                                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-purple-500">Compartilhe resultados</p>
                                    <p class="mt-2 text-xl font-semibold text-neutral-900">Exportações premium</p>
                                    <p class="mt-2 text-sm text-neutral-600">Monte PDFs por disciplina, filtros e envie pacotes completos para mentores, alunos ou equipes.</p>
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-8 sm:grid-cols-2 xl:grid-cols-3">
                            <article class="group flex h-full flex-col rounded-xl border border-gray-100 bg-white/90 p-6 transition duration-300 hover:-translate-y-1">
                                <div class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-100 via-sky-100 to-purple-100 text-indigo-600 shadow-inner">
                                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="M4.75 5.75h14.5M4.75 18.25h14.5M6.25 5.75v12.5A1.5 1.5 0 0 0 7.75 19.75h8.5a1.5 1.5 0 0 0 1.5-1.5V5.75" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M9.5 9.5h5M9.5 13h5" stroke-linecap="round" />
                                    </svg>
                                </div>
                                <h3 class="mt-6 text-lg font-semibold text-neutral-900">Painel de estudos e métricas</h3>
                                <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                                    Acompanhe notebooks, disciplinas, notas e flashcards, veja sessões recentes e monitore a taxa de acertos dos últimos 30 dias.
                                </p>
                                <div class="mt-5 flex flex-wrap gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-indigo-500">
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">Métricas</span>
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">Insights</span>
                                </div>
                            </article>
                            <article class="group flex h-full flex-col rounded-xl border border-gray-100 bg-white/90 p-6 transition duration-300 hover:-translate-y-1">
                                <div class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-100 via-sky-100 to-purple-100 text-indigo-600 shadow-inner">
                                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="M7.5 7.5h9M7.5 12h6M7.5 16.5h3" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M5 21h14a1 1 0 0 0 1-1V4.914a1 1 0 0 0-.293-.707l-1.914-1.914A1 1 0 0 0 17.086 2H5a1 1 0 0 0-1 1v17a1 1 0 0 0 1 1Z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <h3 class="mt-6 text-lg font-semibold text-neutral-900">Notas e flashcards conectados</h3>
                                <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                                    Crie notas ricas, vincule a disciplinas e transforme em flashcards com um clique para usar filtros, buscas e o hub de estudos com foco.
                                </p>
                                <div class="mt-5 flex flex-wrap gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-indigo-500">
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">Flashcards</span>
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">Filtros</span>
                                </div>
                            </article>
                            <article class="group flex h-full flex-col rounded-xl border border-gray-100 bg-white/90 p-6 transition duration-300 hover:-translate-y-1">
                                <div class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-100 via-sky-100 to-purple-100 text-indigo-600 shadow-inner">
                                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="M15.75 17.25a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
                                        <path d="M15.75 5.25V3M7.5 12H3m5.25-6.75-1.5-1.5m0 13.5 1.5-1.5m12.75-4.5h-4.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <h3 class="mt-6 text-lg font-semibold text-neutral-900">Hub de PDFs e exportações</h3>
                                <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                                    Guarde apostilas no visualizador de PDFs e gere documentos unificados com filtros, agrupamentos e layouts configuráveis.
                                </p>
                                <div class="mt-5 flex flex-wrap gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-indigo-500">
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">PDFs</span>
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">Exportações</span>
                                </div>
                            </article>
                            <article class="group flex h-full flex-col rounded-xl border border-gray-100 bg-white/90 p-6 transition duration-300 hover:-translate-y-1">
                                <div class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-100 via-sky-100 to-purple-100 text-indigo-600 shadow-inner">
                                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="M9 9h6v6H9z" />
                                        <path d="M7 7h10v10H7z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <h3 class="mt-6 text-lg font-semibold text-neutral-900">Verdadeiro ou falso</h3>
                                <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                                    Gere afirmações combinadas com respostas de outros cards para testar rapidamente se o conceito apresentado corresponde ao conteúdo original.
                                </p>
                                <div class="mt-5 flex flex-wrap gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-indigo-500">
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">Revisões</span>
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">Conceitos</span>
                                </div>
                            </article>
                            <article class="group flex h-full flex-col rounded-xl border border-gray-100 bg-white/90 p-6 transition duration-300 hover:-translate-y-1">
                                <div class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-100 via-sky-100 to-purple-100 text-indigo-600 shadow-inner">
                                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="M5 12h14" stroke-linecap="round" />
                                        <path d="M6 8h6M12 16h6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <h3 class="mt-6 text-lg font-semibold text-neutral-900">Complete as lacunas</h3>
                                <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                                    Escondemos até quatro termos relevantes da resposta para você digitar e reforçar vocabulário, fórmulas ou datas críticas de cada flashcard.
                                </p>
                                <div class="mt-5 flex flex-wrap gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-indigo-500">
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">Memória ativa</span>
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">Vocabulár</span>
                                </div>
                            </article>
                            <article class="group flex h-full flex-col rounded-xl border border-gray-100 bg-white/90 p-6 transition duration-300 hover:-translate-y-1">
                                <div class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-100 via-sky-100 to-purple-100 text-indigo-600 shadow-inner">
                                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="M7 9h10M7 13h10M7 17h6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M5 7h14v12H5z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <h3 class="mt-6 text-lg font-semibold text-neutral-900">Múltipla escolha</h3>
                                <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                                    Criamos alternativas A, B, C e D com respostas de outros cards para comparar nuances e identificar pegadinhas de provas e concursos.
                                </p>
                                <div class="mt-5 flex flex-wrap gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-indigo-500">
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">Quizzes</span>
                                    <span class="rounded-full bg-indigo-50 px-3 py-1">Pegadinhas</span>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section id="workflow" class="overflow-hidden rounded-3xl bg-neutral-950 px-8 py-20 text-white sm:px-12">
                <div class="mx-auto max-w-4xl text-center">
                    <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">Do livro ao aprendizado aplicado em três passos</h2>
                    <p class="mt-4 text-base leading-relaxed text-neutral-300">
                        Combine leitura ativa, anotações estruturadas e revisão contínua sem precisar alternar entre múltiplas ferramentas.
                    </p>
                </div>
                <div class="mt-16 grid gap-10 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-left backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.32em] text-indigo-200">01</p>
                        <h3 class="mt-4 text-xl font-semibold">Capture e organize</h3>
                        <p class="mt-3 text-sm text-neutral-300">
                            Crie notebooks e disciplinas para cada objetivo, adicione notas estruturadas e envie PDFs importantes para manter todo o material referenciado.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-left backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.32em] text-indigo-200">02</p>
                        <h3 class="mt-4 text-xl font-semibold">Aprenda de forma ativa</h3>
                        <p class="mt-3 text-sm text-neutral-300">
                            Converta notas em flashcards, inicie sessões com fila embaralhada, ative o modo foco e pratique com exercícios de Verdadeiro ou Falso, Lacunas e Múltipla Escolha com estatísticas instantâneas.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-left backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.32em] text-indigo-200">03</p>
                        <h3 class="mt-4 text-xl font-semibold">Compartilhe e acompanhe</h3>
                        <p class="mt-3 text-sm text-neutral-300">
                            Gere exportações em PDF para mentores ou equipes, consulte o log de atividades e use as métricas para planejar as próximas revisões.
                        </p>
                    </div>
                </div>
            </section>

            <section id="pricing" class="py-24">
                @php($originalPriceValue = 29.90)
                @php($discountedPriceValue = config('services.stripe.monthly_amount', 14.90))
                @php($originalPrice = number_format($originalPriceValue, 2, ',', '.'))
                @php($monthlyPrice = number_format($discountedPriceValue, 2, ',', '.'))
                @php($discountPercent = (int) max(0, min(100, round(100 - ($discountedPriceValue / $originalPriceValue * 100)))))
                <div class="mx-auto max-w-4xl text-center">
                    <p class="text-sm font-semibold uppercase tracking-[0.32em] text-indigo-600">Plano único</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-tight text-neutral-900 sm:text-4xl">
                        Evolua seus estudos com o {{ config('app.name') }} Premium
                    </h2>
                    <p class="mt-4 text-base leading-relaxed text-neutral-600">
                        Teste todos os recursos por 14 dias, sem precisar de cartão. Se gostar, você ativa a assinatura mensal com cobrança automática — sem burocracia.
                    </p>
                </div>

                <div class="mt-12 grid gap-8 items-start lg:grid-cols-2">
                    <div class="relative rounded-3xl border border-neutral-200 bg-white/90 p-8 shadow-xl shadow-indigo-100">
                        <span class="absolute right-6 top-6 inline-flex items-center rounded-full bg-emerald-600/10 px-4 py-2 text-base font-semibold uppercase tracking-wide text-emerald-700">50% OFF</span>
                        <p class="text-sm font-semibold text-neutral-500">Inclui acesso completo</p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="text-sm font-semibold text-neutral-400 line-through">R$ {{ $originalPrice }}</span>
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-bold tracking-tight text-neutral-900">R$ {{ $monthlyPrice }}</span>
                                <span class="text-sm text-neutral-500">/mês</span>
                            </div>
                        </div>
                        <p class="mt-2 text-sm font-semibold text-emerald-700">Oferta por tempo limitado!</p>
                        <p class="mt-3 text-sm text-neutral-600">
                            <span class="font-bold">14 dias de testes gratuitos</span> · impostos calculados automaticamente · cancelamento a qualquer momento
                        </p>
                        <ul class="mt-8 space-y-3 rounded-2xl bg-neutral-50/80 p-5 text-sm text-neutral-700">
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex size-2 flex-none rounded-full bg-indigo-500"></span>
                                <div>
                                    <p class="font-semibold text-neutral-900">Notas Rápidas</p>
                                    <p>Organize seus estudos com anotações rápidas e funcionais. Utilize tags para manter tudo organizado.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex size-2 flex-none rounded-full bg-indigo-500"></span>
                                <div>
                                    <p class="font-semibold text-neutral-900">Flashcards</p>
                                    <p>Converta qualquer anotação em flashcards. Utilize suas anotações como flashcardspara criar revisões eficientes em um clique.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex size-2 flex-none rounded-full bg-indigo-500"></span>
                                <div>
                                    <p class="font-semibold text-neutral-900">Modo Múltipla Escolha</p>
                                    <p>O sistema transforma automaticamente suas notas, flashcards e PDFs em quizzes com alternativas baralhadas e feedback instantâneo para medir retenção rapidamente.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex size-2 flex-none rounded-full bg-indigo-500"></span>
                                <div>
                                    <p class="font-semibold text-neutral-900">Modo Complete a Lacuna</p>
                                    <p>A plataforma identifica termos-chave nos seus resumos, gera lacunas automaticamente e oferece dicas para você treinar memorização ativa.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex size-2 flex-none rounded-full bg-indigo-500"></span>
                                <div>
                                    <p class="font-semibold text-neutral-900">Modo Verdadeiro ou Falso</p>
                                    <p>Receba afirmações criadas automaticamente a partir do seu conteúdo e pratique conceitos em séries focadas, acompanhando métricas para ajustar o cronograma.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex size-2 flex-none rounded-full bg-indigo-500"></span>
                                <div>
                                    <p class="font-semibold text-neutral-900">Assinatura transparente</p>
                                    <p>Assinatura simples e segura, com cobrança clara, cancelamento a qualquer momento e 14 dias de testes gratuitos.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex size-2 flex-none rounded-full bg-indigo-500"></span>
                                <div>
                                    <p class="font-semibold text-neutral-900">Suporte contínuo</p>
                                    <p>Atualizações constantes e suporte humano em português sempre que precisar.</p>
                                </div>
                            </li>
                        </ul>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-indigo-500">
                                Começar período grátis
                            </a>
                            @endif
                            @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="inline-flex flex-1 items-center justify-center rounded-2xl border border-neutral-200 px-6 py-3 text-sm font-semibold text-neutral-900 transition hover:border-neutral-300">
                                Já tenho conta
                            </a>
                            @endif
                        </div>
                        <p class="mt-4 text-xs text-neutral-500">
                            A cobrança acontece somente após o período de testes.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-neutral-200 bg-neutral-50/80 p-8 text-sm leading-relaxed text-neutral-700">
                        <p class="text-base font-semibold text-neutral-900">
                            Estude com o Booknotes por R$ {{ $monthlyPrice }} e aproveite todo o potencial da plataforma
                        </p>

                        <p class="mt-3">
                            Organize suas anotações, revise com flashcards, exporte seus resumos em PDF e acompanhe sua evolução de forma prática e sem limitações.
                        </p>

                        <p class="mt-3">
                            Converta qualquer conteúdo em exercícios de Múltipla Escolha, Complete a Lacuna e Verdadeiro ou Falso para transformar teoria em prática com feedback imediato.
                        </p>

                        <p class="mt-3">
                            Cada assinatura mantém o Booknotes em constante melhoria — com novos recursos, atualizações frequentes e suporte em português sempre que você precisar.
                        </p>

                        <p class="mt-3">
                            Continue seus estudos com foco, sem anúncios, distrações ou planos confusos. Tudo em um só lugar, criado para quem leva o aprendizado a sério.
                        </p>

                        <p class="mt-3">
                            Precisa de um plano para equipes, mentorias ou instituições? Fale conosco e criamos uma condição personalizada mantendo os 14 dias de teste gratuitos.
                        </p>
                    </div>
                </div>
            </section>

            <section id="testimonials" class="py-24">
                <div class="mx-auto max-w-5xl">
                    <h2 class="text-3xl font-semibold tracking-tight text-neutral-900 sm:text-4xl">Quem já está aprendendo com {{ config('app.name') }}</h2>
                    <p class="mt-4 text-lg text-neutral-600">
                        Estudantes, pesquisadores e profissionais já usam a plataforma para transformar leitura em resultados.
                    </p>

                    <div class="mt-16 grid gap-8 sm:grid-cols-2">
                        <figure class="flex flex-col rounded-2xl border border-neutral-200 bg-neutral-50/60 p-8">
                            <blockquote class="text-sm leading-relaxed text-neutral-700">
                                "O painel diário mostra quantas cartas revisei e quais disciplinas ficaram para trás. Consigo retomar o estudo exatamente de onde parei."
                            </blockquote>
                            <figcaption class="mt-6 text-sm font-semibold text-neutral-900">
                                Ana Luiza Ribeiro — Estudante para concursos
                            </figcaption>
                        </figure>
                        <figure class="flex flex-col rounded-2xl border border-neutral-200 bg-neutral-50/60 p-8">
                            <blockquote class="text-sm leading-relaxed text-neutral-700">
                                "Envio minhas apostilas para a biblioteca de PDFs e exporto notas em PDF para o grupo de pesquisa. Fica tudo padronizado e registrado no log."
                            </blockquote>
                            <figcaption class="mt-6 text-sm font-semibold text-neutral-900">
                                Prof. Thiago Martins — Pesquisa em educação
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </section>

            <section id="faq" class="pb-24">
                <div class="mx-auto max-w-4xl">
                    <h2 class="text-3xl font-semibold tracking-tight text-neutral-900 sm:text-4xl">Perguntas frequentes</h2>
                    <dl class="mt-12 space-y-10 divide-y divide-neutral-200">
                        <div class="pt-0">
                            <dt class="text-base font-semibold text-neutral-900">Preciso instalar algo?</dt>
                            <dd class="mt-3 text-sm text-neutral-600">
                                Não. {{ config('app.name') }} roda no navegador, funciona bem em qualquer dispositivo e oferece autenticação em duas etapas direto pela web.
                            </dd>
                        </div>
                        <div class="pt-10">
                            <dt class="text-base font-semibold text-neutral-900">Como organizo diferentes áreas de estudo?</dt>
                            <dd class="mt-3 text-sm text-neutral-600">
                                Use notebooks para temas amplos, crie disciplinas para cada prova ou módulo e relacione notas e flashcards. Os contadores e filtros ajudam a achar tudo rapidamente.
                            </dd>
                        </div>
                        <div class="pt-10">
                            <dt class="text-base font-semibold text-neutral-900">Consigo estudar com flashcards pelo próprio app?</dt>
                            <dd class="mt-3 text-sm text-neutral-600">
                                Sim. Marque notas como flashcards, abra o hub de estudos, revele respostas no seu ritmo e registre acertos e erros. Cartas incorretas voltam automaticamente ao final da fila.
                            </dd>
                        </div>
                        <div class="pt-10">
                            <dt class="text-base font-semibold text-neutral-900">Minha conta fica protegida?</dt>
                            <dd class="mt-3 text-sm text-neutral-600">
                                Além das verificações por e-mail, você pode habilitar 2FA, acompanhar cada ação no log de atividades e controlar idioma e senha nas telas de configurações.
                            </dd>
                        </div>
                        <div class="pt-10" id="lgpd">
                            <dt class="text-base font-semibold text-neutral-900">Como o {{ config('app.name') }} trata cookies e dados pessoais?</dt>
                            <dd class="mt-3 text-sm text-neutral-600">
                                Mantemos apenas cookies essenciais para autenticação e segurança e solicitamos seu consentimento para métricas opcionais, seguindo os princípios de finalidade, necessidade e transparência previstos na LGPD. Saiba mais na nossa
                                <a href="{{ route('privacy') }}" class="font-semibold text-indigo-600 underline-offset-4 transition hover:text-indigo-500">Política de privacidade</a>
                                ou ajuste o consentimento pelo banner exibido na área deslogada.
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="mb-24">
                <div class="relative overflow-hidden rounded-3xl bg-indigo-600 px-8 py-16 text-white sm:px-12">
                    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.3),transparent_60%)]"></div>
                    <div class="mx-auto flex flex-col items-start justify-between gap-8 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="text-3xl font-semibold tracking-tight">Desbloqueie seu hub de estudos agora</h2>
                            <p class="mt-4 text-sm text-indigo-100">
                                Ative dashboards, biblioteca de PDFs, exportações em PDF e autenticação em duas etapas em menos de dois minutos. Sem cartão, sem pegadinha, cancele quando quiser.
                            </p>
                        </div>

                        @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-3 text-base font-semibold text-indigo-600 text-center transition hover:bg-indigo-50">
                            Criar minha conta gratuita
                        </a>
                        @endif
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-neutral-200 bg-neutral-50 py-12">
            <div class="mx-auto flex max-w-6xl flex-col-reverse gap-6 px-6 text-sm text-neutral-500 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</p>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="#features" class="transition hover:text-neutral-700">Recursos</a>
                    <a href="#workflow" class="transition hover:text-neutral-700">Como funciona</a>
                    <a href="#faq" class="transition hover:text-neutral-700">Perguntas</a>
                    <a href="{{ route('privacy') }}" class="transition hover:text-neutral-700">Privacidade</a>
                    @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="transition hover:text-neutral-700">Área do usuário</a>
                    @endif
                </div>
            </div>
        </footer>
    </div>

    <x-cookie-consent />
    @fluxScripts
</body>

</html>
