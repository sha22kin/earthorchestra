@extends('admin.pages._shell')

@php
    $c = $page->content;
    $story = implode(PHP_EOL.PHP_EOL, $c['paragraphs'] ?? []);
@endphp

@section('fields')
    <article class="card">
        <header class="card__head"><h2>About us</h2><span class="muted small">Leave a blank line between paragraphs</span></header>
        <div class="card__body stack">
            <x-admin.field name="paragraphs" label="Story" type="textarea" rows="14" :value="$story" required data-autosize spellcheck="true" />
        </div>
    </article>

    <article class="card">
        <header class="card__head"><h2>YouTube video</h2></header>
        <div class="card__body stack">
            <x-admin.field name="youtube_url" label="YouTube link" type="url" :value="$c['youtube_url'] ?? ''" placeholder="https://www.youtube.com/watch?v=..." hint="Shown after the story. Clear the box to remove the video." />
            @if ($id = $page->youtubeId())
                <div class="video-preview"><iframe src="https://www.youtube-nocookie.com/embed/{{ $id }}" title="Video preview" loading="lazy" allowfullscreen></iframe></div>
            @endif
        </div>
    </article>
@endsection
