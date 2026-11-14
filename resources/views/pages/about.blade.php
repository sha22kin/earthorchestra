@extends('layouts.app', ['title' => $page->title, 'description' => $page->lede])

@section('body-class', 'page-about')

@section('content')
<article class="page">
    <header class="page__head wrap wrap--narrow">
        <p class="eyebrow">Behind the curtain</p>
        <h1 class="page__title">{{ $page->title }}</h1>
        <p class="page__lede">{{ $page->lede }}</p>
        <x-read-aloud target="#about-body" />
    </header>

    <div class="wrap wrap--narrow">
        <div class="prose prose--lead" id="about-body">
            @foreach ($page->content['paragraphs'] as $paragraph)
                <p>{{ $paragraph }}</p>
                @if ($loop->index === 1)
                    <blockquote class="pullquote" aria-hidden="true">
                        <p>{{ $page->content['pullquote'] }}</p>
                    </blockquote>
                @endif
            @endforeach
        </div>

        <div class="page__end">
            <a class="btn btn--ink" href="{{ route('home') }}#programme">Back to the music <x-icon name="arrow-r" /></a>
        </div>
    </div>
</article>
@endsection
