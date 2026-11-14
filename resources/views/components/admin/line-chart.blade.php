@props(['chart'])
@php
    $W = 1000; $H = 300;
    $padL = 48; $padR = 16; $padT = 16; $padB = 34;
    $innerW = $W - $padL - $padR;
    $innerH = $H - $padT - $padB;

    $max = max(1, ...collect($chart['series'])->flatMap(fn ($s) => $s['values'])->all());
    // Round the axis up to a friendly number with 4 even steps.
    $step = max(1, (int) ceil($max / 4));
    $mag = 10 ** max(0, (int) floor(log10($step)));
    $step = (int) (ceil($step / $mag) * $mag);
    $top = $step * 4;

    $n = count($chart['labels']);
    $x = fn ($i) => $padL + ($n > 1 ? $i * $innerW / ($n - 1) : $innerW / 2);
    $y = fn ($v) => $padT + $innerH - ($v / $top) * $innerH;

    // Smooth path through points (Catmull-Rom → cubic Bézier), clamped so it never dips below zero.
    $path = function (array $values) use ($x, $y, $padT, $innerH) {
        $pts = array_map(fn ($v, $i) => [$x($i), $y($v)], $values, array_keys($values));
        $d = sprintf('M%.1f %.1f', ...$pts[0]);
        $floor = $padT + $innerH;
        for ($i = 0; $i < count($pts) - 1; $i++) {
            $p0 = $pts[max(0, $i - 1)]; $p1 = $pts[$i]; $p2 = $pts[$i + 1]; $p3 = $pts[min(count($pts) - 1, $i + 2)];
            $c1 = [$p1[0] + ($p2[0] - $p0[0]) / 6, min($floor, $p1[1] + ($p2[1] - $p0[1]) / 6)];
            $c2 = [$p2[0] - ($p3[0] - $p1[0]) / 6, min($floor, $p2[1] - ($p3[1] - $p1[1]) / 6)];
            $d .= sprintf(' C%.1f %.1f %.1f %.1f %.1f %.1f', $c1[0], $c1[1], $c2[0], $c2[1], $p2[0], $p2[1]);
        }
        return $d;
    };

    $payload = collect(range(0, $n - 1))->map(fn ($i) => [
        'date' => $chart['dates'][$i],
        'values' => collect($chart['series'])->map(fn ($s) => ['name' => $s['name'], 'color' => $s['color'], 'value' => $s['values'][$i]])->all(),
    ]);
@endphp
<div class="chart" data-chart='@json($payload)'>
    <ul class="chart__legend">
        @foreach ($chart['series'] as $s)
            <li><span style="--c: {{ $s['color'] }}"></span>{{ $s['name'] }}</li>
        @endforeach
    </ul>

    <svg viewBox="0 0 {{ $W }} {{ $H }}" preserveAspectRatio="none" role="img"
         aria-label="Line chart: {{ collect($chart['series'])->map(fn ($s) => $s['name'].' '.implode(', ', $s['values']))->implode('; ') }}">
        <defs>
            <linearGradient id="chart-fill" x1="0" x2="0" y1="0" y2="1">
                <stop offset="0" stop-color="{{ $chart['series'][0]['color'] }}" stop-opacity=".16"/>
                <stop offset="1" stop-color="{{ $chart['series'][0]['color'] }}" stop-opacity=".02"/>
            </linearGradient>
        </defs>

        @for ($g = 0; $g <= 4; $g++)
            @php $gy = $y($step * $g); @endphp
            <line class="chart__grid" x1="{{ $padL }}" x2="{{ $W - $padR }}" y1="{{ $gy }}" y2="{{ $gy }}"/>
        @endfor

        @php $first = $path($chart['series'][0]['values']); @endphp
        <path d="{{ $first }} L{{ $x($n - 1) }} {{ $padT + $innerH }} L{{ $x(0) }} {{ $padT + $innerH }} Z" fill="url(#chart-fill)"/>

        @foreach ($chart['series'] as $s)
            <path class="chart__line" d="{{ $path($s['values']) }}" stroke="{{ $s['color'] }}"/>
        @endforeach

        <line class="chart__cursor" data-chart-cursor x1="0" x2="0" y1="{{ $padT }}" y2="{{ $padT + $innerH }}" visibility="hidden"/>
        @foreach ($chart['series'] as $si => $s)
            @foreach ($s['values'] as $i => $v)
                <circle class="chart__dot" data-chart-dot="{{ $i }}" cx="{{ $x($i) }}" cy="{{ $y($v) }}" r="4.5" stroke="{{ $s['color'] }}"/>
            @endforeach
        @endforeach

        @foreach ($chart['labels'] as $i => $label)
            <rect class="chart__hit" data-chart-hit="{{ $i }}" data-x="{{ $x($i) }}"
                  x="{{ $x($i) - $innerW / max(1, $n - 1) / 2 }}" y="{{ $padT }}" width="{{ $innerW / max(1, $n - 1) }}" height="{{ $innerH }}"/>
        @endforeach
    </svg>

    <div class="chart__labels" aria-hidden="true">
        @for ($g = 0; $g <= 4; $g++)
            <span class="chart__y" style="top: {{ round($y($step * $g) / $H * 100, 3) }}%; right: {{ round(($W - $padL + 12) / $W * 100, 3) }}%">{{ $step * $g }}</span>
        @endfor
        @foreach ($chart['labels'] as $i => $label)
            <span class="chart__x" style="left: {{ round($x($i) / $W * 100, 3) }}%">{{ $label }}</span>
        @endforeach
    </div>
    <div class="chart__tip" data-chart-tip hidden></div>
</div>
