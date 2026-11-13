@php
    $onHome = request()->routeIs('home', 'arrangements.show');
    $anchor = fn (string $id) => $onHome ? "#{$id}" : route('home')."#{$id}";
@endphp
<header class="site-nav" data-nav>
    <div class="site-nav__inner">
        <a class="site-nav__brand" href="{{ route('home') }}" aria-label="Earth Orchestra — home">
            <img src="{{ asset('media/title.webp') }}" alt="" width="120" height="69">
        </a>

        <nav class="site-nav__links" aria-label="Primary">
            <a href="{{ $anchor('listen') }}">How to listen</a>
            <a href="{{ $anchor('programme') }}">Movements</a>
            <a href="{{ $anchor('orchestra') }}">The orchestra</a>
            <a href="{{ route('about') }}" @class(['is-current' => request()->routeIs('about')])>About</a>
            <a href="{{ route('credits') }}" @class(['is-current' => request()->routeIs('credits')])>Credits</a>
        </nav>

        <button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="nav-sheet" data-nav-toggle>
            <span class="sr-only">Menu</span>
            <span class="site-nav__bars" aria-hidden="true"><i></i><i></i></span>
        </button>
    </div>

    <div class="nav-sheet" id="nav-sheet" data-nav-sheet aria-hidden="true" inert>
        <nav class="nav-sheet__links" aria-label="Mobile">
            @foreach ([
                ['overture', 'Overture'], ['listen', 'How to listen'], ['programme', 'The six movements'],
                ['orchestra', 'The full orchestra'], [route('about'), 'About us'], [route('credits'), 'Credits'],
            ] as $i => [$target, $label])
                <a href="{{ str_starts_with($target, 'http') ? $target : $anchor($target) }}" style="--i: {{ $i }}">
                    <small>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</small>
                    <span>{{ $label }}</span>
                    <x-icon name="arrow-r" />
                </a>
            @endforeach
        </nav>

        <div class="nav-sheet__foot" style="--i: 6">
            <a class="nav-sheet__cta" href="{{ $anchor('programme') }}">
                <span>
                    <small>Tonight's programme</small>
                    Six movements, one orchestra
                </span>
                <span class="nav-sheet__disc"><x-icon name="play" /></span>
            </a>
            <p class="nav-sheet__meta"><span>NASA GEOS-FP &amp; GEOS-CF data</span> <i aria-hidden="true">◆</i> <span>Autumn 2026</span></p>
        </div>

        <div class="nav-sheet__sky" aria-hidden="true">
            <img class="nav-sheet__cloud nav-sheet__cloud--a" src="{{ asset('media/cloud-1.webp') }}" alt="" loading="lazy">
            <img class="nav-sheet__cloud nav-sheet__cloud--b" src="{{ asset('media/cloud-2.webp') }}" alt="" loading="lazy">
        </div>
    </div>
</header>
