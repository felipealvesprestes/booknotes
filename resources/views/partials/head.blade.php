<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ $title ?? (config('app.name') . ' - Plataforma de estudos com flashcards online, resumos e PDFs') }}
</title>

<meta name="description"
    content="{{ $metaDescription ?? 'Organize resumos, flashcards e PDFs em um só lugar. Plataforma de estudos para concursos, ENEM, faculdade, certificações e quem quer estudar de forma eficiente.' }}">

<meta name="robots" content="{{ $metaRobots ?? 'index,follow' }}">

<link rel="canonical" href="{{ $canonical ?? url()->current() }}">

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Open Graph --}}
<meta property="og:title"
    content="{{ $ogTitle ?? $title ?? (config('app.name') . ' - Plataforma de estudos com flashcards online') }}">
<meta property="og:description"
    content="{{ $ogDescription ?? $metaDescription ?? 'Transforme suas anotações em aprendizado com flashcards, PDFs e métricas de estudo.' }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $canonical ?? url()->current() }}">
<meta property="og:image" content="{{ $ogImage ?? asset('img/share-default.jpg') }}">
<meta property="og:site_name" content="{{ config('app.name') }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title"
    content="{{ $twitterTitle ?? $ogTitle ?? $title ?? (config('app.name') . ' - Plataforma de estudos') }}">
<meta name="twitter:description"
    content="{{ $twitterDescription ?? $ogDescription ?? $metaDescription ?? 'Flashcards online, resumos e PDFs em um único hub de estudos.' }}">
<meta name="twitter:image" content="{{ $twitterImage ?? $ogImage ?? asset('img/share-default.jpg') }}">

{{-- Google  --}}
<meta name="google-site-verification" content="Eq3LfdnEnQfqjmE2AoomRvHQIUJZiyqHw30Lk7INBmA" />

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-532Q36MTXB"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-532Q36MTXB');
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])