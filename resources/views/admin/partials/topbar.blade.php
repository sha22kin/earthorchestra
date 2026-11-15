@php
    $todaySaves = \App\Models\Arrangement::where('created_at', '>=', today())->count();
@endphp
<header class="topbar">
    <button type="button" class="icon-btn" data-sidebar-toggle aria-label="Toggle sidebar">
        <x-admin.icon name="panel" />
    </button>
    <h1 class="topbar__title">{{ $title }}</h1>

    <div class="topbar__actions">
        @hasSection('action')
            @yield('action')
        @else
            <a class="btn btn--primary btn--icon" href="{{ route('admin.visualisations.index') }}" title="Edit content" aria-label="Edit content">
                <x-admin.icon name="pencil" />
            </a>
        @endif

        <span class="topbar__sep" aria-hidden="true"></span>

        <a class="icon-btn" href="{{ route('home') }}" target="_blank" rel="noopener" title="Open the live site" aria-label="Open the live site">
            <x-admin.icon name="radio" />
        </a>
        <a class="icon-btn" href="{{ route('admin.arrangements.index') }}" title="{{ $todaySaves }} saved today" aria-label="Arrangements saved today: {{ $todaySaves }}">
            <x-admin.icon name="bell" />
            @if ($todaySaves)
                <span class="dot-count">{{ $todaySaves > 9 ? '9+' : $todaySaves }}</span>
            @endif
        </a>
        <button type="button" class="icon-btn" data-theme-toggle aria-label="Toggle dark mode">
            <x-admin.icon name="sun" class="only-light" />
            <x-admin.icon name="moon" class="only-dark" />
        </button>
    </div>
</header>
