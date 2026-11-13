@extends('admin.layout', ['title' => 'Movements'])

@section('content')
    <article class="card">
        <header class="card__head">
            <h2><x-admin.icon name="music" /> All movements</h2>
            <span class="muted small">Order matches the home page</span>
        </header>
        <div class="table-wrap">
            <table class="table table--roomy">
                <thead>
                    <tr><th>#</th><th>Movement</th><th>Dataset</th><th>Mode</th><th>Snapshot</th><th>Sources</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($visualisations as $v)
                        <tr>
                            <td><span class="swatch" style="--c: {{ $v->accent }}">{{ $v->numeral }}</span></td>
                            <td>
                                <a class="row-link strong" href="{{ route('admin.visualisations.edit', $v) }}">{{ $v->title }}</a>
                                <div class="muted small">{{ $v->measurement }}</div>
                            </td>
                            <td><span class="tag">{{ $v->model }}</span></td>
                            <td class="cap">{{ $v->interaction }}</td>
                            <td class="muted nowrap">{{ $v->date_range }}</td>
                            <td>{{ $v->citations_count }}</td>
                            <td class="actions">
                                <a class="icon-btn" href="{{ route('home') }}#{{ $v->slug }}" target="_blank" rel="noopener" title="View on site"><x-admin.icon name="eye" /></a>
                                <a class="btn btn--ghost btn--sm" href="{{ route('admin.visualisations.edit', $v) }}"><x-admin.icon name="pencil" /> Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
@endsection
