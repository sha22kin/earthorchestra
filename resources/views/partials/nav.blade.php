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

    <div class="nav-sheet" id="nav-sheet" hidden data-nav-sheet>
        <nav aria-label="Mobile">
            <a href="{{ $anchor('overture') }}"><small>00</small>Overture</a>
            <a href="{{ $anchor('listen') }}"><small>01</small>How to listen</a>
            <a href="{{ $anchor('programme') }}"><small>02</small>The six movements</a>
            <a href="{{ $anchor('orchestra') }}"><small>03</small>The full orchestra</a>
            <a href="{{ route('about') }}"><small>04</small>About us</a>
            <a href="{{ route('credits') }}"><small>05</small>Credits</a>
        </nav>
        <img class="nav-sheet__cloud" src="{{ asset('media/cloud-3.webp') }}" alt="" loading="lazy">
    </div>
</header>
