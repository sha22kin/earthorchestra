import { initNav, initReveal, initParallax, initNowPlaying } from './components/chrome.js';
import { initReadAloud } from './components/read-aloud.js';
import { initMovements } from './components/movement.js';
import { initOrchestra } from './components/orchestra.js';

function boot() {
    document.documentElement.classList.add('js');

    initNav();
    initReveal();
    initParallax();
    initNowPlaying();
    initReadAloud();
    initMovements();
    initOrchestra();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}
