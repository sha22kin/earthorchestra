@extends('admin.layout', ['title' => 'Edit · '.$page->title])

@section('action')
    <button type="submit" form="page-form" class="btn btn--primary"><x-admin.icon name="save" /> <span>Save page</span></button>
@endsection

@section('content')
    <a class="back" href="{{ route('admin.pages.index') }}"><x-admin.icon name="chevron" class="flip" /> All pages</a>

    <form id="page-form" method="POST" action="{{ route('admin.pages.update', $page) }}" class="form-narrow">
        @csrf
        @method('PUT')

        <article class="card">
            <header class="card__head"><h2>Basics</h2></header>
            <div class="card__body fields-2">
                <x-admin.field name="title" label="Page title" :value="$page->title" required />
                <x-admin.field name="lede" label="Lede / tagline" :value="$page->lede" />
            </div>
        </article>

        @yield('fields')

        <div class="form-foot">
            <button type="submit" class="btn btn--primary"><x-admin.icon name="save" /> Save page</button>
        </div>
    </form>
@endsection
