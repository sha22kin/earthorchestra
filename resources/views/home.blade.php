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
