<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php($title = config('app.name') . ' - Plataforma de estudos com flashcards, resumos e PDFs')
    @php($metaDescription = 'Use o ' . config('app.name') . ' para criar resumos, organizar cadernos, estudar com flashcards online e acompanhar métricas. Ideal para concursos, ENEM, faculdade e certificações.')
    @php($metaRobots = 'index,follow')
    @php($canonical = url('/'))
    @php($ogTitle = 'Flashcards, simulados e modos de estudo online em um hub de estudos completo')
    @php($ogDescription = 'Organize, revise e memorize conteúdos com uma plataforma de estudos feita para quem leva o aprendizado a sério.')
    @php($ogImage = asset('img/share-default.jpg'))
    @include('partials.head')
</head>

<body class="bg-white text-neutral-900 antialiased">
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[520px] bg-gradient-to-b from-indigo-200/60 via-indigo-100/40 to-transparent"></div>
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 mx-auto h-[420px] max-w-6xl bg-gradient-to-r from-indigo-200/40 via-sky-100/40 to-purple-200/40 blur-[120px]"></div>

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

        <main class="mx-auto max-w-6xl px-6 pb-24 space-y-16 sm:space-y-24">
            <section id="hero" class="relative overflow-hidden rounded-3xl border border-indigo-100 bg-white px-8 pt-16 pb-8 shadow-lg shadow-indigo-100/30 sm:px-12">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-indigo-600">
                        Plataforma de estudos com IA
                    </span>

                    <h1 class="mt-6 text-3xl font-semibold tracking-tight text-neutral-900 leading-tight sm:text-4xl uppercase">
                        Estude com clareza, revise para a prova e <span class="bg-gradient-to-r from-indigo-600 via-purple-500 to-pink-500 bg-clip-text text-transparent">retenha mais</span>
                        conteúdo
                    </h1>

                    <p class="mt-6 text-lg leading-relaxed text-neutral-600">
                        O {{ config('app.name') }} transforma seus PDFs e anotações em <span class="font-semibold text-neutral-900">conteúdos e exercícios</span>.
                        Com <span class="font-semibold">IA</span>, você mantém uma rotina de revisões, pratica no tempo certo e acompanha sua evolução com métricas.
                    </p>

                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-sm uppercase font-semibold text-white transition hover:bg-indigo-500">
                            Criar minha conta gratuita
                        </a>
                        @endif

                        <a
                            href="#features"
                            class="inline-flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-base font-semibold text-indigo-600 transition hover:text-indigo-500">
                            Ver como revisar melhor
                            <svg class="size-4 text-current" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                <path d="M8.00065 1.33331C7.63246 1.33331 7.33398 1.63179 7.33398 1.99998V11.7243L3.4716 7.86195C3.21025 7.6006 2.78792 7.6006 2.52657 7.86195C2.26521 8.12331 2.26521 8.54564 2.52657 8.80699L7.52824 13.8087C7.78959 14.07 8.21192 14.07 8.47327 13.8087L13.4749 8.80699C13.7363 8.54564 13.7363 8.12331 13.4749 7.86195C13.2136 7.6006 12.7912 7.6006 12.5299 7.86195L8.66732 11.7243V1.99998C8.66732 1.63179 8.36884 1.33331 8.00065 1.33331Z" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="mt-14 grid gap-6 sm:grid-cols-3">
                    <div class="rounded-2xl border border-indigo-100/60 bg-indigo-50/80 p-6 text-left">
                        <h3 class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Você ganha clareza</h3>
                        <p class="mt-3 text-sm text-neutral-700 font-medium">
                            Você sabe exatamente o que estudar e o que revisar.
                        </p>
                        <p class="mt-2 text-sm text-neutral-600">
                            Cadernos, disciplinas e notas com filtros rápidos e contadores automáticos.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100/60 bg-white p-6 text-left shadow-sm shadow-indigo-100/50">
                        <h3 class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Você estuda de forma ativa</h3>
                        <p class="mt-3 text-sm text-neutral-700 font-medium">
                            Deixa de só ler e começa a fixar conteúdo.
                        </p>
                        <p class="mt-2 text-sm text-neutral-600">
                            PDFs + notas viram flashcards e exercícios dentro do seu hub de revisões.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100/60 bg-indigo-50/80 p-6 text-left">
                        <h3 class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Você evolui com dados</h3>
                        <p class="mt-3 text-sm text-neutral-700 font-medium">
                            Enxerga progresso real e reforça onde precisar.
                        </p>
                        <p class="mt-2 text-sm text-neutral-600">
                            Modos: V/F, Lacunas, Múltipla Escolha e Simulados com métricas.
                        </p>
                    </div>
                </div>
                <p class="mx-auto mt-6 max-w-3xl text-center text-sm leading-relaxed text-neutral-500">
                    Com apoio da <strong>inteligência artificial</strong>, você cria sua base de estudos e revisa com consistência.
                </p>
            </section>

            <section id="workflow" class="overflow-hidden rounded-3xl bg-neutral-950 px-8 py-20 text-white sm:px-12">
                <div class="mx-auto max-w-4xl text-center">
                    <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                        Do conteúdo à aprovação! Um fluxo simples e eficaz
                    </h2>
                    <p class="mt-4 text-base leading-relaxed text-neutral-300">
                        Pare de recomeçar do zero toda semana. Capture o conteúdo, pratique e revise com consistência — tudo no mesmo lugar.
                    </p>
                </div>
                <div class="mt-16 grid gap-10 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-left backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.32em] text-indigo-200">01</p>
                        <h3 class="mt-4 text-xl font-semibold">Organize sem fricção</h3>
                        <p class="mt-3 text-sm text-neutral-300">
                            Traga PDFs e anotações para cadernos e disciplinas. Você encontra tudo rápido e mantém o estudo “vivo” ao longo das semanas.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-left backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.32em] text-indigo-200">02</p>
                        <h3 class="mt-4 text-xl font-semibold">Pratique para fixar</h3>
                        <p class="mt-3 text-sm text-neutral-300">
                            Transforme conteúdo em flashcards e exercícios. Erre e acerte com feedback imediato — e reforce exatamente o que precisa.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-left backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.32em] text-indigo-200">03</p>
                        <h3 class="mt-4 text-xl font-semibold">Evolua com consistência</h3>
                        <p class="mt-3 text-sm text-neutral-300">
                            Acompanhe sessões, acertos e histórico. Você sabe onde está forte e o que revisar antes da prova.
                        </p>
                    </div>
                </div>
            </section>

            <section id="imagem">
                <div class="mx-auto max-w-6xl">
                    <div class="max-w-3xl mx-auto text-center">
                        <h2 class="text-4xl font-semibold tracking-tight text-neutral-900 sm:text-5xl">
                            Plataforma de estudos completa para quem quer evoluir
                        </h2>
                        <p class="mt-4 text-base leading-relaxed text-neutral-600">
                            Um pouco da experiência dentro do {{ config('app.name') }} — sem tirar o foco do que importa: estudar com eficiência.
                        </p>
                    </div>

                    <div class="mt-12">
                        <figure class="group rounded-2xl border border-neutral-200 bg-white shadow-[0_18px_60px_rgba(15,23,42,0.06)] overflow-hidden">
                            <div class="border-b border-neutral-100 bg-neutral-50/80 px-4 py-2 flex items-center gap-2">
                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-red-300"></span>
                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                                <span class="ml-3 text-[11px] font-medium text-neutral-400 truncate">
                                    Painel de estudos
                                </span>
                            </div>

                            <div class="relative bg-neutral-900/5">
                                <img
                                    src="{{ asset('img/landing/dashboard-pt.png') }}"
                                    alt="Painel de estudos do Booknotes com métricas, cadernos e sessões."
                                    class="w-full h-auto max-md:blur-[1px]" />
                            </div>

                            <figcaption class="px-5 py-4 text-xs leading-snug text-neutral-600 border-t border-neutral-100 bg-white/90">
                                Visão geral com cadernos, disciplinas, cards revisados e últimas sessões de estudo.
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </section>

            <section id="bloco-cta">
                <div class="relative overflow-hidden rounded-3xl bg-indigo-600 px-8 py-16 text-white sm:px-12">
                    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.3),transparent_60%)]"></div>
                    <div class="mx-auto flex flex-col items-start justify-between gap-8 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="text-3xl font-semibold tracking-tight">Desbloqueie seu hub de estudos com IA</h2>
                            <p class="mt-4 text-sm text-indigo-100">
                                14 dias grátis, sem cartão. Use IA, PDFs, modos de estudo e métricas para construir consistência — e saber o que revisar antes da prova.
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

            <section id="features">
                <div class="relative overflow-hidden rounded-3xl border border-neutral-100 bg-white px-8 py-20 shadow-[0_18px_60px_rgba(15,23,42,0.08)] sm:px-14">
                    <div class="pointer-events-none absolute inset-x-0 top-0 mx-auto h-40 max-w-5xl bg-gradient-to-r from-indigo-200/50 via-white to-sky-200/40 blur-[120px]"></div>
                    <div class="relative space-y-20">
                        <div class="mx-auto max-w-3xl text-center space-y-6">
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-indigo-600">
                                Para revisar melhor para a prova
                            </span>
                            <h2 class="text-3xl font-semibold tracking-tight text-neutral-900 sm:text-4xl">
                                Transforme seus PDFs em revisões — e retenha mais, com consistência
                            </h2>
                            <p class="text-base leading-relaxed text-neutral-600">
                                O {{ config('app.name') }} converte PDFs e anotações em
                                <span class="font-semibold text-neutral-900">flashcards e exercícios</span>.
                                A IA acelera a criação do conteúdo, e você pratica e revisa com consistência.
                            </p>
                        </div>
                        <div class="grid gap-10 lg:grid-cols-3">
                            <div class="rounded-2xl border border-neutral-200 bg-neutral-50/60 p-8">
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-indigo-500">
                                    Organize
                                </p>
                                <h3 class="mt-3 text-xl font-semibold text-neutral-900">
                                    Clareza total do que estudar
                                </h3>
                                <p class="mt-3 text-sm text-neutral-600">
                                    Estruture seus estudos em cadernos, disciplinas e notas.
                                    Tudo fica conectado, com filtros e contadores automáticos.
                                </p>
                                <ul class="mt-5 space-y-2 text-sm text-neutral-600">
                                    <li>• Notas rápidas com tags</li>
                                    <li>• Base conhecimento personalizada</li>
                                    <li>• Biblioteca de PDFs integrada</li>
                                </ul>
                            </div>
                            <div class="rounded-2xl border border-neutral-200 bg-neutral-50/60 p-8">
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-indigo-500">
                                    Aprenda
                                </p>
                                <h3 class="mt-3 text-xl font-semibold text-neutral-900">
                                    Estudo ativo que realmente fixa
                                </h3>
                                <p class="mt-3 text-sm text-neutral-600">
                                    Não é só leitura. Você transforma PDFs e notas em prática: flashcards e exercícios para estudar
                                    e reforçar os pontos certos antes da prova.
                                </p>
                                <ul class="mt-5 space-y-2 text-sm text-neutral-600">
                                    <li>• PDF → flashcards com IA</li>
                                    <li>• Múltipla escolha, V/F e Lacunas</li>
                                    <li>• Simulados completos</li>
                                </ul>
                            </div>
                            <div class="rounded-2xl border border-neutral-200 bg-neutral-50/60 p-8">
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-indigo-500">
                                    Evolua
                                </p>
                                <h3 class="mt-3 text-xl font-semibold text-neutral-900">
                                    Veja sua evolução com dados reais
                                </h3>
                                <p class="mt-3 text-sm text-neutral-600">
                                    Acompanhe hábitos, sessões, taxa de acertos e histórico.
                                    Saiba exatamente onde melhorar antes da próxima prova.
                                </p>
                                <ul class="mt-5 space-y-2 text-sm text-neutral-600">
                                    <li>• Painel 360º de estudos</li>
                                    <li>• Métricas de desempenho</li>
                                    <li>• Logs e histórico completo</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mx-auto max-w-4xl rounded-3xl bg-indigo-600 px-8 py-12 text-center text-white">
                            <h3 class="text-2xl font-semibold">
                                Inteligência Artificial para criar sua base de estudos
                            </h3>

                            <p class="mt-4 text-sm text-indigo-100 leading-relaxed">
                                A IA transforma seus PDFs e textos em flashcards prontos para estudo.
                                Você organiza, pratica e revisa no seu ritmo, com controle total do conteúdo.
                            </p>
                            <div class="mt-8 flex flex-wrap justify-center gap-3 text-xs font-semibold uppercase tracking-[0.2em]">
                                <span class="rounded-full bg-white/10 px-4 py-2">PDF → Flashcards</span>
                                <span class="rounded-full bg-white/10 px-4 py-2">Criação automática</span>
                                <span class="rounded-full bg-white/10 px-4 py-2">Controle do usuário</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="seguranca" class="overflow-hidden rounded-3xl border border-neutral-200 bg-neutral-950/95 px-8 py-16 text-white shadow-xl shadow-neutral-900/10 sm:px-12 sm:py-20">
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

            <section id="pricing" class="bg-white">
                @php($monthlyPriceValue = config('services.stripe.monthly_amount', 24.90))
                @php($monthlyPrice = number_format($monthlyPriceValue, 2, ',', '.'))

                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    {{-- Header --}}
                    <div class="mx-auto max-w-4xl text-center space-y-3">
                        <span class="inline-flex items-center justify-center rounded-full bg-indigo-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-indigo-600">
                            Planos Booknotes
                        </span>
                        <h2 class="text-4xl font-semibold tracking-tight text-balance text-neutral-900 sm:text-5xl">
                            Estude com consistência. Evolua com clareza.
                        </h2>
                    </div>

                    <p class="mx-auto mt-6 max-w-3xl text-center text-lg font-medium text-pretty text-neutral-600 sm:text-xl leading-8">
                        IA para gerar conteúdo, modos completos de estudo e um roteiro semanal para você manter ritmo — sem perder tempo organizando tudo do zero.
                    </p>

                    {{-- Pricing cards --}}
                    <div class="isolate mx-auto mt-16 grid max-w-md grid-cols-1 gap-y-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                        {{-- Free trial --}}
                        <div class="-mr-px flex flex-col justify-between rounded-3xl bg-white p-8 ring-1 ring-neutral-200 lg:mt-8 lg:rounded-r-none xl:p-10">
                            <div>
                                <div class="flex items-center justify-between gap-x-4">
                                    <h3 id="tier-trial" class="text-lg font-semibold text-neutral-900">Teste gratuito</h3>
                                    <span class="inline-flex items-center rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-semibold text-neutral-700">
                                        Sem cartão
                                    </span>
                                </div>

                                <p class="mt-4 text-sm text-neutral-600">
                                    14 dias para experimentar a rotina completa do Booknotes: IA, estudo ativo e exportações.
                                </p>

                                <p class="mt-6 flex items-baseline gap-x-1">
                                    <span class="text-4xl font-semibold tracking-tight text-neutral-900">R$ 0</span>
                                    <span class="text-sm font-semibold text-neutral-600">/14 dias</span>
                                </p>

                                <ul role="list" class="mt-8 space-y-3 text-sm text-neutral-600">
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        IA para gerar flashcards e resumos
                                    </li>
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        Exportações em PDF habilitadas
                                    </li>
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        Modos ativos: Múltipla escolha, Lacunas e V/F
                                    </li>
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        Cancele quando quiser, sem burocracia
                                    </li>
                                </ul>
                            </div>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                aria-describedby="tier-trial"
                                class="mt-8 block rounded-md px-3 py-2 text-center text-sm font-semibold text-indigo-600 ring-1 ring-indigo-200 hover:ring-indigo-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Começar grátis
                                </a>
                            @endif
                        </div>

                        {{-- Premium --}}
                        <div class="flex flex-col justify-between rounded-3xl bg-white p-8 ring-2 ring-indigo-400 lg:z-10 lg:rounded-b-none xl:p-10">
                            <div>
                                <div class="flex items-center justify-between gap-x-4">
                                    <h3 id="tier-premium" class="text-lg font-semibold text-indigo-600">
                                        {{ config('app.name') }} Premium
                                    </h3>

                                    <span class="shrink-0 whitespace-nowrap inline-flex items-center rounded-full bg-indigo-600/10 px-3 py-1 text-xs font-semibold leading-none text-indigo-600">
                                        Mais popular
                                    </span>
                                </div>

                                <p class="mt-4 text-sm text-neutral-600">
                                    Para quem quer estudar de verdade: criar conteúdo rápido, revisar com eficiência e acompanhar evolução.
                                </p>

                                <p class="mt-6 flex items-baseline gap-x-1">
                                    <span class="text-4xl font-semibold tracking-tight text-neutral-900">R$ {{ $monthlyPrice }}</span>
                                    <span class="text-sm font-semibold text-neutral-600">/mês</span>
                                </p>

                                <ul role="list" class="mt-8 space-y-3 text-sm text-neutral-600">
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        IA para flashcards e resumos com fluxo guiado
                                    </li>
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        Notas, flashcards e biblioteca de PDFs em um só lugar
                                    </li>
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        Modos de estudo: Múltipla escolha, Lacunas, V/F e Simulado
                                    </li>
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        Roteiro semanal com tarefas diárias automáticas
                                    </li>
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        Métricas de desempenho e histórico de evolução
                                    </li>
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        Suporte humano em português
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-8 flex flex-col gap-3">
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                    aria-describedby="tier-premium"
                                    class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                        Assinar o Premium
                                    </a>
                                @endif

                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}"
                                    class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-neutral-900 ring-1 ring-neutral-200 hover:ring-neutral-300">
                                        Já tenho conta
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Teams --}}
                        <div class="-ml-px flex flex-col justify-between rounded-3xl bg-white p-8 ring-1 ring-neutral-200 lg:mt-8 lg:rounded-l-none xl:p-10">
                            <div>
                                <div class="flex items-center justify-between gap-x-4">
                                    <h3 id="tier-teams" class="text-lg font-semibold text-neutral-900">
                                        Mentorias e instituições
                                    </h3>

                                    <span class="shrink-0 whitespace-nowrap inline-flex items-center rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold leading-none text-neutral-700">
                                        Sob medida
                                    </span>
                                </div>

                                <p class="mt-4 text-sm text-neutral-600">
                                    Para quem precisa de onboarding assistido, exportações frequentes, permissões e visão compartilhada.
                                </p>

                                <p class="mt-6 flex items-baseline gap-x-1">
                                    <span class="text-4xl font-semibold tracking-tight text-neutral-900">Personalizado</span>
                                </p>

                                <ul role="list" class="mt-8 space-y-3 text-sm text-neutral-600">
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        Múltiplos perfis com histórico independente
                                    </li>
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        Permissões, exportações em lote e governança de conteúdo
                                    </li>
                                    <li class="flex gap-x-3">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                            <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                        </svg>
                                        Suporte dedicado e onboarding guiado
                                    </li>
                                </ul>
                            </div>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                aria-describedby="tier-teams"
                                class="mt-8 block rounded-md px-3 py-2 text-center text-sm font-semibold text-indigo-600 ring-1 ring-indigo-200 hover:ring-indigo-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Quero levar para meu time
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Everything included --}}
                    <div class="mx-auto mt-16 max-w-5xl rounded-3xl bg-neutral-50/80 p-8 ring-1 ring-neutral-200 sm:p-10">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <h3 class="text-2xl font-semibold text-neutral-900">Tudo que você ganha no Booknotes</h3>
                        </div>

                        <ul class="mt-6 grid gap-4 text-sm text-neutral-700 sm:grid-cols-2">
                            <li class="flex gap-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                    <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Assistente com IA para flashcards e resumos
                            </li>
                            <li class="flex gap-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                    <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Flashcards a partir de PDFs (20, 30 ou 50 cards)
                            </li>
                            <li class="flex gap-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                    <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Notas rápidas com tags e filtros
                            </li>
                            <li class="flex gap-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                    <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Biblioteca de PDFs + exportações configuráveis
                            </li>
                            <li class="flex gap-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                    <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Roteiro semanal com tarefas diárias automáticas
                            </li>
                            <li class="flex gap-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                    <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Múltipla escolha com feedback imediato
                            </li>
                            <li class="flex gap-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                    <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Complete a Lacuna para memorização ativa
                            </li>
                            <li class="flex gap-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                    <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Verdadeiro/Falso com métricas instantâneas
                            </li>
                            <li class="flex gap-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                    <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Simulados (10, 30 ou 50 questões) com estatísticas
                            </li>
                            <li class="flex gap-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-6 w-5 flex-none text-indigo-600">
                                    <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Assinatura transparente + suporte contínuo em português
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="bloco-ia" class="relative overflow-hidden rounded-3xl border border-indigo-100 bg-white px-8 py-20 shadow-[0_18px_60px_rgba(15,23,42,0.08)] sm:px-12">
                <div class="pointer-events-none absolute inset-x-0 bottom-[-28%] h-[360px] bg-[radial-gradient(circle_at_bottom,_rgba(129,140,248,0.45),_rgba(236,72,153,0.2),_transparent_70%)] blur-3xl"></div>
                <div class="mx-auto max-w-3xl text-center">
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-indigo-600">
                        Novo assistente de IA
                    </span>
                    <h2 class="mt-6 text-4xl font-semibold tracking-tight text-neutral-900 sm:text-5xl">
                        Estude com a Inteligência Artificial do {{ config('app.name') }}
                    </h2>
                    <p class="mx-auto mt-6 max-w-3xl text-lg leading-relaxed text-neutral-600">
                        Transforme seus materiais em um plano de estudo prático. A IA ajuda a preparar exercícios, reforça automaticamente o que você erra e deixa claro o que revisar antes da prova.
                    </p>

                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row sm:gap-6">
                        @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="relative z-10 rounded-lg bg-gradient-to-r bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/40 transition hover:brightness-110 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            Criar minha conta gratuita
                        </a>
                        @endif
                        <a
                            href="#features"
                            class="text-sm font-semibold text-neutral-900 transition hover:text-neutral-600">
                            Ver como funciona <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
                <svg viewBox="0 0 1024 1024" aria-hidden="true" class="pointer-events-none absolute top-1/2 left-1/2 -z-10 h-96 w-96 -translate-x-1/2 -translate-y-1/2">
                    <circle r="512" cx="512" cy="512" fill="url(#ai-spotlight)" fill-opacity="0.7" />
                    <defs>
                        <radialGradient id="ai-spotlight">
                            <stop stop-color="#818CF8" />
                            <stop offset="1" stop-color="#E879F9" />
                        </radialGradient>
                    </defs>
                </svg>
            </section>

            <section id="testimonials">
                <div class="mx-auto">
                    <h2 class="text-4xl font-semibold tracking-tight text-neutral-900 sm:text-5xl">Quem já está aprendendo com {{ config('app.name') }}</h2>
                    <p class="mt-4 text-lg text-neutral-600">
                        Estudantes, pesquisadores e profissionais já usam a plataforma para transformar leitura em resultados.
                    </p>

                    <div class="mt-10 grid gap-8 sm:grid-cols-3">
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
                        <figure class="flex flex-col rounded-2xl border border-neutral-200 bg-neutral-50/60 p-8">
                            <blockquote class="text-sm leading-relaxed text-neutral-700">
                                "As ferramentas de lacunas e múltipla escolha aumentaram muito a retenção do meu time. Agora conseguimos revisar conteúdos complexos de forma prática."
                            </blockquote>
                            <figcaption class="mt-6 text-sm font-semibold text-neutral-900">
                                Marcos Silva — Mentor de equipes de estudo
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </section>

            <section id="faq" class="bg-white">
                <div class="mx-auto max-w-7xl">
                    <h2 class="text-4xl font-semibold tracking-tight text-neutral-900 sm:text-5xl">
                        Perguntas frequentes
                    </h2>

                    <p class="mt-6 max-w-2xl text-base/7 text-neutral-600">
                        Ficou com alguma dúvida e não encontrou a resposta aqui?
                        Fale com a gente pelo
                        <a href="mailto:contato@booknotes.com.br" class="font-semibold text-indigo-600 hover:text-indigo-500">
                            Central de suporte
                        </a>
                        e respondemos o quanto antes.
                    </p>

                    <div class="mt-16 sm:mt-20">
                        <dl class="space-y-16 sm:grid sm:grid-cols-2 sm:space-y-0 sm:gap-x-6 sm:gap-y-16 lg:grid-cols-3 lg:gap-x-10">
                            <div>
                                <dt class="text-base/7 font-semibold text-neutral-900">Preciso instalar algo para estudar com o Booknotes?</dt>
                                <dd class="mt-2 text-base/7 text-neutral-600">
                                    Não. O {{ config('app.name') }} funciona direto no navegador, em qualquer dispositivo, sem downloads.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-base/7 font-semibold text-neutral-900">Para qual público o uso do Booknotes é indicado?</dt>
                                <dd class="mt-2 text-base/7 text-neutral-600">
                                    Para estudantes de vestibular, concursos, faculdade e certificações — e para quem quer estudar com método e consistência.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-base/7 font-semibold text-neutral-900">Como é feita a organização dos meus estudos?</dt>
                                <dd class="mt-2 text-base/7 text-neutral-600">
                                    Crie cadernos, disciplinas e notas. Transforme notas em flashcards para usar nos modos de estudos.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-base/7 font-semibold text-neutral-900">Consigo estudar com flashcards pelo próprio app?</dt>
                                <dd class="mt-2 text-base/7 text-neutral-600">
                                    Sim. Você estuda no hub, registra acertos/erros e reforça automaticamente os cards que erra.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-base/7 font-semibold text-neutral-900">Quais são os modos de estudo que estão disponíveis?</dt>
                                <dd class="mt-2 text-base/7 text-neutral-600">
                                    Flashcards, Múltipla Escolha, Complete as Lacunas, Verdadeiro/Falso e Simulados — todos baseados no seu conteúdo.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-base/7 font-semibold text-neutral-900">Posso enviar PDFs e gerar conteúdo a partir deles?</dt>
                                <dd class="mt-2 text-base/7 text-neutral-600">
                                    Sim. Você envia PDFs para sua biblioteca e pode gerar flashcards (e resumos, quando disponível no seu plano) com fluxo guiado.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-base/7 font-semibold text-neutral-900">A IA tem limite de uso para gerar conteúdo?</dt>
                                <dd class="mt-2 text-base/7 text-neutral-600">
                                    Sim, para garantir qualidade e sustentabilidade. Os limites ficam claros dentro da plataforma e foram pensados para rotina de estudos instensas.
                                </dd>
                            </div>

                            <div>
                                <dt class="text-base/7 font-semibold text-neutral-900">Minha conta realmente fica protegida de acessos indevidos?</dt>
                                <dd class="mt-2 text-base/7 text-neutral-600">
                                    Sim. Você pode habilitar 2FA, e ações importantes ficam registradas no log (ex.: exportações, estudos, alterações e exclusões).
                                </dd>
                            </div>

                            <div id="lgpd">
                                <dt class="text-base/7 font-semibold text-neutral-900">Como o {{ config('app.name') }} trata cookies e dados pessoais?</dt>
                                <dd class="mt-2 text-base/7 text-neutral-600">
                                    Nós usamos apenas os cookies essenciais para a aplicação e seguimos a LGPD. Você pode ler detalhes na
                                    <a href="{{ route('privacy') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">
                                        Política de privacidade
                                    </a>.
                                </dd>
                            </div>
                        </dl>
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
                    <a
                        href="https://www.instagram.com/booknotes_app/"
                        class="inline-flex items-center justify-center text-neutral-500 transition hover:text-neutral-700"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Instagram do Booknotes">
                        <span class="sr-only">Instagram</span>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.5"
                                stroke-linejoin="round" />
                            <circle cx="12" cy="12" r="4.5" stroke="currentColor" stroke-width="1.5" />
                            <circle cx="17" cy="7" r="1.1" fill="currentColor" />
                        </svg>
                    </a>
                </div>
            </div>
        </footer>
    </div>

    <x-cookie-consent />
    @fluxScripts
</body>

</html>
