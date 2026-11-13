/**
 * Turns a pixel colour from a NASA visualisation into a position on its
 * colour legend (0 = left/quiet, 1 = right/loud) and a readable value.
 *
 * The legend stops are drawn evenly spaced, so we build a lookup table by
 * interpolating between neighbouring stops and then find the nearest entry
 * for any sampled colour. "Aliases" are extra colours that appear in the
 * footage and belong to a known value but aren't drawn on the bar; "neutral"
 * colours (land masks, borders) mean "no data here".
 */

const STEPS_BETWEEN = 10;
const MAX_DISTANCE = 95; // redmean distance; beyond this we treat the pixel as noise/overlay

export function hexToRgb(hex) {
    const n = parseInt(hex.replace('#', ''), 16);
    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
}

/** "Redmean" approximation of perceptual colour distance. Cheap and good enough. */
function distance(r1, g1, b1, r2, g2, b2) {
    const rm = (r1 + r2) / 2;
    const dr = r1 - r2;
    const dg = g1 - g2;
    const db = b1 - b2;
    return Math.sqrt((2 + rm / 256) * dr * dr + 4 * dg * dg + (2 + (255 - rm) / 256) * db * db);
}

export class Legend {
    constructor(config) {
        this.config = config;
        this.unit = config.unit;
        this.decimals = config.decimals ?? 0;
        this.stops = config.stops.map((s) => ({ ...s, rgb: hexToRgb(s.color) }));
        this.entries = this.#buildTable();
        this.neutral = (config.neutral || []).map(hexToRgb);
    }

    #buildTable() {
        const stops = this.stops;
        const last = stops.length - 1;
        const table = [];

        for (let i = 0; i < last; i++) {
            const a = stops[i];
            const b = stops[i + 1];
            for (let k = 0; k < STEPS_BETWEEN; k++) {
                const f = k / STEPS_BETWEEN;
                table.push({
                    rgb: a.rgb.map((c, j) => Math.round(c + (b.rgb[j] - c) * f)),
                    t: (i + f) / last,
                    value: a.value + (b.value - a.value) * f,
                    label: f < 0.5 ? a.label : b.label,
                });
            }
        }
        const end = stops[last];
        table.push({ rgb: end.rgb, t: 1, value: end.value, label: end.label });

        for (const alias of this.config.aliases || []) {
            table.push({ rgb: hexToRgb(alias.color), t: this.positionOf(alias), value: alias.value, label: alias.label });
        }

        return table;
    }

    /** Where a value (or labelled stop) sits on the bar, 0–1. */
    positionOf({ value, label }) {
        const stops = this.stops;
        const last = stops.length - 1;

        if (label) {
            const i = stops.findIndex((s) => s.label === label);
            if (i >= 0) return i / last;
        }
        if (value <= stops[0].value) return 0;

        for (let i = 0; i < last; i++) {
            const a = stops[i].value;
            const b = stops[i + 1].value;
            if (value >= a && value <= b && b > a) return (i + (value - a) / (b - a)) / last;
        }
        return 1;
    }

    /** The reading at a given bar position (inverse of positionOf). */
    readingAt(t) {
        const last = this.stops.length - 1;
        const x = Math.min(1, Math.max(0, t)) * last;
        const i = Math.min(last - 1, Math.floor(x));
        const f = x - i;
        const a = this.stops[i];
        const b = this.stops[i + 1];
        return { t, value: a.value + (b.value - a.value) * f, label: f < 0.5 ? a.label : b.label };
    }

    /**
     * @returns {{t: number, value: number, label?: string} | null}
     */
    classify(r, g, b) {
        let best = null;
        let bestD = Infinity;

        for (const e of this.entries) {
            const d = distance(r, g, b, e.rgb[0], e.rgb[1], e.rgb[2]);
            if (d < bestD) {
                bestD = d;
                best = e;
            }
        }

        for (const n of this.neutral) {
            if (distance(r, g, b, n[0], n[1], n[2]) < bestD) return null;
        }

        return bestD <= MAX_DISTANCE ? best : null;
    }

    format(reading) {
        if (!reading) return 'No data here';
        if (reading.label) return reading.label;

        const v = reading.value;
        const first = this.stops[0].value;
        const last = this.stops[this.stops.length - 1].value;
        const n = v.toFixed(this.decimals);

        if (v <= first && reading.t === 0) return `≤ ${n} ${this.unit}`;
        if (v >= last) return `≥ ${n} ${this.unit}`;
        return `≈ ${n} ${this.unit}`;
    }
}

/**
 * How loud a legend position should sound. Slightly curved so the quiet end
 * stays audible but the loud end really opens up.
 */
export function loudness(t) {
    return 0.05 + 0.95 * Math.pow(Math.min(1, Math.max(0, t)), 1.35);
}
