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
        <div class="prose" id="about-body">
            @foreach ($page->content['paragraphs'] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>

        @if ($video = $page->youtubeId())
            <figure class="video-embed">
                <iframe src="https://www.youtube-nocookie.com/embed/{{ $video }}?rel=0" title="{{ $page->title }} video"
                        loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </figure>
        @endif

        <div class="page__end">
            <a class="btn btn--ink" href="{{ route('home') }}#programme">Back to the music <x-icon name="arrow-r" /></a>
        </div>
    </div>
</article>
@endsection
