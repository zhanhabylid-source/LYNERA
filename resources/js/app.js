import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const LOADER_ID = 'global-page-loader';

    const ensureLoader = () => {
        let loader = document.getElementById(LOADER_ID);
        if (loader) return loader;

        loader = document.createElement('div');
        loader.id = LOADER_ID;
        loader.className = 'global-loader hidden';
        loader.innerHTML = `
            <div class="global-loader__backdrop"></div>
            <div class="global-loader__box" role="status" aria-live="polite" aria-label="Memuat">
                <span class="global-loader__spinner" aria-hidden="true"></span>
                <span class="global-loader__text">Memuat...</span>
            </div>
        `;
        document.body.appendChild(loader);
        return loader;
    };

    const showLoader = () => {
        const loader = ensureLoader();
        loader.classList.remove('hidden');
        document.body.classList.add('is-loading');
    };

    const hideLoader = () => {
        const loader = ensureLoader();
        loader.classList.add('hidden');
        document.body.classList.remove('is-loading');
    };

    hideLoader();
    window.addEventListener('pageshow', hideLoader);
    window.addEventListener('load', hideLoader);

    document.addEventListener('click', (event) => {
        const anchor = event.target instanceof Element ? event.target.closest('a[href]') : null;
        if (!anchor) return;
        if (anchor.hasAttribute('data-no-loading')) return;
        if (anchor.target === '_blank' || anchor.hasAttribute('download')) return;
        if (event.defaultPrevented) return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const href = anchor.getAttribute('href') || '';
        if (href.startsWith('#') || href.startsWith('javascript:')) return;

        const url = new URL(anchor.href, window.location.href);
        if (url.origin !== window.location.origin) return;

        showLoader();
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('data-no-loading')) return;

        const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
        if (submitter) {
            submitter.setAttribute('disabled', 'disabled');
            submitter.classList.add('opacity-60', 'cursor-not-allowed');
            submitter.setAttribute('data-loading-disabled', 'true');
        }

        showLoader();
    }, true);
});
