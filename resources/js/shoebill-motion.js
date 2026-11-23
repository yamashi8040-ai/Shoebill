// Shoebill motion. Level comes from <html data-sb-motion="off|standard|rich"> (admin setting); reduced motion wins.
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
const level = () => (reduced.matches ? 'off' : document.documentElement.dataset.sbMotion || 'standard');
const seen = new WeakSet();

// Number ticker: numbers in stat widgets and [data-sb-count] roll up once when shown.
function countUp(el) {
    if (seen.has(el) || el.childElementCount > 0) return;
    seen.add(el);
    const text = el.textContent.trim();
    const match = text.match(/^([^\d-]*)(-?[\d,]*\.?\d+)(.*)$/s);
    if (level() === 'off' || !match) return;

    const [, prefix, raw, suffix] = match;
    const target = parseFloat(raw.replace(/,/g, ''));
    const decimals = (raw.split('.')[1] || '').length;
    const start = performance.now();
    const duration = level() === 'rich' ? 900 : 600;

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
new MutationObserver(() => requestAnimationFrame(scan)).observe(document.documentElement, { childList: true, subtree: true });

// Spotlight (own admin switch, independent of the motion level): a soft light follows the pointer across cards.
const SPOT = '.sb-card, .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat, .fi-ta-record';
document.addEventListener('pointermove', (event) => {
    if (document.documentElement.dataset.sbSpotlight !== 'on' || event.pointerType !== 'mouse') return;
    const card = event.target.closest?.(SPOT);
    if (!card) return;
    const rect = card.getBoundingClientRect();
    card.style.setProperty('--sb-mx', `${event.clientX - rect.left}px`);
    card.style.setProperty('--sb-my', `${event.clientY - rect.top}px`);
}, { passive: true });
