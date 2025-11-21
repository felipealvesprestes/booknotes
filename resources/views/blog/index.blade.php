<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="bg-neutral-50 text-neutral-900 antialiased min-h-screen">
    <div class="min-h-screen bg-white">
        <header class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4 px-6 py-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-neutral-900" wire:navigate>
                    {{ config('app.name') }}
                </a>
                <span class="rounded-full bg-indigo-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] text-indigo-700">Blog</span>
            </div>

            <div class="flex items-center gap-3 text-sm font-semibold text-neutral-700">
                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-neutral-200 bg-white px-4 py-2 transition hover:border-neutral-300 hover:text-neutral-900"
                    wire:navigate>
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            fill-rule="evenodd"
                            d="M11.28 4.22a.75.75 0 0 1 0 1.06L7.561 9h7.689a.75.75 0 0 1 0 1.5H7.56l3.72 3.72a.75.75 0 0 1-1.06 1.06l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 0 1 1.06 0Z"
                            clip-rule="evenodd" />
                    </svg>
                    Voltar
                </a>

                @if (Route::has('login'))
                    <a
                        href="{{ route('login') }}"
                        class="hidden items-center rounded-full border border-neutral-200 bg-white px-4 py-2 text-neutral-700 transition hover:border-neutral-300 hover:text-neutral-900 sm:flex">
                        Entrar
                    </a>
                @endif

                @if (Route::has('register'))
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-white transition hover:bg-indigo-500">
                        Criar conta gratuita
                    </a>
                @endif
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 pb-16">
            <section class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white px-6 py-12 sm:px-10" aria-labelledby="blog-hero-title">
                <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,rgba(99,102,241,0.08),transparent_35%),radial-gradient(circle_at_80%_0%,rgba(59,130,246,0.08),transparent_30%)]"></div>
                <div class="pointer-events-none absolute inset-x-6 -top-6 h-12 bg-gradient-to-b from-neutral-100/90 to-transparent"></div>

                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-indigo-600">Blog do {{ config('app.name') }}</p>
                <h1 id="blog-hero-title" class="mt-3 text-3xl font-semibold text-neutral-900 sm:text-4xl">
                    Aprender pode ser mais leve, organizado e eficiente!
                </h1>
                <p class="mt-3 max-w-3xl text-base leading-relaxed text-neutral-600 sm:text-lg">
                    Tudo sobre técnicas de aprendizagem, recursos do Booknotes e a jornada de quem está reinventando o estudo digital.
                </p>
            </section>

            <section class="mt-6 divide-y divide-neutral-200" aria-label="Lista de publicações">
                @forelse ($posts as $post)
                    @php($publishedAt = $post->published_at->copy()->locale('pt_BR'))
                    <article class="py-10">
                        <header class="flex flex-col gap-3">
                            <div class="flex items-center gap-3 text-[11px] font-semibold uppercase tracking-[0.15em] text-neutral-500">
                                <time datetime="{{ $post->published_at->toDateString() }}" class="text-indigo-600">
                                    {{ $publishedAt->translatedFormat('d \\d\\e F, Y') }}
                                </time>
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-emerald-700">
                                    {{ $post->status === 'published' ? 'Publicado' : ucfirst($post->status) }}
                                </span>
                            </div>

                            <h2 class="text-2xl font-semibold leading-tight text-neutral-900 sm:text-[26px]">
                                <a href="{{ route('blog.show', $post->slug) }}" class="text-indigo-700 transition hover:text-indigo-600">
                                    {{ $post->title }}
                                </a>
                            </h2>

                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.15em] text-neutral-600">
                                @forelse ($post->tags as $tag)
                                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-indigo-700">{{ $tag }}</span>
                                @empty
                                    <span class="rounded-full bg-neutral-100 px-3 py-1 text-neutral-500">Sem tags</span>
                                @endforelse
                            </div>
                        </header>

                        <p class="mt-3 text-base leading-relaxed text-neutral-600">
                            {{ $post->description }}
                        </p>
                    </article>
                @empty
                    <div class="py-10 text-center">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-neutral-500">Nada por aqui ainda</p>
                        <p class="mt-3 text-base text-neutral-600">
                            Suba arquivos Markdown em <code class="rounded bg-neutral-100 px-1.5 py-0.5 text-xs font-semibold">resources/content/blog</code>
                            com o front matter definido para começar a publicar.
                        </p>
                    </div>
                @endforelse
            </section>

            @if ($posts->hasPages())
                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
            @endif
        </main>
    </div>

    @fluxScripts
</body>

</html>
