@extends('admin.layout', ['title' => 'Arrangements'])

@section('content')
    <article class="card">
        <header class="card__head card__head--wrap">
            <h2><x-admin.icon name="sliders" /> Saved arrangements</h2>
            <form method="GET" class="toolbar">
                <label class="search">
                    <x-admin.icon name="search" />
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search title or code" aria-label="Search arrangements">
                </label>
                <select name="sort" aria-label="Sort" onchange="this.form.submit()">
                    <option value="created_at" @selected($sort === 'created_at')>Newest first</option>
                    <option value="listens" @selected($sort === 'listens')>Most listened</option>
                </select>
            </form>
        </header>

        @if ($arrangements->isEmpty())
            <p class="empty">
                @if ($search !== '')
                    Nothing matches “{{ $search }}”.
                @else
                    No arrangements yet. They appear here when visitors press “Save” in the full orchestra.
                @endif
            </p>
        @else
            <div class="table-wrap">
                <table class="table table--roomy">
                    <thead>
                        <tr><th>Title</th><th>Mix</th><th>Listens</th><th>Saved</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($arrangements as $a)
                            <tr>
                                <td>
                                    <span class="strong">{{ $a->displayTitle() }}</span>
                                    <div class="mono muted small">{{ $a->code }}</div>
                                </td>
                                <td>
                                    <span class="mix" aria-label="Levels">
                                        @foreach ($visualisations as $v)
                                            @php $level = $a->levels[$v->slug] ?? 0; @endphp
                                            <span class="mix__bar" style="--c: {{ $v->accent }}; --h: {{ max(6, $level) }}%" title="{{ $v->short_title }}: {{ $level }}" @class(['is-off' => $level === 0])></span>
                                        @endforeach
                                    </span>
                                </td>
                                <td>{{ $a->listens }}</td>
                                <td class="muted nowrap" title="{{ $a->created_at }}">{{ $a->created_at->format('j M Y, H:i') }}</td>
                                <td class="actions">
                                    <a class="icon-btn" href="{{ $a->shareUrl() }}" target="_blank" rel="noopener" title="Open share link"><x-admin.icon name="link" /></a>
                                    <form method="POST" action="{{ route('admin.arrangements.destroy', $a->id) }}" data-confirm="Delete “{{ $a->displayTitle() }}”? Its share link will stop working.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn icon-btn--danger" title="Delete"><x-admin.icon name="trash" /></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card__foot">{{ $arrangements->links() }}</div>
        @endif
    </article>
@endsection
