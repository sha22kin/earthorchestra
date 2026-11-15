@extends('admin.layout', ['title' => "Movement {$v->numeral} · {$v->short_title}"])

@section('action')
    <button type="submit" form="movement-form" class="btn btn--primary btn--icon" title="Save changes" aria-label="Save changes"><x-admin.icon name="save" /></button>
@endsection

@section('content')
    <a class="back" href="{{ route('admin.visualisations.index') }}"><x-admin.icon name="chevron" class="flip" /> All movements</a>

    <form id="movement-form" method="POST" action="{{ route('admin.visualisations.update', $v) }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @method('PUT')

        <div class="form-main">
            <article class="card">
                <header class="card__head"><h2>Details</h2></header>
                <div class="card__body fields-2">
                    <x-admin.field name="title" label="Title" :value="$v->title" required class="span-2" />
                    <x-admin.field name="short_title" label="Short title" :value="$v->short_title" hint="Used in the orchestra and the programme list." required />
                    <x-admin.field name="measurement" label="What it measures" :value="$v->measurement" required />
                    <x-admin.field name="summary" label="Introduction (home page)" type="textarea" rows="3" :value="$v->summary" required class="span-2" data-autosize />
                </div>
            </article>

            <article class="card">
                <header class="card__head">
                    <h2>Learn-more notes</h2>
                    <span class="muted small">Blank line between paragraphs · start a line with <code>##</code> for a heading</span>
                </header>
                <div class="card__body">
                    <x-admin.field name="body" label="Article" type="textarea" rows="16" :value="$bodyText" required data-autosize spellcheck="true" />
                </div>
            </article>

            <article class="card">
                <header class="card__head">
                    <h2>Bibliography</h2>
                    <button type="button" class="btn btn--ghost btn--sm btn--icon" data-repeater-add="citations" title="Add source" aria-label="Add source"><x-admin.icon name="plus" /></button>
                </header>
                <div class="card__body">
                    <ol class="repeater" data-repeater="citations">
                        @foreach (old('citations', $v->citations->map(fn ($c) => ['reference' => $c->reference, 'url' => $c->url])->all()) as $i => $c)
                            @include('admin.visualisations.citation-row', ['i' => $i, 'c' => $c])
                        @endforeach
                    </ol>
                    <template data-repeater-template="citations">
                        @include('admin.visualisations.citation-row', ['i' => '__i__', 'c' => ['reference' => '', 'url' => '']])
                    </template>
                </div>
            </article>

            <article class="card">
                <header class="card__head">
                    <h2>Colour legend</h2>
                    <span class="muted small">Maps map colours to values and loudness</span>
                </header>
                <div class="card__body">
                    <div class="legend-preview" data-legend-preview>
                        <div class="legend-preview__bar"></div>
                        <div class="legend-preview__meta"><span data-legend-label></span><span data-legend-range></span></div>
                    </div>
                    <x-admin.field name="legend" label="Legend JSON" type="textarea" rows="14"
                                   :value="json_encode($v->legend, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)"
                                   hint='"stops" run low → high (quiet → loud). "aliases" are extra colours seen in the video; "neutral" colours mean no data.'
                                   required data-legend-input class="mono-field" />
                </div>
            </article>
        </div>

        <aside class="form-side">
            <article class="card">
                <header class="card__head"><h2>Settings</h2></header>
                <div class="card__body stack">
                    <x-admin.field name="interaction" label="Default interaction" type="select" :value="$v->interaction" :options="['cursor' => 'Cursor', 'slider' => 'Slider']" />
                    <x-admin.field name="model" label="Dataset" type="select" :value="$v->model" :options="['GEOS-FP' => 'GEOS-FP', 'GEOS-CF' => 'GEOS-CF']" />
                    <x-admin.field name="accent" label="Accent colour" type="color" :value="$v->accent" required />
                    <x-admin.field name="svs_id" label="NASA SVS id" type="number" :value="$v->svs_id" hint="svs.gsfc.nasa.gov/{id}" required />
                    <div class="fields-2 tight">
                        <x-admin.field name="data_starts_on" label="Snapshot from" type="date" :value="$v->data_starts_on->toDateString()" required />
                        <x-admin.field name="data_ends_on" label="to" type="date" :value="$v->data_ends_on->toDateString()" required />
                    </div>
                </div>
            </article>

            <article class="card">
                <header class="card__head"><h2>Media</h2></header>
                <div class="card__body stack">
                    <video class="poster" src="{{ asset($v->video_path) }}" poster="{{ asset($v->poster_path) }}" controls muted preload="metadata" data-video-preview></video>
                    <img class="poster" src="{{ asset($v->poster_path) }}" alt="Current poster" data-poster-preview>
                    <label class="upload">
                        <x-admin.icon name="upload" />
                        <span><strong>Poster image</strong><small>{{ basename($v->poster_path) }}</small></span>
                        <input type="file" name="poster" accept="image/webp,image/jpeg,image/png" data-upload>
                    </label>
                    <label class="upload">
                        <x-admin.icon name="film" />
                        <span><strong>Video (MP4)</strong><small>{{ basename($v->video_path) }}</small></span>
                        <input type="file" name="video" accept="video/mp4" data-upload>
                    </label>
                    <label class="upload">
                        <x-admin.icon name="headphones" />
                        <span><strong>Audio</strong><small>{{ basename($v->audio_path) }}</small></span>
                        <input type="file" name="audio" accept="audio/*" data-upload>
                    </label>
                    <audio controls preload="none" src="{{ asset($v->audio_path) }}"></audio>
                    <p class="field__hint">Uploads replace the file used on the site. Large videos may exceed your PHP <code>upload_max_filesize</code>.</p>
                </div>
            </article>

            <div class="side-actions">
                <button type="submit" class="btn btn--primary btn--block"><x-admin.icon name="save" /> Save changes</button>
                <a class="btn btn--ghost btn--block" href="{{ route('visualisations.show', $v->slug) }}" target="_blank" rel="noopener"><x-admin.icon name="eye" /> Preview notes</a>
            </div>
        </aside>
    </form>
@endsection
