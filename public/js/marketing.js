document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.textContent = show ? 'Ocultar' : 'Mostrar';
        button.setAttribute('aria-pressed', String(show));
    });
});
document.querySelectorAll('form[data-submit-label]').forEach((form) => {
    const button = form.querySelector('button[type="submit"]');
    if (!button) return;
    const originalLabel = button.textContent;
    form.addEventListener('submit', () => {
        button.disabled = true;
        button.textContent = form.dataset.submitLabel;
        form.setAttribute('aria-busy', 'true');
    });
    window.addEventListener('pageshow', () => {
        button.disabled = false;
        button.textContent = originalLabel;
        form.removeAttribute('aria-busy');
    });
});
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('.landing-menu[open]').forEach((menu) => {
        menu.open = false;
        menu.querySelector('summary')?.focus();
    });
});
document.querySelectorAll('.landing-menu nav a').forEach((link) => {
    link.addEventListener('click', () => { link.closest('details').open = false; });
});
