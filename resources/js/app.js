import './bootstrap';

const html = document.documentElement;

function applyTheme(theme) {
    const dark = theme === 'dark';
    html.classList.toggle('dark', dark);
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.textContent = dark ? 'Mode Terang' : 'Mode Gelap';
    });
}

applyTheme(localStorage.getItem('theme') || 'light');

window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const next = html.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem('theme', next);
            applyTheme(next);
        });
    });

    document.querySelectorAll('[data-mobile-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.querySelector(button.dataset.mobileToggle);
            if (target) target.classList.toggle('hidden');
        });
    });

    const splash = document.getElementById('page-splash');
    if (splash) {
        setTimeout(() => splash.classList.add('opacity-0', 'pointer-events-none'), 600);
    }

    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;

        document.querySelectorAll('[data-install-app]').forEach((button) => {
            button.classList.remove('hidden');
        });
    });

    document.querySelectorAll('[data-install-app]').forEach((installButton) => {
        installButton.addEventListener('click', async () => {
            if (!deferredPrompt) {
                window.alert('Fitur install app muncul kalau browser mendukung PWA. Coba pakai Chrome/Edge lalu pilih Install App dari address bar.');
                return;
            }

            deferredPrompt.prompt();
            await deferredPrompt.userChoice;
            deferredPrompt = null;
        });
    });

    document.querySelectorAll('[data-qty-group]').forEach((group) => {
        const input = group.querySelector('input[data-qty-input]');
        const minus = group.querySelector('[data-qty-minus]');
        const plus = group.querySelector('[data-qty-plus]');
        if (!input || !minus || !plus) return;

        minus.addEventListener('click', () => {
            input.value = Math.max(parseInt(input.min || '1', 10), parseInt(input.value || '1', 10) - 1);
            input.dispatchEvent(new Event('change'));
        });

        plus.addEventListener('click', () => {
            const max = parseInt(input.max || '99', 10);
            input.value = Math.min(max, parseInt(input.value || '1', 10) + 1);
            input.dispatchEvent(new Event('change'));
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.querySelector(button.dataset.modalClose);
            if (target) target.remove();
        });
    });

    if ('serviceWorker' in navigator) {
        const isLocal = ['localhost', '127.0.0.1'].includes(window.location.hostname);

        if (isLocal) {
            navigator.serviceWorker.getRegistrations().then((registrations) => {
                registrations.forEach((registration) => registration.unregister());
            });

            if (window.caches) {
                caches.keys().then((keys) => {
                    keys.forEach((key) => {
                        if (key.startsWith('cafe-hagi-')) caches.delete(key);
                    });
                });
            }
        } else {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    }
});