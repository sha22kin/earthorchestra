@extends('layouts.app', [
    'title' => $visualisation->title,
    'description' => \Illuminate\Support\Str::limit(\App\Support\Prose::plain($visualisation->body[0]['text'] ?? $visualisation->summary), 155),
    'ogImage' => $visualisation->poster_path,
])

@use('App\Support\Prose')

@section('body-class', 'page-article')

@section('content')
<article class="article" style="--accent: {{ $visualisation->accent }}">
    <header class="article__head">
        <div class="wrap wrap--narrow">
            <a class="crumb" href="{{ route('home') }}#{{ $visualisation->slug }}">
                <x-icon name="arrow-l" /> Back to the movement
            </a>
            <p class="eyebrow">Movement {{ $visualisation->numeral }} <span aria-hidden="true">·</span> Notes</p>
            <h1 class="article__title">{{ $visualisation->title }}</h1>
            <p class="article__meta">
                <span>{{ $visualisation->model }}</span>
                <span>{{ $visualisation->reading_minutes }} min read</span>
                <span>Snapshot {{ $visualisation->date_range }}</span>
            </p>
            <x-read-aloud target="#article-body" label="Listen to these notes" class="read-aloud--solid" />
        </div>
    </header>

    <figure class="article__plate">
        <img src="{{ asset($visualisation->poster_path) }}" alt="Still frame from the {{ strtolower($visualisation->short_title) }} visualisation" width="1600" height="800">
        <figcaption>Still from NASA SVS visualisation {{ $visualisation->svs_id }}. <a href="{{ route('home') }}#{{ $visualisation->slug }}">Hear it move <x-icon name="arrow-r" /></a></figcaption>
    </figure>

    <div class="article__layout wrap">
        <div class="article__body prose" id="article-body">
            @foreach ($visualisation->body as $block)
                @if ($block['type'] === 'h')
                    <h2>{{ $block['text'] }}</h2>
                @else
                    <p>{{ Prose::withCitations($block['text']) }}</p>
                @endif
            @endforeach
        </div>

        <aside class="article__aside">
            <div class="factcard">
                <h2>At a glance</h2>
                <dl>
                    <div><dt>Dataset</dt><dd>{{ $visualisation->model }}</dd></div>
                    <div><dt>Measures</dt><dd>{{ $visualisation->measurement }}</dd></div>
                    <div><dt>Unit</dt><dd>{{ $visualisation->legend['unit'] }}</dd></div>
                    <div><dt>Snapshot</dt><dd>{{ $visualisation->date_range }}</dd></div>
                </dl>
                <a class="btn btn--ink btn--block" href="{{ $visualisation->svs_url }}" target="_blank" rel="noopener">
                    NASA's official data <x-icon name="external" />
                </a>
            </div>
        </aside>
    </div>

    <div class="wrap wrap--narrow">
        <p class="article__more">
            Want to learn even more right from NASA's official data?
            <a href="{{ $visualisation->svs_url }}" target="_blank" rel="noopener">Visit the Scientific Visualization Studio <x-icon name="external" /></a>
        </p>

        <section class="sources" aria-labelledby="sources-title">
            <h2 id="sources-title">Bibliography &amp; citations</h2>
            <ol>
                @foreach ($visualisation->citations as $citation)
                    <li id="ref-{{ $citation->position }}">
                        <span>{{ $citation->reference }}</span>
                        @if ($citation->url)
                            <a href="{{ $citation->url }}" target="_blank" rel="noopener">{{ preg_replace('#^https?://(www\.)?#', '', rtrim($citation->url, '/')) }}</a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>

        <aside class="disclaimer">
            <strong>Disclaimer</strong>
            <p>The visualisation displays a static snapshot of NASA's {{ $visualisation->model }} data, covering the period from
                {{ $visualisation->data_starts_on->format('j F Y') }} to {{ $visualisation->data_ends_on->format('j F Y') }}.
                The data is not actively updated, and the visualisation is intended for educational and artistic exploration rather than
                real-time weather monitoring or scientific analysis. For accurate, regularly updated data, refer to
                <a href="{{ $visualisation->svs_url }}" target="_blank" rel="noopener">the official NASA website</a>.</p>
        </aside>
    </div>

    <nav class="pager wrap" aria-label="Other movements">
        @if ($previous)
            <a class="pager__link" href="{{ route('visualisations.show', $previous->slug) }}" style="--accent: {{ $previous->accent }}">
                <small><x-icon name="arrow-l" /> Movement {{ $previous->numeral }}</small>
                <span>{{ $previous->short_title }}</span>
            </a>
        @else
            <span></span>
        @endif
        @if ($next)
            <a class="pager__link pager__link--next" href="{{ route('visualisations.show', $next->slug) }}" style="--accent: {{ $next->accent }}">
                <small>Movement {{ $next->numeral }} <x-icon name="arrow-r" /></small>
                <span>{{ $next->short_title }}</span>
            </a>
        @else
            <a class="pager__link pager__link--next" href="{{ route('home') }}#orchestra">
                <small>Finale <x-icon name="arrow-r" /></small>
                <span>The full orchestra</span>
            </a>
        @endif
    </nav>
</article>
@endsection
