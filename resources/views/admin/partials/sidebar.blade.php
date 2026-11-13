@php
    $nav = [
        null => [
            ['Dashboard', 'admin.dashboard', 'dashboard', 'admin.dashboard'],
        ],
        'Content' => [
            ['Movements', 'admin.visualisations.index', 'music', 'admin.visualisations.*'],
            ['Pages', 'admin.pages.index', 'file', 'admin.pages.*'],
        ],
        'Audience' => [
            ['Arrangements', 'admin.arrangements.index', 'sliders', 'admin.arrangements.*'],
        ],
        'System' => array_filter([
            auth()->user()->can('manage-users') ? ['Admins & editors', 'admin.users.index', 'users', 'admin.users.*'] : null,
            ['My profile', 'admin.profile.edit', 'user', 'admin.profile.*'],
        ]),
    ];
    $user = auth()->user();
@endphp
<aside class="sidebar" data-sidebar>
    <a class="brand" href="{{ route('admin.dashboard') }}">
        <span class="brand__mark"><x-admin.icon name="logo" /></span>
        <span class="brand__name">Earth Orchestra</span>
    </a>

    <nav class="sidenav" aria-label="Admin">
        @foreach ($nav as $section => $items)
            @if ($section)
                <p class="sidenav__label">{{ $section }}</p>
            @endif
            @foreach ($items as [$label, $route, $icon, $pattern])
                <a href="{{ route($route) }}" @class(['sidenav__link', 'is-active' => request()->routeIs($pattern)]) title="{{ $label }}">
                    <x-admin.icon :name="$icon" />
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        @endforeach

        <p class="sidenav__label">Site</p>
        <a href="{{ route('home') }}" class="sidenav__link" target="_blank" rel="noopener" title="Open website">
            <x-admin.icon name="external" />
            <span>Open website</span>
        </a>
    </nav>

    <div class="whoami">
        <span class="avatar">{{ $user->initials }}</span>
        <span class="whoami__text">
            <strong>{{ $user->name }}</strong>
            <small>{{ $user->email }}</small>
        </span>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="icon-btn" title="Sign out" aria-label="Sign out"><x-admin.icon name="logout" /></button>
        </form>
    </div>
</aside>
