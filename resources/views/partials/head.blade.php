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

<script async src="https://www.googletagmanager.com/gtag/js?id=G-K6DV39NELX"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-K6DV39NELX');
</script>

<script>
    !function (w, d, t) {
    w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(
    var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script")
    ;n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};


    ttq.load('D4J281BC77UA1JCPUADG');
    ttq.page();
    }(window, document, 'ttq');
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])