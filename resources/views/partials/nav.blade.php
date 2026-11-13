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
        <nav aria-label="Mobile">
            <a href="{{ $anchor('overture') }}" style="--i: 0"><small>00</small>Overture</a>
            <a href="{{ $anchor('listen') }}" style="--i: 1"><small>01</small>How to listen</a>
            <a href="{{ $anchor('programme') }}" style="--i: 2"><small>02</small>The six movements</a>
            <a href="{{ $anchor('orchestra') }}" style="--i: 3"><small>03</small>The full orchestra</a>
            <a href="{{ route('about') }}" style="--i: 4"><small>04</small>About us</a>
            <a href="{{ route('credits') }}" style="--i: 5"><small>05</small>Credits</a>
        </nav>

        {{-- A little score: one note per movement, in its own colour. --}}
        <div class="nav-sheet__score" aria-hidden="true">
            <div class="nav-sheet__stave">
                @foreach (\App\Models\Visualisation::ordered() as $v)
                    <span class="nav-sheet__note" style="--c: {{ $v->accent }}; --n: {{ $loop->index }}; --y: {{ [3, 1, 2, 0, 2, 4][$loop->index] ?? 2 }}"></span>
                @endforeach
            </div>
            <p>Six movements, one orchestra</p>
        </div>
    </div>
</header>
