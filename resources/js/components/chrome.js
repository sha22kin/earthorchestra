import { stage, prefersReducedMotion } from '../lib/stage.js';

/* ───────────── Navigation ───────────── */

export function initNav() {
    const nav = document.querySelector('[data-nav]');
    if (!nav) return;

    const toggle = nav.querySelector('[data-nav-toggle]');
    const sheet = nav.querySelector('[data-nav-sheet]');

    // The whole open/close animation is CSS; JS only flips state.
    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', String(open));
        toggle.querySelector('.sr-only').textContent = open ? 'Close menu' : 'Menu';
        nav.classList.toggle('is-open', open);
        document.documentElement.classList.toggle('has-sheet', open);
        sheet.inert = !open;
        sheet.setAttribute('aria-hidden', String(!open));
        if (open) {
            sheet.scrollTop = 0;
            setTimeout(() => sheet.querySelector('a')?.focus({ preventScroll: true }), 350);
        }
    };

    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
    sheet.addEventListener('click', (e) => e.target.closest('a') && setOpen(false));
    window.matchMedia('(min-width: 901px)').addEventListener('change', (e) => e.matches && setOpen(false));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && nav.classList.contains('is-open')) {
            setOpen(false);
            toggle.focus();
        }
    });

    // Condense the bar once the page scrolls.
    let ticking = false;
    const onScroll = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            nav.classList.toggle('is-scrolled', window.scrollY > 24);
            ticking = false;
        });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}

/* ───────────── Reveal on scroll ───────────── */

export function initReveal() {
    const items = document.querySelectorAll('[data-reveal]');
    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-in'));
        return;
    }
    document.documentElement.classList.add('can-reveal');

    const io = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (!entry.isIntersecting) continue;
            entry.target.classList.add('is-in');
            io.unobserve(entry.target);
        }
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    items.forEach((el) => io.observe(el));
}

/* ───────────── Hero parallax ───────────── */

export function initParallax() {
    const hero = document.querySelector('[data-hero]');
    if (!hero || prefersReducedMotion()) return;

    const layers = [...hero.querySelectorAll('[data-depth]')].map((el) => ({ el, depth: Number(el.dataset.depth) }));
    let px = 0, py = 0, sy = 0, frame = null;

    const render = () => {
        frame = null;
        for (const { el, depth } of layers) {
            el.style.setProperty('--px', `${(px * depth * 40).toFixed(1)}px`);
            el.style.setProperty('--py', `${(py * depth * 24 + sy * depth).toFixed(1)}px`);
        }
    };
    const schedule = () => frame ??= requestAnimationFrame(render);

    if (window.matchMedia('(pointer: fine)').matches) {
        hero.addEventListener('pointermove', (e) => {
            const r = hero.getBoundingClientRect();
            px = (e.clientX - r.left) / r.width - 0.5;
            py = (e.clientY - r.top) / r.height - 0.5;
            schedule();
        });
    }

    window.addEventListener('scroll', () => {
        if (window.scrollY > window.innerHeight * 1.2) return;
        sy = window.scrollY * 0.5;
        schedule();
    }, { passive: true });
}

/* ───────────── "Now playing" pill ───────────── */

export function initNowPlaying() {
    const pill = document.querySelector('[data-now-playing]');
    if (!pill) return;

    const title = pill.querySelector('[data-now-playing-title]');
    const link = pill.querySelector('[data-now-playing-link]');

    stage.subscribe((current) => {
        const show = current && !current.visible;
        pill.classList.toggle('is-visible', !!show);
        if (show) {
            pill.hidden = false;
            title.textContent = current.title;
            link.href = current.anchor;
        }
    });

    pill.addEventListener('transitionend', () => {
        if (!pill.classList.contains('is-visible')) pill.hidden = true;
    });
    pill.querySelector('[data-now-playing-stop]').addEventListener('click', () => stage.current?.pause());
}
