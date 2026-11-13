@extends('layouts.app', ['title' => 'Lost in the score'])

@section('body-class', 'page-error')

@section('content')
<section class="lost wrap wrap--narrow">
    <img src="{{ asset('media/cloud-3.webp') }}" alt="" width="900" height="314">
    <p class="eyebrow">404</p>
    <h1 class="page__title">This note isn't in the score.</h1>
    <p class="page__lede">The page you were after has drifted off somewhere. Let's get you back to the music.</p>
    <a class="btn btn--ink" href="{{ route('home') }}">Return to the overture <x-icon name="arrow-r" /></a>
</section>
@endsection
