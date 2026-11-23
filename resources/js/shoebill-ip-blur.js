// IP addresses stay blurred until the pointer is over them (or the field has focus), so screenshots and streams don't leak them.
const IP = /\b(?:\d{1,3}\.){3}\d{1,3}\b|\b(?:[0-9a-f]{1,4}:){2,7}[0-9a-f]{0,4}\b|(?:^|\s)::(?:\s|$)/i;
const SKIP = 'script, style, .sb-ip, .xterm, .monaco-editor, pre, code';

function mark(root) {
    root.querySelectorAll('body *:not(' + SKIP + ')').forEach((el) => {
        if (el.closest('.monaco-editor, .xterm')) return;
        if (el.matches('input:not([type=hidden]), textarea')) {
            el.classList.toggle('sb-ip-input', IP.test(el.value));
            return;
        }
        // Only leaf elements, so the blur covers the address and not the whole row.
        if (el.childElementCount === 0 && IP.test(el.textContent)) el.classList.add('sb-ip');
    });
}

let queued = false;
const schedule = () => {
    if (queued) return;
    queued = true;
    requestAnimationFrame(() => {
        queued = false;
        mark(document);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    mark(document);
    // Livewire re-renders replace classes, so re-mark after every change.
    new MutationObserver(schedule).observe(document.body, { childList: true, subtree: true, characterData: true, attributes: true, attributeFilter: ['class', 'value'] });
    document.addEventListener('input', schedule, true);
});
