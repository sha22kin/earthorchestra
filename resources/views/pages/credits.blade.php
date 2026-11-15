@extends('layouts.app', ['title' => $page->title, 'description' => $page->lede])

@section('body-class', 'page-credits')

@section('content')
<article class="page">
    <header class="page__head wrap wrap--narrow">
        <p class="eyebrow">{{ $page->text('eyebrow') }}</p>
        <h1 class="page__title">{{ $page->title }}</h1>
        <p class="page__lede">{{ $page->lede }}</p>
    </header>

    <div class="wrap wrap--narrow">
        <section class="bill" aria-label="The company">
            <dl class="bill__list">
                @foreach ($page->content['team'] ?? [] as $row)
                    <div class="bill__row">
                        <dt>{{ $row['role'] }}</dt>
                        <dd>{{ implode(' & ', $row['names']) }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="bill" aria-labelledby="tools-title">
            <h2 id="tools-title" class="bill__title">{{ $page->text('programs_title') }}</h2>
            <dl class="bill__list">
                @foreach ($page->content['programs'] ?? [] as $row)
                    <div class="bill__row">
                        <dt>{{ $row['label'] }}</dt>
                        <dd>{{ $row['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="bill" aria-labelledby="data-title">
            <h2 id="data-title" class="bill__title">{{ $page->text('sources_title') }}</h2>
            <ol class="bill__sources">
                @foreach ($visualisations as $v)
                    <li style="--accent: {{ $v->accent }}">
                        <span class="bill__num">{{ $v->numeral }}</span>
                        <a href="{{ $v->svs_url }}" target="_blank" rel="noopener">
                            {{ $v->title }}
                            <small>svs.gsfc.nasa.gov/{{ $v->svs_id }}</small>
                        </a>
                    </li>
                @endforeach
            </ol>
            @if (filled($page->content['citations_note'] ?? null))
                <p class="bill__note">{{ $page->content['citations_note'] }}</p>
            @endif
        </section>

        @if (filled($page->content['ai_note'] ?? null))
        <section class="bill bill--note" aria-labelledby="ai-title">
            <h2 id="ai-title" class="bill__title">{{ $page->text('ai_title') }}</h2>
            <p>{{ $page->content['ai_note'] }}</p>
        </section>
        @endif
    </div>
</article>
@endsection
