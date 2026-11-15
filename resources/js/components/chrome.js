import { stage, prefersReducedMotion } from '../lib/stage.js';

export function initNav() {
    const nav = document.querySelector('[data-nav]');
    if (!nav) return;

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

// Playback speed of the hero intro animation (1 = original, 0.5 = half speed).
const HERO_SPEED = 0.5;

export function initHeroFilm() {
    const hero = document.querySelector('[data-hero]');
    const film = hero?.querySelector('[data-hero-film]');
    if (!film) return;

    const still = () => {
        hero.classList.add('is-still');
        film.pause();
    };

    film.addEventListener('contextmenu', (e) => e.preventDefault());
    film.addEventListener('error', still, true);

    if (prefersReducedMotion()) {
        still();
        return;
    }

    film.defaultPlaybackRate = HERO_SPEED;
    film.playbackRate = HERO_SPEED;

    const attempt = film.play();
    if (attempt) attempt.catch(still);
}

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
