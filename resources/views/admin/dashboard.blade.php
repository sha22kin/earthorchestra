@extends('admin.layout', ['title' => 'Dashboard'])

@section('content')
    <section class="stats">
        @foreach ($stats as [$label, $value, $icon, $tone, $href])
            <a class="stat" href="{{ $href }}">
                <span class="stat__icon tone-{{ $tone }}"><x-admin.icon :name="$icon" /></span>
                <span class="stat__body">
                    <strong>{{ is_numeric($value) ? number_format($value) : $value }}</strong>
                    <span>{{ $label }}</span>
                </span>
            </a>
        @endforeach
    </section>

    <section class="grid-2-1">
        <article class="card">
            <header class="card__head">
                <h2><x-admin.icon name="chart" /> Last 7 days</h2>
            </header>
            <div class="card__body">
                <x-admin.line-chart :chart="$chart" />
            </div>
        </article>

        <article class="card">
            <header class="card__head">
                <h2><x-admin.icon name="activity" /> System status</h2>
            </header>
            <ul class="status">
                @foreach ($status as [$label, $icon, $state, $badge])
                    <li>
                        <x-admin.icon :name="$icon" />
                        <span>{{ $label }}</span>
                        <span class="pill pill--{{ $state }}">{{ $badge }}</span>
                    </li>
                @endforeach
                <li>
                    <x-admin.icon name="shield" />
                    <span>Laravel / PHP</span>
                    <strong class="status__value">{{ app()->version() }} / {{ PHP_MAJOR_VERSION }}.{{ PHP_MINOR_VERSION }}</strong>
                </li>
            </ul>
        </article>
    </section>

    <section class="grid-2">
        <article class="card">
            <header class="card__head">
                <h2><x-admin.icon name="list" /> Recent arrangements</h2>
                <a class="link" href="{{ route('admin.arrangements.index') }}">View all <x-admin.icon name="chevron" /></a>
            </header>
            @if ($recent->isEmpty())
                <p class="empty">No arrangements yet. They appear here when visitors save a mix in the orchestra.</p>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Title</th><th>Code</th><th>Listens</th><th>Saved</th></tr></thead>
                        <tbody>
                            @foreach ($recent as $a)
                                <tr>
                                    <td class="strong">{{ $a->excerpt() }}</td>
                                    <td><a class="mono link" href="{{ $a->shareUrl() }}" target="_blank" rel="noopener">{{ $a->code }}</a></td>
                                    <td>{{ $a->listens }}</td>
                                    <td class="muted" title="{{ $a->created_at }}">{{ $a->created_at->diffForHumans(short: true) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>

        <article class="card">
            <header class="card__head">
                <h2><x-admin.icon name="flame" /> Movements</h2>
                <a class="link" href="{{ route('admin.visualisations.index') }}">View all <x-admin.icon name="chevron" /></a>
            </header>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Movement</th><th>Dataset</th><th>Mode</th><th>Sources</th></tr></thead>
                    <tbody>
                        @foreach ($visualisations as $v)
                            <tr>
                                <td>
                                    <a class="row-link" href="{{ route('admin.visualisations.edit', $v) }}">
                                        <span class="swatch" style="--c: {{ $v->accent }}">{{ $v->numeral }}</span>
                                        {{ $v->short_title }}
                                    </a>
                                </td>
                                <td><span class="tag">{{ $v->model }}</span></td>
                                <td class="cap">{{ $v->interaction }}</td>
                                <td>{{ $v->citations->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    @if ($popular->isNotEmpty())
        <article class="card">
            <header class="card__head">
                <h2><x-admin.icon name="headphones" /> Most-listened arrangements</h2>
            </header>
            <ul class="bars">
                @php $peak = max(1, $popular->max('listens')); @endphp
                @foreach ($popular as $a)
                    <li>
                        <span class="bars__name">{{ $a->excerpt() }}</span>
                        <span class="bars__track"><span style="width: {{ round($a->listens / $peak * 100) }}%"></span></span>
                        <span class="bars__value">{{ $a->listens }}</span>
                    </li>
                @endforeach
            </ul>
        </article>
    @endif
@endsection
