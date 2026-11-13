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
            <x-admin.field name="pullquote" label="Pull quote" :value="$c['pullquote'] ?? ''" hint="Shown large after the second paragraph." />
        </div>
    </article>
@endsection
