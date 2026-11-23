// Stat card count-up (number ticker): numbers in stat widgets and [data-sb-count] roll up once when shown.
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
const seen = new WeakSet();

function countUp(el) {
    if (seen.has(el)) return;
    seen.add(el);
    const text = el.textContent.trim();
    const match = text.match(/^([^\d-]*)(-?[\d,]*\.?\d+)(.*)$/s);
    if (reduced.matches || !match) return;

    const [, prefix, raw, suffix] = match;
    const target = parseFloat(raw.replace(/,/g, ''));
    const decimals = (raw.split('.')[1] || '').length;
    const start = performance.now();
    const duration = 600;

    const frame = (now) => {
        const t = Math.min(1, (now - start) / duration);
        const eased = 1 - Math.pow(1 - t, 3);
        el.textContent = prefix + (target * eased).toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + suffix;
        if (t < 1) requestAnimationFrame(frame);
        else el.textContent = text; // end on the exact server-rendered string
    };
    requestAnimationFrame(frame);
}

const scan = () => document.querySelectorAll('.fi-wi-stats-overview-stat-value, [data-sb-count]').forEach(countUp);

document.addEventListener('DOMContentLoaded', scan);
document.addEventListener('livewire:navigated', scan);
