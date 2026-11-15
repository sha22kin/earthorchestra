
const store = {
    get(k) { try { return localStorage.getItem(k); } catch { return null; } },
    set(k, v) { try { localStorage.setItem(k, v); } catch {  } },
};

function initTheme() {
    document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
        const root = document.documentElement;
        const dark = root.dataset.theme !== 'dark';
        if (dark) root.dataset.theme = 'dark';
        else delete root.dataset.theme;
        store.set('eo-admin-theme', dark ? 'dark' : 'light');
    });
}

function initSidebar() {
    const root = document.documentElement;
    const mobile = () => window.matchMedia('(max-width: 960px)').matches;

    document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => {
        if (mobile()) {
            root.classList.toggle('sb-open');
        } else {
            const collapsed = root.classList.toggle('sb-collapsed');
            store.set('eo-admin-sidebar', collapsed ? 'collapsed' : 'open');
        }
    });
    document.querySelector('[data-scrim]')?.addEventListener('click', () => root.classList.remove('sb-open'));
    document.addEventListener('keydown', (e) => e.key === 'Escape' && root.classList.remove('sb-open'));
}

function initToasts() {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        const close = () => {
            toast.classList.add('is-leaving');
            setTimeout(() => toast.remove(), 260);
        };
        toast.querySelector('[data-toast-close]')?.addEventListener('click', close);
        setTimeout(close, 4500);
    });
}

function initConfirm() {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });
}

function initChart() {
    document.querySelectorAll('[data-chart]').forEach((chart) => {
        const data = JSON.parse(chart.dataset.chart);
        const svg = chart.querySelector('svg');
        const tip = chart.querySelector('[data-chart-tip]');
        const cursor = chart.querySelector('[data-chart-cursor]');
        const dots = [...chart.querySelectorAll('[data-chart-dot]')];

        const hide = () => {
            tip.hidden = true;
            cursor.setAttribute('visibility', 'hidden');
            dots.forEach((d) => d.classList.remove('is-on'));
        };

        chart.querySelectorAll('[data-chart-hit]').forEach((hit) => {
            hit.addEventListener('pointerenter', () => {
                const i = Number(hit.dataset.chartHit);
                const x = Number(hit.dataset.x);
                const point = data[i];

                cursor.setAttribute('x1', x);
                cursor.setAttribute('x2', x);
                cursor.setAttribute('visibility', 'visible');
                dots.forEach((d) => d.classList.toggle('is-on', Number(d.dataset.chartDot) === i));

                tip.innerHTML = `<strong>${point.date}</strong>` + point.values
                    .map((v) => `<div><i style="--c:${v.color}"></i>${v.name}<b>${v.value}</b></div>`).join('');
                tip.hidden = false;

                const vb = svg.viewBox.baseVal;
                const left = (x / vb.width) * svg.clientWidth;
                const half = tip.offsetWidth / 2;
                tip.style.left = `${Math.min(svg.clientWidth - half, Math.max(half, left))}px`;
            });
        });
        svg.addEventListener('pointerleave', hide);
    });
}

function initRepeaters() {
    document.querySelectorAll('[data-repeater-add]').forEach((btn) => {
        const name = btn.dataset.repeaterAdd;
        const list = document.querySelector(`[data-repeater="${name}"]`);
        const tpl = document.querySelector(`[data-repeater-template="${name}"]`);

        btn.addEventListener('click', () => {
            const index = Date.now();
            const html = tpl.innerHTML.replaceAll('__i__', index);
            list.insertAdjacentHTML('beforeend', html);
            list.lastElementChild.querySelector('input, textarea')?.focus();
        });
    });

    document.addEventListener('click', (e) => {
        const remove = e.target.closest('[data-repeater-remove]');
        if (remove) remove.closest('.repeater__row')?.remove();
    });
}

function initAutosize() {
    const fit = (el) => {
        el.style.height = 'auto';
        el.style.height = `${Math.min(900, el.scrollHeight + 2)}px`;
    };
    document.querySelectorAll('textarea[data-autosize]').forEach((el) => {
        fit(el);
        el.addEventListener('input', () => fit(el));
    });
}

function initLegendPreview() {
    const input = document.querySelector('textarea[data-legend-input]');
    const preview = document.querySelector('[data-legend-preview]');
    if (!input || !preview) return;

    const bar = preview.querySelector('.legend-preview__bar');
    const label = preview.querySelector('[data-legend-label]');
    const range = preview.querySelector('[data-legend-range]');

    const render = () => {
        try {
            const legend = JSON.parse(input.value);
            const stops = legend.stops || [];
            if (stops.length < 2) throw new Error('need stops');
            const n = stops.length - 1;
            bar.style.background = `linear-gradient(90deg, ${stops.map((s, i) => `${s.color} ${((i / n) * 100).toFixed(2)}%`).join(', ')})`;
            label.textContent = `${legend.label ?? ''} (${legend.unit ?? ''})`;
            range.textContent = `${stops[0].label ?? stops[0].value} → ${stops[n].label ?? stops[n].value}`;
            preview.classList.remove('is-invalid');
        } catch {
            bar.style.background = '';
            label.textContent = 'Invalid JSON';
            range.textContent = '';
            preview.classList.add('is-invalid');
        }
    };
    render();
    input.addEventListener('input', render);
}

function initColorPickers() {
    document.querySelectorAll('[data-color-picker]').forEach((picker) => {
        const text = document.getElementById(picker.dataset.colorPicker);
        picker.addEventListener('input', () => (text.value = picker.value));
        text.addEventListener('input', () => {
            if (/^#[0-9a-f]{6}$/i.test(text.value)) picker.value = text.value;
        });
    });
}

function initUploads() {
    document.querySelectorAll('[data-upload]').forEach((input) => {
        input.addEventListener('change', () => {
            const label = input.closest('.upload');
            const file = input.files[0];
            label.classList.toggle('has-file', !!file);
            if (file) label.querySelector('small').textContent = `${file.name} · ${(file.size / 1048576).toFixed(1)} MB - will upload on save`;

            if (file && input.name === 'video') {
                const video = document.querySelector('[data-video-preview]');
                if (video) video.src = URL.createObjectURL(file);
            }

            if (file && input.name === 'poster') {
                const img = document.querySelector('[data-poster-preview]');
                if (img) img.src = URL.createObjectURL(file);
            }
        });
    });
}

function boot() {
    initTheme();
    initSidebar();
    initToasts();
    initConfirm();
    initChart();
    initRepeaters();
    initAutosize();
    initLegendPreview();
    initColorPickers();
    initUploads();
}

document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot, { once: true }) : boot();
