/**
 * "Read aloud" buttons using the browser's own speech synthesis.
 * Where the CSS Custom Highlight API exists, the word being spoken is
 * highlighted in place without touching the DOM.
 */
const synth = window.speechSynthesis;
const canHighlight = typeof CSS !== 'undefined' && 'highlights' in CSS && typeof Highlight !== 'undefined';

let active = null; // { button, target }

function pickVoice() {
    const voices = synth.getVoices();
    return (
        voices.find((v) => v.lang === 'en-GB' && /natural|google|daniel|libby|sonia/i.test(v.name)) ||
        voices.find((v) => v.lang === 'en-GB') ||
        voices.find((v) => v.lang?.startsWith('en')) ||
        null
    );
}

/** Text nodes with their offsets, so a character index maps back to a Range. */
function collectText(root) {
    const nodes = [];
    let text = '';
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
        acceptNode: (n) =>
            n.parentElement.closest('sup, .read-aloud, [aria-hidden="true"]')
                ? NodeFilter.FILTER_REJECT
                : NodeFilter.FILTER_ACCEPT,
    });
    while (walker.nextNode()) {
        const node = walker.currentNode;
        const content = node.textContent;
        if (!content.trim()) continue;
        if (text && !/\s$/.test(text)) text += ' ';
        nodes.push({ node, start: text.length });
        text += content;
    }
    return { nodes, text };
}

function rangeFor(nodes, index, length) {
    for (let i = nodes.length - 1; i >= 0; i--) {
        const { node, start } = nodes[i];
        if (index >= start) {
            const offset = index - start;
            const end = Math.min(node.textContent.length, offset + length);
            if (offset >= node.textContent.length) return null;
            const r = new Range();
            r.setStart(node, offset);
            r.setEnd(node, end);
            return r;
        }
    }
    return null;
}

function stop() {
    synth.cancel();
    if (canHighlight) CSS.highlights.delete('tts-word');
    if (active) {
        active.button.classList.remove('is-speaking');
        active.button.querySelector('[data-tts-label]').textContent = active.label;
        active.target.classList.remove('is-being-read');
        active = null;
    }
}

function speak(button) {
    const target = document.querySelector(button.dataset.tts);
    if (!target) return;

    const { nodes, text } = collectText(target);
    const utterance = new SpeechSynthesisUtterance(text);
    const voice = pickVoice();
    if (voice) utterance.voice = voice;
    utterance.lang = voice?.lang || 'en-GB';
    utterance.rate = 0.98;

    const labelEl = button.querySelector('[data-tts-label]');
    active = { button, target, label: labelEl.textContent };
    button.classList.add('is-speaking');
    labelEl.textContent = 'Stop reading';
    target.classList.add('is-being-read');

    utterance.addEventListener('boundary', (e) => {
        if (!canHighlight || e.name !== 'word') return;
        const length = e.charLength || (text.slice(e.charIndex).match(/^\S+/) || [''])[0].length;
        const range = rangeFor(nodes, e.charIndex, length);
        if (range) CSS.highlights.set('tts-word', new Highlight(range));
    });
    utterance.addEventListener('end', () => active?.button === button && stop());
    utterance.addEventListener('error', () => active?.button === button && stop());

    synth.speak(utterance);
}

export function initReadAloud() {
    if (!synth || typeof SpeechSynthesisUtterance === 'undefined') return;

    // Some browsers load voices asynchronously.
    synth.getVoices();
    synth.addEventListener?.('voiceschanged', () => synth.getVoices());

    document.querySelectorAll('[data-tts]').forEach((button) => {
        button.hidden = false;
        button.addEventListener('click', () => {
            const same = active?.button === button;
            stop();
            if (!same) speak(button);
        });
    });

    window.addEventListener('pagehide', stop);
}
