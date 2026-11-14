@extends('admin.pages._shell')

@php $c = $page->content; $join = fn ($a) => implode("\n\n", $a ?? []); @endphp

@section('fields')
    <article class="card">
        <header class="card__head"><h2>Home page copy</h2><span class="muted small">Leave a blank line between paragraphs</span></header>
        <div class="card__body stack">
            <x-admin.field name="intro" label="Overture - About Earth Orchestra" type="textarea" rows="5" :value="$join($c['intro'])" required data-autosize />
            <x-admin.field name="how" label="How to use" type="textarea" rows="5" :value="$join($c['how'])" required data-autosize />
            <x-admin.field name="cursor" label="Cursor mode explanation" type="textarea" rows="5" :value="$join($c['cursor'])" required data-autosize />
            <x-admin.field name="slider" label="Slider mode explanation" type="textarea" rows="3" :value="$join($c['slider'])" required data-autosize />
            <x-admin.field name="orchestra" label="Full orchestra introduction" type="textarea" rows="2" :value="$c['orchestra'] ?? ''" required />
        </div>
    </article>
@endsection
