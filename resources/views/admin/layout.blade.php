<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Dashboard' }} · Earth Orchestra Admin</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <script>
        // Apply saved theme and sidebar state before paint to avoid a flash.
        try {
            const t = localStorage.getItem('eo-admin-theme');
            if (t === 'dark' || (!t && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.dataset.theme = 'dark';
            if (localStorage.getItem('eo-admin-sidebar') === 'collapsed') document.documentElement.classList.add('sb-collapsed');
        } catch (e) {}
    </script>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin">
    <div class="shell">
        @include('admin.partials.sidebar')

        <div class="main">
            @include('admin.partials.topbar', ['title' => $title ?? 'Dashboard'])

            <main class="content" id="content">
                @include('admin.partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
    <div class="scrim" data-scrim></div>
</body>
</html>
