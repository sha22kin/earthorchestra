@props(['visualisation'])
@php
    $legend = $visualisation->legend;
    $stops = $legend['stops'];
    $n = count($stops);
    $gradient = collect($stops)
        ->map(fn ($s, $i) => $s['color'].' '.round($i / max(1, $n - 1) * 100, 2).'%')
        ->implode(', ');
@endphp
<figure class="legend" data-legend>
    <figcaption class="legend__caption">
        <span>{{ $legend['label'] }}</span>
        <span class="legend__unit">{{ $legend['unit'] }}</span>
    </figcaption>

    <div class="legend__bar" style="--gradient: linear-gradient(90deg, {{ $gradient }})">
        <span class="legend__marker" data-legend-marker hidden></span>
        <span class="legend__marker legend__marker--lock" data-legend-lock hidden></span>
    </div>

    <ol class="legend__ticks" aria-hidden="true">
        @foreach ($visualisation->legendTicks() as $tick)
            <li style="--at: {{ round($tick['at'], 4) }}">{{ $tick['label'] }}</li>
        @endforeach
    </ol>

    <div class="legend__volume" aria-hidden="true">
        <svg viewBox="0 0 200 14" preserveAspectRatio="none"><path d="M0 12.5 L200 1.5 L200 12.5 Z"/></svg>
        <span>quiet</span><span>loud</span>
    </div>
</figure>
