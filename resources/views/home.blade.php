@extends('layouts.app')

@section('body-class', 'page-home')

@push('preload')
    <link rel="preload" as="image" href="{{ asset('media/hero-first.webp') }}" fetchpriority="high">
@endpush

@section('content')
    <section class="hero" data-hero>
        <div class="hero__stage">
            <div class="hero__frame">
                <video class="hero__film" data-hero-film
                       muted playsinline autoplay loop preload="auto"
                       poster="{{ asset('media/hero-first.webp') }}"
                       disablepictureinpicture disableremoteplayback
                       aria-hidden="true" tabindex="-1" width="1600" height="896">
                    <source src="{{ asset('media/hero-intro.mp4') }}" type="video/mp4">
                </video>
                <img class="hero__still" src="{{ asset('media/hero-still.webp') }}" alt="" width="1600" height="896" aria-hidden="true">
            </div>
            <h1 class="sr-only">Earth Orchestra</h1>
        </div>

        <div class="hero__inner">
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
            <h2 class="section-title">About Earth Orchestra</h2>
            @foreach ($page->content['intro'] as $paragraph)
                <p class="overture__text" id="intro-{{ $loop->index }}">{{ $paragraph }}</p>
            @endforeach
            <x-read-aloud target="#intro-0" />
        </div>
        <img class="overture__motif" src="{{ asset('media/motif-tile.webp') }}" alt="" loading="lazy" width="1100" height="825">
        <img class="overture__motif overture__motif--left" src="{{ asset('media/motif-tile.webp') }}" alt="" loading="lazy" width="1100" height="825">
    </section>

    <section class="listen" id="listen">
        <div class="wrap">
            <header class="listen__head" data-reveal>
                <h2 class="section-title">How to use</h2>
                <div id="how-text">
                    @foreach ($page->content['how'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
                <x-read-aloud target="#how-text" />
            </header>

            <div class="listen__ways">
                <article class="way">
                    <svg class="way__sketch" viewBox="0 0 600 170" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                        <rect x="4" y="4" width="592" height="162" rx="10" class="sk-frame"/>
                        <path class="sk-contour" d="M20 52c60-26 110 18 170-2s95-30 150-10 90 22 140 2 70-14 100-4"/>
                        <path class="sk-contour" d="M20 92c55-18 105 20 165 0s100-28 155-8 85 18 140 0 75-12 100-2"/>
                        <path class="sk-contour" d="M20 132c65-14 120 14 180-4s100-20 150-4 80 12 135-2 70-8 95 0"/>
                        <circle class="sk-ring" cx="300" cy="86" r="15"/>
                        <circle class="sk-dot" cx="300" cy="86" r="6.5"/>
                        <path class="sk-cursor" d="M307 93l26 11.5-10.5 2.6-3.9 10.4z"/>
                    </svg>
                    <h3><x-icon name="cursor" /> Cursor</h3>
                    <div id="how-cursor">
                        @foreach ($page->content['cursor'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                    <x-read-aloud target="#how-cursor" />
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
        };
    </script>
@endpush
