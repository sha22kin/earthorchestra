<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · Earth Orchestra Admin</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap">
    <script>try { const t = localStorage.getItem('eo-admin-theme'); if (t === 'dark' || (!t && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.dataset.theme = 'dark'; } catch (e) {}</script>
    @vite(['resources/css/admin.css'])
</head>
<body class="admin auth">
    <main class="auth__card">
        <a class="brand brand--center" href="{{ route('home') }}">
            <span class="brand__mark"><x-admin.icon name="logo" /></span>
            <span class="brand__name">Earth Orchestra</span>
        </a>
        <h1>Sign in to the admin</h1>
        <p class="muted">Manage movements, pages and saved arrangements.</p>

        @if (session('status'))
            <div class="toast toast--ok toast--static"><x-admin.icon name="check" /><span>{{ session('status') }}</span></div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="stack">
            @csrf
            <x-admin.field name="email" label="Email" type="email" :value="old('email')" autocomplete="username" required />
            <x-admin.field name="password" label="Password" type="password" autocomplete="current-password" required />
            <label class="checkline">
                <input type="checkbox" name="remember" value="1"> Keep me signed in
            </label>
            <button type="submit" class="btn btn--primary btn--block">Sign in</button>
        </form>
    </main>
</body>
</html>
