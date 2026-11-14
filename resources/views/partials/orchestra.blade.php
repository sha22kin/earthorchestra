<section class="orchestra" id="orchestra" aria-labelledby="orchestra-title" data-orchestra>
    <div class="wrap">
        <header class="orchestra__head" data-reveal>
            <h2 class="section-title" id="orchestra-title">The Full Orchestra</h2>
            <p class="orchestra__intro">{{ $page->content['orchestra'] }}</p>
        </header>

        <div class="orchestra__stage" data-reveal>
            <div class="orchestra__strings">
                @foreach ($visualisations as $v)
                    <div class="channel" style="--accent: {{ $v->accent }}; --i: {{ $loop->index }}" data-channel="{{ $v->slug }}">
                        <div class="channel__hang" aria-hidden="true">
                            <span class="channel__string"></span>
                            <svg class="channel__drop" viewBox="0 0 40 64" data-drop>
                                <path d="M20 2C20 2 5 28 5 42a15 15 0 0 0 30 0C35 28 20 2 20 2z"/>
                                <path class="channel__shine" d="M13 40c0-6 3-12 5-16"/>
                            </svg>
                        </div>
                        <label class="channel__fader">
                            <span class="sr-only">{{ $v->short_title }} level</span>
                            <input type="range" min="0" max="100" value="70" step="1" data-level>
                        </label>
                        <output class="channel__value" data-value>70</output>
                        <button type="button" class="channel__name" data-mute aria-pressed="false" title="Mute or unmute">
                            <span class="channel__num">{{ $v->numeral }}</span>
                            {{ $v->short_title }}
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="transport">
                <button type="button" class="btn-play btn-play--lg" data-orchestra-play aria-label="Play the full orchestra">
                    <x-icon name="play" class="icon-play" />
                    <x-icon name="pause" class="icon-pause" />
                </button>
                <button type="button" class="btn-icon" data-orchestra-restart aria-label="Back to the start">
                    <x-icon name="restart" />
                </button>

                <div class="transport__progress" aria-hidden="true"><span data-orchestra-progress></span></div>

                <label class="transport__master">
                    <span>Master</span>
                    <input class="range" type="range" min="0" max="100" value="80" data-master aria-label="Master volume">
                </label>
            </div>

            <div class="presets" role="group" aria-label="Starting points">
                <span class="presets__label">Try</span>
                <button type="button" data-preset="tutti">Melody</button>
                <button type="button" data-preset="weather">Weather only</button>
                <button type="button" data-preset="air">Air quality</button>
                <button type="button" data-preset="solo">One at a time</button>
            </div>
        </div>

    </div>
</section>
