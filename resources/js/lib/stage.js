const listeners = new Set();
let current = null;

export const stage = {
    claim(player) {
        if (current && current !== player) current.pause();
        current = player;
        this.emit();
    },

    release(player) {
        if (current === player) current = null;
        this.emit();
    },

    get current() {
        return current;
    },

    subscribe(fn) {
        listeners.add(fn);
        return () => listeners.delete(fn);
    },

    emit() {
        for (const fn of listeners) fn(current);
    },
};

export const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;
