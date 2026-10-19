// Bootstrap's CSS is imported from resources/css/app.css so it always loads before our overrides.
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

// ---------------------------------------------------------------------------
// Light / dark theme (visual only). The first paint is handled by the inline
// script in layouts/partials/theme-init.blade.php; this wires up the toggle.
// ---------------------------------------------------------------------------
const THEME_KEY = 'connectly-theme';
const darkQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

const readStoredTheme = () => {
    try {
        const value = localStorage.getItem(THEME_KEY);
        return value === 'light' || value === 'dark' ? value : null;
    } catch (e) {
        return null;
    }
};

const applyTheme = (theme) => {
    const root = document.documentElement;
    root.setAttribute('data-theme', theme);
    root.setAttribute('data-bs-theme', theme);
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
    });
};

const currentTheme = () => document.documentElement.getAttribute('data-theme')
    || (darkQuery && darkQuery.matches ? 'dark' : 'light');

document.addEventListener('DOMContentLoaded', () => {
    applyTheme(currentTheme());

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const next = currentTheme() === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            try {
                localStorage.setItem(THEME_KEY, next);
            } catch (e) {
                /* storage unavailable: the choice just lasts for this page */
            }
        });
    });

    // Follow the system setting until the person makes their own choice.
    if (darkQuery && darkQuery.addEventListener) {
        darkQuery.addEventListener('change', (event) => {
            if (!readStoredTheme()) {
                applyTheme(event.matches ? 'dark' : 'light');
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // 1. Tell screen readers which fields are invalid and move focus to the first one.
    const invalidFields = document.querySelectorAll('.is-invalid');
    invalidFields.forEach((field) => field.setAttribute('aria-invalid', 'true'));
    if (invalidFields.length > 0) {
        invalidFields[0].focus();
    }

    // 2. Show "typed / maximum" under every text area that has a limit.
    document.querySelectorAll('textarea[maxlength]').forEach((textarea) => {
        const max = textarea.getAttribute('maxlength');
        const counter = document.createElement('div');
        counter.className = 'form-text text-end char-counter';
        counter.setAttribute('aria-hidden', 'true');

        const update = () => {
            counter.textContent = textarea.value.length + ' / ' + max;
        };

        textarea.addEventListener('input', update);
        textarea.insertAdjacentElement('afterend', counter);
        update();
    });
});

// 3. Show a spinner and block double clicks while a form is being sent.
document.addEventListener('submit', (event) => {
    // The user pressed "Cancel" in a confirmation box: do nothing.
    if (event.defaultPrevented) {
        return;
    }

    event.target.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.insertAdjacentHTML(
            'afterbegin',
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
        );
    });
});

// If the user comes back with the browser's Back button, make the buttons usable again.
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll('button[aria-busy="true"]').forEach((button) => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.querySelector('.spinner-border')?.remove();
    });
});
