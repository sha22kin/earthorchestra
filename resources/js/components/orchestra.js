import { audioContext, createVoice, levelOf } from '../lib/audio.js';
import { stage, prefersReducedMotion } from '../lib/stage.js';

const PRESETS = {
    tutti: () => ({}),
    weather: (slugs) => Object.fromEntries(slugs.map((s, i) => [s, i < 4 ? 75 : 0])),
    air: (slugs) => Object.fromEntries(slugs.map((s, i) => [s, i >= 4 ? 85 : i === 1 ? 35 : 0])),
};

const perceptual = (level) => Math.pow(level / 100, 1.6);

export class Orchestra {
    constructor(root, config) {
        this.root = root;
        this.config = config;
        this.title = 'The full orchestra';
        this.anchor = '#orchestra';
        this.visible = false;
        this.playing = false;
        this.soloIndex = -1;

        this.channels = [...root.querySelectorAll('[data-channel]')].map((el, i) => {
            const slug = el.dataset.channel;
            const track = config.tracks.find((t) => t.slug === slug);
            return {
                el,
                slug,
                track,
                index: i,
                level: 70,
                muted: false,
                smooth: 0,
                fader: el.querySelector('[data-level]'),
                value: el.querySelector('[data-value]'),
                mute: el.querySelector('[data-mute]'),
                drop: el.querySelector('[data-drop]'),
            };
        });

        this.ui = {
            play: root.querySelector('[data-orchestra-play]'),
            restart: root.querySelector('[data-orchestra-restart]'),
            progress: root.querySelector('[data-orchestra-progress]'),
            master: root.querySelector('[data-master]'),
            presets: [...root.querySelectorAll('[data-preset]')],
            form: root.querySelector('[data-save-form]'),
            result: root.querySelector('[data-save-result]'),
            shareUrl: root.querySelector('[data-share-url]'),
            copy: root.querySelector('[data-copy]'),
            error: root.querySelector('[data-save-error]'),
        };

        this.bind();
        this.channels.forEach((c) => this.renderChannel(c));

        if (config.arrangement) this.loadArrangement(config.arrangement);

        if ('IntersectionObserver' in window) {
            new IntersectionObserver(([e]) => {
                this.visible = e.isIntersecting;
                if (this.playing) stage.emit();
            }, { threshold: 0.1 }).observe(root);
        }
    }

    bind() {
        const { ui } = this;

        ui.play.addEventListener('click', () => (this.playing ? this.pause() : this.play()));
        ui.restart.addEventListener('click', () => this.seekAll(0));
        ui.master.addEventListener('input', () => this.applyMaster());

        for (const c of this.channels) {
            c.fader.addEventListener('input', () => {
                c.level = Number(c.fader.value);
                if (c.level > 0 && c.muted) c.muted = false;
                this.renderChannel(c);
                this.markCustom();
            });
            c.mute.addEventListener('click', () => {
                c.muted = !c.muted;
                this.renderChannel(c);
                this.markCustom();
            });
        }

        ui.presets.forEach((btn) => btn.addEventListener('click', () => this.applyPreset(btn)));
        ui.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.save();
        });
        ui.copy.addEventListener('click', () => this.copyLink());
    }

    ensureGraph() {
        if (this.ready) return;
        const ac = audioContext();
        this.master = ac ? ac.createGain() : null;
        if (this.master) this.master.connect(ac.destination);

        for (const c of this.channels) {
            c.voice = createVoice(c.track.audio, { loop: false, destination: this.master });
            c.analyser = c.voice.connectAnalyser();
            c.buffer = c.analyser ? new Uint8Array(c.analyser.fftSize) : null;
            c.voice.el.addEventListener('ended', () => this.onEnded());
        }
        this.ready = true;
        this.applyMaster();
        this.channels.forEach((c) => this.renderChannel(c));
    }

    async play() {
        stage.claim(this);
        this.ensureGraph();

        const lead = this.channels[0].voice.el;
        const t = lead.currentTime;
        this.channels.forEach((c) => (c.voice.el.currentTime = t));

        try {
            await Promise.all(this.channels.map((c) => c.voice.el.play()));
        } catch {
            this.channels.forEach((c) => c.voice.el.pause());
            stage.release(this);
            return;
        }

        this.playing = true;
        this.root.classList.add('is-playing');
        this.ui.play.setAttribute('aria-label', 'Pause the full orchestra');
        this.tick();
        stage.emit();
    }

    pause() {
        if (!this.playing) return;
        this.playing = false;
        this.channels.forEach((c) => c.voice?.el.pause());
        this.root.classList.remove('is-playing');
        this.ui.play.setAttribute('aria-label', 'Play the full orchestra');
        cancelAnimationFrame(this.raf);
        this.channels.forEach((c) => c.el.style.setProperty('--energy', 0));
        stage.release(this);
    }

    onEnded() {
        if (this.restarting) return;
        this.restarting = true;
        this.seekAll(0);
        if (this.playing) this.channels.forEach((c) => c.voice.el.play().catch(() => {}));
        setTimeout(() => (this.restarting = false), 200);
    }

    seekAll(t) {
        if (!this.ready) return;
        this.channels.forEach((c) => (c.voice.el.currentTime = t));
        this.ui.progress.style.setProperty('--p', 0);
    }

    applyMaster() {
        const v = Number(this.ui.master.value) / 100;
        if (this.master) this.master.gain.setTargetAtTime(v, this.master.context.currentTime, 0.05);
    }

    tick = () => {
        if (!this.playing) return;
        const lead = this.channels[0].voice.el;
        if (lead.duration) this.ui.progress.style.setProperty('--p', lead.currentTime / lead.duration);

        if (!prefersReducedMotion()) {
            const time = performance.now() / 1000;
            for (const c of this.channels) {
                if (!c.analyser) continue;
                const raw = c.muted || c.level === 0 ? 0 : levelOf(c.analyser, c.buffer);
                c.smooth += (Math.min(1, raw * 3.2) - c.smooth) * 0.18;
                c.el.style.setProperty('--energy', c.smooth.toFixed(3));
                c.el.style.setProperty('--swing', `${(Math.sin(time * (1.4 + c.index * 0.17) + c.index) * c.smooth * 14).toFixed(2)}deg`);
            }
        }
        this.raf = requestAnimationFrame(this.tick);
    };

    renderChannel(c) {
        c.fader.value = c.level;
        c.value.textContent = c.muted ? '-' : c.level;
        c.mute.setAttribute('aria-pressed', String(c.muted));
        c.el.classList.toggle('is-muted', c.muted || c.level === 0);
        c.el.style.setProperty('--level', (c.muted ? 0 : c.level) / 100);
        c.voice?.setGain(c.muted ? 0 : perceptual(c.level), 0.06);
    }

    setLevels(levels) {
        for (const c of this.channels) {
            c.level = levels[c.slug] ?? 70;
            c.muted = false;
            this.renderChannel(c);
        }
    }

    applyPreset(btn) {
        const name = btn.dataset.preset;
        const slugs = this.channels.map((c) => c.slug);

        if (name === 'solo') {
            this.soloIndex = (this.soloIndex + 1) % slugs.length;
            this.setLevels(Object.fromEntries(slugs.map((s, i) => [s, i === this.soloIndex ? 85 : 0])));
            btn.textContent = `Solo: ${this.channels[this.soloIndex].track.title}`;
        } else {
            this.soloIndex = -1;
            this.setLevels(PRESETS[name](slugs));
            this.ui.presets.find((b) => b.dataset.preset === 'solo').textContent = 'One at a time';
        }

        this.ui.presets.forEach((b) => b.classList.toggle('is-active', b === btn));
    }

    markCustom() {
        this.ui.presets.forEach((b) => b.classList.remove('is-active'));
    }

    loadArrangement(arr) {
        this.setLevels(arr.levels);
        const chip = document.createElement('p');
        chip.className = 'orchestra__loaded';
        chip.textContent = `Loaded arrangement: "${arr.title}"`;
        this.root.querySelector('.orchestra__head').append(chip);
    }

    async save() {
        const { form, error, result, shareUrl } = this.ui;
        const button = form.querySelector('button[type="submit"]');
        const levels = Object.fromEntries(this.channels.map((c) => [c.slug, c.muted ? 0 : c.level]));

        error.hidden = true;
        button.disabled = true;
        button.classList.add('is-busy');

        try {
            const res = await fetch(this.config.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ title: form.title.value, levels }),
            });
            const body = await res.json().catch(() => ({}));

            if (res.status === 429) throw new Error("That's a lot of saving, give it a minute and try again.");
            if (!res.ok) throw new Error(Object.values(body.errors || {}).flat()[0] || body.message || "Couldn't save just now.");

            shareUrl.value = body.url;
            result.hidden = false;
            shareUrl.select();
        } catch (err) {
            error.textContent = err.message;
            error.hidden = false;
        } finally {
            button.disabled = false;
            button.classList.remove('is-busy');
        }
    }

    async copyLink() {
        const label = this.ui.copy.querySelector('span');
        try {
            await navigator.clipboard.writeText(this.ui.shareUrl.value);
            label.textContent = 'Copied';
        } catch {
            this.ui.shareUrl.select();
            label.textContent = 'Press Ctrl+C';
        }
        setTimeout(() => (label.textContent = 'Copy link'), 2200);
    }
}

export function initOrchestra() {
    const root = document.querySelector('[data-orchestra]');
    if (!root || !window.EARTH_ORCHESTRA) return null;
    return new Orchestra(root, window.EARTH_ORCHESTRA);
}
