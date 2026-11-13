import { Legend, loudness } from '../lib/legend.js';
import { createVoice } from '../lib/audio.js';
import { stage } from '../lib/stage.js';

const SAMPLE_W = 240;
const SAMPLE_H = 120;
const SAMPLE_EVERY_MS = 80;
const DOUBLE_TAP_MS = 320;

// Burned-in overlays on the NASA footage (logo top-left, timestamp bottom-left).
// Pixels here aren't data, so they're ignored. Normalised x0, y0, x1, y1.
const OVERLAYS = [
    [0, 0, 0.175, 0.075],
    [0, 0.9, 0.25, 1],
];

const HINTS = {
    cursor: {
        mouse: 'Move over the map to hear the data change. Double-click to lock a sound in place.',
        touch: 'Drag across the map to hear the data change. Double-tap to lock a sound in place.',
    },
    slider: {
        mouse: 'Drag the line anywhere on the map. As the video plays, the volume follows the data under it.',
        touch: 'Drag the line anywhere on the map. As the video plays, the volume follows the data under it.',
    },
};

const isCoarse = () => window.matchMedia('(pointer: coarse)').matches;

export class Movement {
    constructor(section) {
        this.el = section;
        this.cfg = JSON.parse(section.dataset.movement);
        this.legend = new Legend(this.cfg.legend);
        this.title = this.cfg.title;
        this.anchor = `#${this.cfg.slug}`;
        this.visible = false;

        const $ = (sel) => section.querySelector(sel);
        this.ui = {
            frame: $('[data-stage]'),
            media: $('[data-media]'),
            video: $('[data-video]'),
            probe: $('[data-probe]'),
            lockDot: $('[data-lock-dot]'),
            slider: $('[data-slider]'),
            grip: $('[data-slider-grip]'),
            readout: $('[data-readout]'),
            cover: $('[data-cover]'),
            play: $('[data-play]'),
            modes: [...section.querySelectorAll('[data-mode]')],
            modeTag: $('[data-mode-tag]'),
            manual: $('[data-manual]'),
            manualLevel: $('[data-manual-level]'),
            manualPct: $('[data-manual-pct]'),
            lock: $('[data-lock]'),
            lockWrap: $('[data-lock-wrap]'),
            hint: $('[data-hint]'),
            marker: $('[data-legend-marker]'),
            lockMarker: $('[data-legend-lock]'),
        };

        this.state = {
            mode: this.cfg.interaction,
            playing: false,
            dataGain: 0.5,
            manual: false,
            manualLevel: 0.6,
            locked: null,
            probe: null,
            sliderX: 0.5,
            dragging: false,
            reading: null,
        };

        this.canvas = document.createElement('canvas');
        this.canvas.width = SAMPLE_W;
        this.canvas.height = SAMPLE_H;
        this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
        this.pixels = null;
        this.sampledAt = 0;
        this.lastTap = null;

        // Posters load only when the section gets close, so they don't
        // compete with the page's first paint and scripts.
        this.poster = new Image();
        this.poster.decoding = 'async';
        this.posterUrl = this.ui.video.dataset.poster;

        this.bind();
        this.setMode(this.state.mode);
        this.observe();
    }

    /* ───────────── wiring ───────────── */

    bind() {
        const { ui } = this;

        ui.cover.addEventListener('click', () => this.play());
        ui.play.addEventListener('click', () => (this.state.playing ? this.pause() : this.play()));
        ui.modes.forEach((btn) => btn.addEventListener('click', () => this.setMode(btn.dataset.mode)));

        ui.manual.addEventListener('change', () => {
            this.state.manual = ui.manual.checked;
            ui.manualLevel.disabled = !ui.manual.checked;
            this.el.classList.toggle('is-manual', this.state.manual);
            this.applyGain();
        });
        ui.manualLevel.addEventListener('input', () => {
            this.state.manualLevel = ui.manualLevel.value / 100;
            ui.manualPct.textContent = `${ui.manualLevel.value}%`;
            this.applyGain();
        });

        ui.lock.addEventListener('change', () => {
            if (ui.lock.checked) {
                const p = this.state.probe || { x: 0.5, y: 0.5 };
                this.lockAt(p.x, p.y);
            } else {
                this.unlock();
            }
        });

        const f = ui.frame;
        f.addEventListener('pointerdown', (e) => this.onPointerDown(e));
        f.addEventListener('pointermove', (e) => this.onPointerMove(e));
        f.addEventListener('pointerup', (e) => this.onPointerUp(e));
        f.addEventListener('pointercancel', () => (this.state.dragging = false));
        f.addEventListener('pointerleave', (e) => this.onPointerLeave(e));
        f.addEventListener('dblclick', (e) => {
            e.preventDefault();
            this.toggleLockAtEvent(e);
        });
        f.addEventListener('keydown', (e) => this.onKey(e));
        ui.lockDot.addEventListener('click', (e) => e.stopPropagation());
    }

    loadPoster() {
        if (this.poster.src || !this.posterUrl) return;
        this.ui.video.poster = this.posterUrl;
        this.poster.src = this.posterUrl;
    }

    observe() {
        if (!('IntersectionObserver' in window)) {
            this.loadPoster();
            return;
        }
        const near = new IntersectionObserver(([entry]) => {
            if (!entry.isIntersecting) return;
            this.loadPoster();
            near.disconnect();
        }, { rootMargin: '900px 0px' });
        near.observe(this.el);

        new IntersectionObserver(([entry]) => {
            this.visible = entry.isIntersecting;
            if (this.state.playing) stage.emit();
        }, { threshold: 0.15 }).observe(this.el);
    }

    /* ───────────── transport ───────────── */

    async play() {
        stage.claim(this);
        this.loadPoster();

        if (!this.voice) {
            this.voice = createVoice(this.cfg.audio);
        }

        const { video } = this.ui;
        if (video.preload !== 'auto') {
            video.preload = 'auto';
            video.load();
        }

        try {
            await this.voice.el.play();
        } catch (err) {
            this.ui.hint.textContent = 'Your browser blocked playback. Tap play again to start the sound.';
            stage.release(this);
            return;
        }
        video.play().catch(() => {});

        this.state.playing = true;
        this.el.classList.add('is-playing', 'has-played');
        this.ui.play.setAttribute('aria-label', `Pause ${this.title}`);
        this.applyGain(0.02);
        this.loop();
        stage.emit();
    }

    pause() {
        if (!this.state.playing) return;
        this.state.playing = false;
        this.voice?.el.pause();
        this.ui.video.pause();
        this.el.classList.remove('is-playing');
        this.ui.play.setAttribute('aria-label', `Play ${this.title}`);
        cancelAnimationFrame(this.raf);
        stage.release(this);
    }

    loop = (now = performance.now()) => {
        if (!this.state.playing) return;
        if (now - this.sampledAt > SAMPLE_EVERY_MS) this.sample();
        this.syncVideo();
        this.raf = requestAnimationFrame(this.loop);
    };

    /** Audio is the clock; the video follows it. */
    syncVideo(force = false) {
        const { video } = this.ui;
        const a = this.voice?.el;
        if (!a || !video.duration || video.seeking) return;
        const target = a.currentTime % video.duration;
        if (force || Math.abs(video.currentTime - target) > 0.25) video.currentTime = target;
    }


    applyGain(smoothing) {
        if (!this.voice) return;
        const s = this.state;
        let g = s.dataGain;
        if (s.mode === 'cursor' && s.locked) g = s.locked.gain;
        if (s.manual) g = s.manualLevel;
        this.voice.setGain(g, smoothing);
        this.currentGain = g;
    }

    /* ───────────── modes ───────────── */

    setMode(mode) {
        const s = this.state;
        s.mode = mode;
        this.ui.modes.forEach((b) => b.setAttribute('aria-checked', String(b.dataset.mode === mode)));
        this.ui.modeTag.textContent = `(${mode})`;
        this.el.dataset.mode = mode;

        const slider = mode === 'slider';
        this.ui.slider.hidden = !slider;
        this.ui.probe.hidden = true;
        this.ui.lock.disabled = slider;
        this.ui.lockWrap.classList.toggle('is-disabled', slider);
        if (slider) this.unlock();

        this.ui.hint.textContent = HINTS[mode][isCoarse() ? 'touch' : 'mouse'];
        this.placeSlider();
        this.sample();
    }

    /* ───────────── sampling ───────────── */

    refreshPixels() {
        const now = performance.now();
        if (this.pixels && now - this.sampledAt < SAMPLE_EVERY_MS / 2) return true;

        const v = this.ui.video;
        const source = v.readyState >= 2 ? v : this.poster.complete && this.poster.naturalWidth ? this.poster : null;
        if (!source) return false;

        try {
            this.ctx.drawImage(source, 0, 0, SAMPLE_W, SAMPLE_H);
            this.pixels = this.ctx.getImageData(0, 0, SAMPLE_W, SAMPLE_H).data;
            this.sampledAt = now;
            return true;
        } catch {
            return false; // tainted canvas or not ready yet
        }
    }

    readAt(nx, ny) {
        if (OVERLAYS.some(([x0, y0, x1, y1]) => nx >= x0 && nx <= x1 && ny >= y0 && ny <= y1)) return null;

        const px = Math.min(SAMPLE_W - 2, Math.max(1, Math.round(nx * SAMPLE_W)));
        const py = Math.min(SAMPLE_H - 2, Math.max(1, Math.round(ny * SAMPLE_H)));
        let r = 0, g = 0, b = 0;
        for (let dy = -1; dy <= 1; dy++) {
            for (let dx = -1; dx <= 1; dx++) {
                const i = ((py + dy) * SAMPLE_W + (px + dx)) * 4;
                r += this.pixels[i];
                g += this.pixels[i + 1];
                b += this.pixels[i + 2];
            }
        }
        return this.legend.classify(r / 9, g / 9, b / 9);
    }

    /** Average legend position down a vertical line. */
    readColumn(nx) {
        let sum = 0;
        let n = 0;
        for (let y = 0.04; y < 0.97; y += 0.025) {
            const r = this.readAt(nx, y);
            if (r) {
                sum += r.t;
                n++;
            }
        }
        if (!n) return null;
        return this.legend.readingAt(sum / n);
    }

    sample() {
        const s = this.state;
        if (!this.refreshPixels()) return;

        let reading = null;
        if (s.mode === 'slider') {
            reading = this.readColumn(s.sliderX);
        } else if (s.probe) {
            reading = this.readAt(s.probe.x, s.probe.y);
        }

        if (reading) {
            s.reading = reading;
            s.dataGain = loudness(reading.t);
        }
        this.renderReading(reading);
        this.applyGain();
    }

    renderReading(reading) {
        const { ui, state: s } = this;
        const showing = s.mode === 'slider' || !!s.probe;

        if (reading) {
            ui.marker.hidden = false;
            ui.marker.style.setProperty('--at', reading.t);
        }

        ui.readout.hidden = !showing;
        if (!showing) return;

        const gain = s.manual ? s.manualLevel : s.mode === 'cursor' && s.locked ? s.locked.gain : s.dataGain;
        const prefix = s.mode === 'slider' ? 'Along the line ' : '';
        ui.readout.innerHTML = `<b>${prefix}${this.legend.format(reading)}</b><span>${Math.round(gain * 100)}% volume${s.manual ? ' · manual' : s.locked && s.mode === 'cursor' ? ' · locked' : ''}</span>`;

        this.placeReadout(s.mode === 'slider' ? { x: s.sliderX, y: 0 } : s.probe);
    }

    /**
     * Put the readout bubble beside the pointer (or the slider line) on
     * whichever side has room, so it never spills outside the map.
     */
    placeReadout(anchor) {
        const el = this.ui.readout;
        const W = this.ui.media.clientWidth;
        const H = this.ui.media.clientHeight;
        const w = el.offsetWidth;
        const h = el.offsetHeight;
        const gap = this.state.mode === 'slider' ? 28 : 18;
        const ax = anchor.x * W;
        const ay = anchor.y * H;

        let left = ax + gap;
        if (left + w > W - 6) left = ax - gap - w;
        left = Math.max(6, Math.min(W - w - 6, left));

        let top = this.state.mode === 'slider' ? 8 : ay - h - 10;
        if (top < 6) top = ay + 22;
        top = Math.max(6, Math.min(H - h - 6, top));

        el.style.transform = `translate(${Math.round(left)}px, ${Math.round(top)}px)`;
    }

    /* ───────────── pointer & keys ───────────── */

    pos(e) {
        const r = this.ui.media.getBoundingClientRect();
        return {
            x: Math.min(1, Math.max(0, (e.clientX - r.left) / r.width)),
            y: Math.min(1, Math.max(0, (e.clientY - r.top) / r.height)),
        };
    }

    onPointerDown(e) {
        if (e.target.closest('[data-cover]')) return;
        const p = this.pos(e);

        if (this.state.mode === 'slider') {
            this.state.dragging = true;
            this.ui.frame.setPointerCapture(e.pointerId);
            this.moveSlider(p.x);
            return;
        }
        this.setProbe(p);
    }

    onPointerMove(e) {
        if (e.target.closest('[data-cover]')) return;
        const p = this.pos(e);
        if (this.state.mode === 'slider') {
            if (this.state.dragging) this.moveSlider(p.x);
            return;
        }
        if (e.pointerType === 'mouse' || e.pressure > 0 || e.buttons) this.setProbe(p);
    }

    onPointerUp(e) {
        this.state.dragging = false;
        if (e.pointerType !== 'touch' || this.state.mode !== 'cursor') return;

        // Manual double-tap detection; dblclick isn't reliable on touch screens.
        const now = performance.now();
        const last = this.lastTap;
        if (last && now - last.t < DOUBLE_TAP_MS && Math.hypot(e.clientX - last.x, e.clientY - last.y) < 30) {
            this.toggleLockAtEvent(e);
            this.lastTap = null;
        } else {
            this.lastTap = { t: now, x: e.clientX, y: e.clientY };
        }
    }

    onPointerLeave(e) {
        if (e.pointerType !== 'mouse' || this.state.mode !== 'cursor') return;
        this.state.probe = null;
        this.ui.probe.hidden = true;
        this.ui.readout.hidden = true;
    }

    setProbe(p) {
        this.state.probe = p;
        const { probe } = this.ui;
        probe.hidden = false;
        probe.style.setProperty('--x', p.x);
        probe.style.setProperty('--y', p.y);
        if (performance.now() - this.sampledAt > SAMPLE_EVERY_MS / 2 || !this.state.playing) this.sample();
    }

    moveSlider(x) {
        this.state.sliderX = x;
        this.placeSlider();
        this.sample();
    }

    placeSlider() {
        const x = this.state.sliderX;
        this.ui.slider.style.setProperty('--x', x);
        this.ui.grip.setAttribute('aria-valuenow', Math.round(x * 100));
    }

    onKey(e) {
        const s = this.state;
        const step = e.shiftKey ? 0.1 : 0.025;
        const d = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, -step], ArrowDown: [0, step] }[e.key];

        if (d) {
            e.preventDefault();
            if (s.mode === 'slider') {
                this.moveSlider(Math.min(1, Math.max(0, s.sliderX + d[0] + d[1])));
            } else {
                const p = s.probe || { x: 0.5, y: 0.5 };
                this.setProbe({ x: Math.min(1, Math.max(0, p.x + d[0])), y: Math.min(1, Math.max(0, p.y + d[1])) });
            }
        } else if ((e.key === 'Enter' || e.key === ' ') && s.mode === 'cursor' && e.target === this.ui.frame) {
            e.preventDefault();
            const p = s.probe || { x: 0.5, y: 0.5 };
            s.locked ? this.unlock() : this.lockAt(p.x, p.y);
        } else if (e.key.toLowerCase() === 'k' && e.target === this.ui.frame) {
            s.playing ? this.pause() : this.play();
        }
    }

    /* ───────────── locking ───────────── */

    toggleLockAtEvent(e) {
        if (this.state.mode !== 'cursor' || e.target.closest('[data-cover]')) return;
        const p = this.pos(e);
        const locked = this.state.locked;

        if (locked) {
            const r = this.ui.media.getBoundingClientRect();
            const near = Math.hypot((p.x - locked.x) * r.width, (p.y - locked.y) * r.height) < 24;
            if (near || e.target === this.ui.lockDot) {
                this.unlock();
                return;
            }
        }
        this.lockAt(p.x, p.y);
    }

    lockAt(x, y) {
        this.refreshPixels();
        const reading = (this.pixels && this.readAt(x, y)) || this.state.reading || this.legend.readingAt(0.5);
        this.state.locked = { x, y, reading, gain: loudness(reading.t) };

        const { lockDot, lock, lockMarker } = this.ui;
        lockDot.hidden = false;
        lockDot.style.setProperty('--x', x);
        lockDot.style.setProperty('--y', y);
        lockDot.classList.remove('is-new');
        void lockDot.offsetWidth;
        lockDot.classList.add('is-new');
        lock.checked = true;
        lockMarker.hidden = false;
        lockMarker.style.setProperty('--at', reading.t);
        this.el.classList.add('is-locked');

        this.applyGain();
        this.renderReading(reading);
    }

    unlock() {
        if (!this.state.locked) {
            this.ui.lock.checked = false;
            return;
        }
        this.state.locked = null;
        this.ui.lockDot.hidden = true;
        this.ui.lock.checked = false;
        this.ui.lockMarker.hidden = true;
        this.el.classList.remove('is-locked');
        this.applyGain();
    }
}

export function initMovements() {
    return [...document.querySelectorAll('[data-movement]')].map((el) => new Movement(el));
}
