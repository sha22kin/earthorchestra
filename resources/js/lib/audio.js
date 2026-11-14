let ctx = null;

export function audioContext() {
    if (!ctx) {
        const Ctor = window.AudioContext || window.webkitAudioContext;
        if (!Ctor) return null;
        ctx = new Ctor({ latencyHint: 'interactive' });
    }
    if (ctx.state === 'suspended') ctx.resume();
    return ctx;
}

export function createVoice(src, { loop = true, destination = null } = {}) {
    const el = new Audio();
    el.src = src;
    el.loop = loop;
    el.preload = 'auto';
    el.crossOrigin = 'anonymous';

    const ac = audioContext();
    if (!ac) {
        return {
            el,
            setGain(v) { el.volume = Math.max(0, Math.min(1, v)); },
            connectAnalyser() { return null; },
        };
    }

    const source = ac.createMediaElementSource(el);
    const gain = ac.createGain();
    gain.gain.value = 0;
    source.connect(gain);
    gain.connect(destination || ac.destination);

    return {
        el,
        gain,
        output: gain,
        setGain(v, smoothing = 0.08) {
            gain.gain.setTargetAtTime(Math.max(0, Math.min(1, v)), ac.currentTime, smoothing);
        },
        connectAnalyser() {
            const analyser = ac.createAnalyser();
            analyser.fftSize = 256;
            gain.connect(analyser);
            return analyser;
        },
    };
}

export function levelOf(analyser, buffer) {
    analyser.getByteTimeDomainData(buffer);
    let sum = 0;
    for (let i = 0; i < buffer.length; i++) {
        const v = (buffer[i] - 128) / 128;
        sum += v * v;
    }
    return Math.sqrt(sum / buffer.length);
}

export function formatTime(seconds) {
    if (!Number.isFinite(seconds)) return '0:00';
    const s = Math.max(0, Math.floor(seconds));
    return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;
}
