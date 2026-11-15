@extends('admin.layout', ['title' => 'Pages'])

@section('content')
    @php
        $meta = [
            'home' => ['Home page', 'Hero line, overture, how-to-use and orchestra copy', route('home')],
            'about' => ['About us', 'Team story and pull quote', route('about')],
            'credits' => ['Credits', 'Team roles, tools and the AI-usage note', route('credits')],
        ];
    @endphp
    <div class="tiles">
        @foreach ($pages as $page)
            @php [$name, $desc, $url] = $meta[$page->slug] ?? [$page->title, '', url('/')]; @endphp
            <article class="tile">
                <span class="stat__icon tone-blue"><x-admin.icon name="file" /></span>
                <h2>{{ $name }}</h2>
                <p class="muted">{{ $desc }}</p>
                <p class="muted small">Last edited {{ $page->updated_at->diffForHumans() }}</p>
                <div class="tile__actions">
                    <a class="btn btn--primary btn--sm btn--icon" href="{{ route('admin.pages.edit', $page) }}" title="Edit" aria-label="Edit {{ $name }}"><x-admin.icon name="pencil" /></a>
                    <a class="btn btn--ghost btn--sm btn--icon" href="{{ $url }}" target="_blank" rel="noopener" title="View on site" aria-label="View {{ $name }} on site"><x-admin.icon name="eye" /></a>
                </div>
            </article>
        @endforeach
    </div>
@endsection
