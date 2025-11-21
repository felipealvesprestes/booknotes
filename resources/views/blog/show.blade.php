<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="bg-white text-neutral-900 antialiased">
    <div class="bg-white">
        <header class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4 px-6 py-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-neutral-900" wire:navigate>
                    {{ config('app.name') }}
                </a>
                <span class="rounded-full bg-indigo-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] text-indigo-700">
                    Blog
                </span>
            </div>

            <div class="flex items-center gap-3 text-sm font-semibold text-neutral-700">
                <a
                    href="{{ route('blog.index') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-neutral-200 bg-white px-4 py-2 transition hover:border-neutral-300 hover:text-neutral-900"
                    wire:navigate>
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            fill-rule="evenodd"
                            d="M11.28 4.22a.75.75 0 0 1 0 1.06L7.561 9h7.689a.75.75 0 0 1 0 1.5H7.56l3.72 3.72a.75.75 0 0 1-1.06 1.06l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 0 1 1.06 0Z"
                            clip-rule="evenodd" />
                    </svg>
                    Voltar para o blog
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
            @php($publishedAt = $post->published_at->copy()->locale('pt_BR'))

            <article class="space-y-6" aria-labelledby="post-title">
                <header class="space-y-3">
                    <div class="flex items-center gap-3 text-[11px] font-semibold uppercase tracking-[0.25em] text-neutral-500">
                        <time datetime="{{ $post->published_at->toDateString() }}" class="text-indigo-600">
                            {{ $publishedAt->translatedFormat('d \\d\\e F, Y') }}
                        </time>
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-emerald-700">
                            {{ $post->status === 'published' ? 'Publicado' : ucfirst($post->status) }}
                        </span>
                    </div>

                    <h1 id="post-title" class="text-3xl font-semibold leading-tight text-neutral-900 sm:text-4xl">
                        {{ $post->title }}
                    </h1>

                    <p class="text-lg leading-relaxed text-neutral-600">
                        {{ $post->description }}
                    </p>

                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-neutral-600">
                        @foreach ($post->tags as $tag)
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-indigo-700">{{ $tag }}</span>
                        @endforeach
                    </div>
                </header>

                <div class="text-neutral-800 [&_a]:text-indigo-600 [&_a]:underline-offset-4 [&_a:hover]:text-indigo-500 [&_blockquote]:border-l-4 [&_blockquote]:border-indigo-200 [&_blockquote]:ps-4 [&_blockquote]:text-neutral-700 [&_blockquote]:italic [&_h2]:mt-10 [&_h2]:text-2xl [&_h2]:font-semibold [&_h3]:mt-8 [&_h3]:text-xl [&_h3]:font-semibold [&_img]:my-6 [&_img]:max-w-full [&_img]:rounded-2xl [&_li]:mt-2 [&_ol]:ms-6 [&_ol]:list-decimal [&_p]:mt-4 [&_p]:leading-7 [&_pre]:overflow-x-auto [&_pre]:rounded-2xl [&_pre]:bg-neutral-900 [&_pre]:p-4 [&_pre]:text-sm [&_strong]:text-neutral-900 [&_ul]:ms-6 [&_ul]:list-disc">
                    {!! $post->content_html !!}
                </div>
            </article>

            <div class="mt-8 flex items-center justify-between gap-4 text-sm font-semibold text-indigo-700">
                <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 hover:text-indigo-500" wire:navigate>
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            fill-rule="evenodd"
                            d="M11.28 4.22a.75.75 0 0 1 0 1.06L7.561 9h7.689a.75.75 0 0 1 0 1.5H7.56l3.72 3.72a.75.75 0 0 1-1.06 1.06l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 0 1 1.06 0Z"
                            clip-rule="evenodd" />
                    </svg>
                    Voltar para o blog
                </a>

                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-neutral-600 hover:text-neutral-900" wire:navigate>
                    Ir para a home
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.25 4.5a.75.75 0 0 1 .75-.75h8.25a.75.75 0 0 1 .75.75V13.5a.75.75 0 0 1-1.5 0V6.81l-7.719 7.72a.75.75 0 0 1-1.062-1.06L12.44 5.75H6a.75.75 0 0 1-.75-.75Z" />
                    </svg>
                </a>
            </div>
        </main>
    </div>

    @fluxScripts
</body>

</html>
