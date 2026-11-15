<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f3ead8">

    <title>{{ isset($title) ? $title.' · Earth Orchestra' : 'Earth Orchestra - NASA Earth data, played as music' }}</title>
    <meta name="description" content="{{ $description ?? 'Explore NASA Earth data through sound. Six interactive visualisations - temperature, wind, rain, humidity, PM2.5 and ozone - each with its own voice in the orchestra.' }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Earth Orchestra">
    <meta property="og:title" content="{{ $title ?? 'Earth Orchestra' }}">
    <meta property="og:description" content="{{ $description ?? 'NASA Earth data you can see, hear and play with.' }}">
    <meta property="og:image" content="{{ asset($ogImage ?? 'media/movement-1-v2.webp') }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ url()->current() }}">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap">

    @stack('preload')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body-class')">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.nav')

    <main id="main" tabindex="-1">
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.now-playing')

    @stack('scripts')
</body>
</html>
