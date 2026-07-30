const body = document.body;
const nav = document.querySelector('[data-drawer]');
const navToggle = document.querySelector('[data-drawer-toggle]');
const navOverlay = document.querySelector('[data-drawer-overlay]');
let navReturnFocus = null;

const closeNav = () => {
    delete body.dataset.navOpen;
    navToggle?.setAttribute('aria-expanded', 'false');
    nav?.setAttribute('aria-hidden', matchMedia('(max-width: 63.99rem)').matches ? 'true' : 'false');
    navReturnFocus?.focus();
};

navToggle?.addEventListener('click', () => {
    navReturnFocus = document.activeElement;
    body.dataset.navOpen = 'true';
    navToggle.setAttribute('aria-expanded', 'true');
    nav?.setAttribute('aria-hidden', 'false');
    nav?.querySelector('a,button')?.focus();
});
navOverlay?.addEventListener('click', closeNav);

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && body.dataset.navOpen) closeNav();
});

document.querySelectorAll('[data-toast-dismiss]').forEach((button) => button.addEventListener('click', () => button.closest('[data-toast]')?.remove()));

let activeDialog = null;
let dialogTrigger = null;
document.querySelectorAll('[data-confirm]').forEach((trigger) => trigger.addEventListener('click', () => {
    const dialog = document.getElementById('pulse-confirm-dialog');
    const form = trigger.form || trigger.closest('form');
    if (!dialog || !form) return;
    dialogTrigger = trigger;
    dialog.querySelector('[data-confirm-title]').textContent = trigger.dataset.confirmTitle || 'Confirm action';
    dialog.querySelector('[data-confirm-message]').textContent = trigger.dataset.confirmMessage || 'This action cannot be undone.';
    dialog.querySelector('[data-confirm-submit]').onclick = () => {
        dialog.querySelector('[data-confirm-submit]').disabled = true;
        form.requestSubmit(trigger);
    };
    activeDialog = dialog;
    dialog.showModal();
}));
document.querySelectorAll('[data-dialog-cancel]').forEach((button) => button.addEventListener('click', () => activeDialog?.close()));
document.getElementById('pulse-confirm-dialog')?.addEventListener('close', () => { dialogTrigger?.focus(); activeDialog = null; });

window.PulseConfirm = (title, message) => new Promise((resolve) => {
    const dialog = document.getElementById('pulse-confirm-dialog');
    if (!dialog) { resolve(false); return; }
    dialog.querySelector('[data-confirm-title]').textContent = title;
    dialog.querySelector('[data-confirm-message]').textContent = message;
    const submit = dialog.querySelector('[data-confirm-submit]');
    submit.disabled = false;
    submit.onclick = () => { dialog.close(); resolve(true); };
    dialog.addEventListener('close', () => resolve(false), { once: true });
    activeDialog = dialog;
    dialog.showModal();
});

window.PulseToast = {
    show(message, variant = 'info', options = {}) {
        const region = document.querySelector('[data-toast-region]');
        if (!region) return;
        const toast = document.createElement('div');
        toast.className = `p-toast p-toast--${variant}`;
        toast.dataset.toast = '';
        toast.setAttribute('role', variant === 'error' ? 'alert' : 'status');
        const body = document.createElement('div');
        body.className = 'p-toast__body';
        body.textContent = String(message);
        toast.append(body);
        if (options.action?.label && options.action?.href) {
            const action = document.createElement('a'); action.textContent = options.action.label; action.href = options.action.href; body.append(' ', action);
        }
        const close = document.createElement('button'); close.type = 'button'; close.className = 'p-button p-button--subtle p-icon-button'; close.setAttribute('aria-label', 'Dismiss notification'); close.textContent = '×'; close.onclick = () => toast.remove(); toast.append(close);
        region.append(toast);
        if (!options.persistent) setTimeout(() => toast.remove(), options.duration || 6000);
    },
};
