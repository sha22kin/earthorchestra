@extends('layouts.app')

@section('body-class', 'page-home')

@push('preload')
    <link rel="preload" as="image" href="{{ asset('media/hero-first.webp') }}" fetchpriority="high">
@endpush

@section('content')
    <section class="hero" data-hero>
        <div class="hero__stage">
            <video class="hero__film" data-hero-film
                   muted playsinline autoplay preload="auto"
                   poster="{{ asset('media/hero-first.webp') }}"
                   disablepictureinpicture disableremoteplayback
                   aria-hidden="true" tabindex="-1" width="1600" height="896">
                <source src="{{ asset('media/hero-intro.mp4') }}" type="video/mp4">
            </video>
            <img class="hero__still" src="{{ asset('media/hero-still.webp') }}" alt="" width="1600" height="896" aria-hidden="true">
            <h1 class="sr-only">Earth Orchestra</h1>
        </div>

        <div class="hero__inner">
            <p class="hero__meta">
                <span>Six movements</span>
                <span>GEOS-FP &amp; GEOS-CF</span>
                <span>Autumn 2026</span>
            </p>
            <div class="hero__actions">
                <a class="btn btn--ink" href="#overture">Take your seat <x-icon name="arrow-d" /></a>
            </div>
        </div>
    </section>

    @if ($arrangement)
        <aside class="shared-banner" role="status">
            <x-icon name="note" />
            <p>Someone shared an arrangement with you: <strong>{{ $arrangement->displayTitle() }}</strong>.
               <a href="#orchestra">Listen to it at the bottom of the page</a>.</p>
        </aside>
    @endif

    <section class="overture" id="overture">
        <div class="overture__inner" data-reveal>
            <p class="eyebrow">Overture</p>
            <h2 class="section-title">About Earth Orchestra</h2>
            @foreach ($page->content['intro'] as $paragraph)
                <p class="overture__text" id="intro-{{ $loop->index }}">{{ $paragraph }}</p>
            @endforeach
            <x-read-aloud target="#intro-0" />
        </div>
        <img class="overture__motif" src="{{ asset('media/motif-tile.webp') }}" alt="" loading="lazy" width="1100" height="825">
    </section>

    <section class="listen" id="listen">
        <div class="wrap">
            <header class="listen__head" data-reveal>
                <p class="eyebrow">Before the music starts</p>
                <h2 class="section-title">How to use</h2>
                <div id="how-text">
                    @foreach ($page->content['how'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
                <x-read-aloud target="#how-text" />
            </header>

            <div class="listen__ways">
                <article class="way" data-reveal>
                    <svg class="way__sketch" viewBox="0 0 240 130" aria-hidden="true">
                        <rect x="6" y="6" width="228" height="118" rx="6" class="sk-frame"/>
                        <path class="sk-contour" d="M14 40c30-14 52 10 80-2s46-22 70-8 40 14 62 4"/>
                        <path class="sk-contour" d="M14 70c26-10 50 12 84 0s44-18 72-4 38 10 56 2"/>
                        <path class="sk-contour" d="M14 100c34-8 58 8 88-2s50-12 74-2 32 6 50 0"/>
                        <circle class="sk-dot" cx="152" cy="62" r="5"/>
                        <circle class="sk-ring" cx="152" cy="62" r="11"/>
                        <path class="sk-cursor" d="M96 50l20 9-8 2-3 8z"/>
                    </svg>
                    <h3><x-icon name="cursor" /> Cursor</h3>
                    <div id="how-cursor">
                        @foreach ($page->content['cursor'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                    <x-read-aloud target="#how-cursor" />
                </article>

                <article class="way" data-reveal>
                    <svg class="way__sketch" viewBox="0 0 240 130" aria-hidden="true">
                        <rect x="6" y="6" width="228" height="118" rx="6" class="sk-frame"/>
                        <path class="sk-contour" d="M14 40c30-14 52 10 80-2s46-22 70-8 40 14 62 4"/>
                        <path class="sk-contour" d="M14 70c26-10 50 12 84 0s44-18 72-4 38 10 56 2"/>
                        <path class="sk-contour" d="M14 100c34-8 58 8 88-2s50-12 74-2 32 6 50 0"/>
                        <path class="sk-line" d="M138 8v114"/>
                        <circle class="sk-grip" cx="138" cy="65" r="9"/>
                        <path class="sk-arrows" d="M118 65l-8-6v12zM158 65l8-6v12z"/>
                    </svg>
                    <h3><x-icon name="slider" /> Slider</h3>
                    <div id="how-slider">
                        @foreach ($page->content['slider'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                    <x-read-aloud target="#how-slider" />
                </article>
            </div>
        </div>
    </section>

    <nav class="programme" id="programme" aria-label="The six movements">
        <div class="wrap">
            <p class="eyebrow" data-reveal>Tonight's programme</p>
            <ol class="programme__list">
                @foreach ($visualisations as $v)
                    <li data-reveal style="--accent: {{ $v->accent }}; --i: {{ $loop->index }}">
                        <a href="#{{ $v->slug }}">
                            <span class="programme__num">{{ $v->numeral }}</span>
                            <span class="programme__name">{{ $v->short_title }}</span>
                            <span class="programme__dots" aria-hidden="true"></span>
                            <span class="programme__mode">{{ $v->interaction }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </nav>

    <div class="movements">
        @foreach ($visualisations as $v)
            @include('partials.movement', ['v' => $v])
        @endforeach
    </div>

    @include('partials.orchestra')
@endsection

@push('scripts')
    <script>
        window.EARTH_ORCHESTRA = {
            tracks: @json($playerConfig),
            arrangement: @json($arrangement ? ['code' => $arrangement->code, 'title' => $arrangement->displayTitle(), 'levels' => $arrangement->levels] : null),
            saveUrl: @json(route('api.arrangements.store')),
        };
    </script>
@endpush
