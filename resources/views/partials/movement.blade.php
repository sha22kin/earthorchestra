@php
    /** @var \App\Models\Visualisation $v */
    $id = $v->slug;
@endphp
<section class="movement" id="{{ $id }}"
         style="--accent: {{ $v->accent }}"
         aria-labelledby="{{ $id }}-title"
         data-movement='@json($v->toPlayerConfig())'>

    <header class="movement__head" data-reveal>
        <h2 class="movement__title" id="{{ $id }}-title">{{ $v->title }}</h2>
    </header>

    <div class="movement__body">
        <figure class="stage" data-reveal>
            <div class="stage__frame" data-stage tabindex="0"
                 aria-label="{{ $v->short_title }} visualisation. Move the pointer, or use arrow keys, to explore the data and change the volume.">
                <div class="stage__media" data-media>
                    <video class="stage__video" data-video
                           muted playsinline loop preload="none"
                           data-poster="{{ asset($v->poster_path) }}"
                           width="1600" height="800">
                        <source src="{{ asset($v->video_path) }}" type="video/mp4">
                    </video>

                    <span class="stage__probe" data-probe hidden></span>
                    <button type="button" class="stage__lock" data-lock-dot hidden tabindex="-1" aria-label="Locked point - double-click to release"></button>

                    <div class="stage__slider" data-slider hidden>
                        <span class="stage__slider-line"></span>
                        <span class="stage__slider-grip" data-slider-grip role="slider" tabindex="-1"
                              aria-label="Reference line position" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50">
                            <x-icon name="slider" />
                        </span>
                    </div>

                    <output class="stage__readout" data-readout hidden></output>
                </div>

                <button type="button" class="stage__cover" data-cover>
                    <span class="stage__cover-disc"><x-icon name="play" /></span>
                    <span class="stage__cover-label">Play movement {{ $v->numeral }}</span>
                </button>
            </div>

            <figcaption class="controls">
                <button type="button" class="btn-play" data-play aria-label="Play {{ $v->short_title }}">
                    <x-icon name="play" class="icon-play" />
                    <x-icon name="pause" class="icon-pause" />
                </button>

                <div class="segmented" role="radiogroup" aria-label="Interaction">
                    <button type="button" role="radio" data-mode="cursor" aria-checked="false"><x-icon name="cursor" /> Cursor</button>
                    <button type="button" role="radio" data-mode="slider" aria-checked="false"><x-icon name="slider" /> Slider</button>
                </div>

                <div class="controls__volume">
                    <label class="check">
                        <input type="checkbox" data-manual>
                        <span class="check__box" aria-hidden="true"><x-icon name="check" /></span>
                        <span>Manual volume</span>
                    </label>
                    <input class="range" type="range" min="0" max="100" value="60" data-manual-level
                           aria-label="Manual volume" disabled>
                    <output class="controls__pct" data-manual-pct>60%</output>
                </div>

                <label class="check" data-lock-wrap>
                    <input type="checkbox" data-lock>
                    <span class="check__box" aria-hidden="true"><x-icon name="check" /></span>
                    <span>Cursor lock</span>
                </label>
            </figcaption>

            <p class="stage__hint" data-hint></p>
        </figure>

        <div class="movement__legend" data-reveal>
            <x-legend :visualisation="$v" />
        </div>

        <div class="movement__text" data-reveal>
            <p class="movement__summary" id="{{ $id }}-summary">{{ $v->summary }}</p>
            <x-read-aloud target="#{{ $id }}-summary" />

            <dl class="movement__facts">
                <div><dt>Measures</dt><dd>{{ $v->measurement }}</dd></div>
                <div><dt>Snapshot</dt><dd>{{ $v->date_range }}</dd></div>
            </dl>

            <a class="link-arrow" href="{{ route('visualisations.show', $v->slug) }}">
                Want to learn more? <span>Read the full notes</span>
                <x-icon name="arrow-r" />
            </a>
        </div>
    </div>
</section>
