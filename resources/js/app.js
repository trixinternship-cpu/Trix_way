import './responsive';


/* =========================================================
   TRIX GLOBAL PAGE LOADER
   Dipasang sekali melalui Vite app.js untuk semua halaman.
   ========================================================= */
(() => {
    if (window.__trixPageLoaderInitialized) return;
    window.__trixPageLoaderInitialized = true;

    const createLoader = () => {
        if (document.getElementById('trix-global-page-loader')) return;

        const loader = document.createElement('div');
        loader.id = 'trix-global-page-loader';
        loader.setAttribute('aria-hidden', 'true');
        loader.innerHTML = `
            <div class="trix-loader-panel">
                <div class="trix-loader-logo"><span class="trix-loader-mark">›</span>TRIX <span>WAY</span></div>
                <div class="trix-loader-spinner" aria-hidden="true"><i></i></div>
                <div class="trix-loader-title">Preparing your workspace</div>
                <div class="trix-loader-subtitle">Please wait a moment...</div>
                <div class="trix-loader-track"><span></span></div>
            </div>`;
        document.body.appendChild(loader);
    };

    const showLoader = () => {
        createLoader();
        const loader = document.getElementById('trix-global-page-loader');
        if (!loader) return;
        loader.classList.remove('trix-loader-hidden');
        loader.classList.add('trix-loader-visible');
        loader.setAttribute('aria-hidden', 'false');
    };

    const hideLoader = () => {
        const loader = document.getElementById('trix-global-page-loader');
        if (!loader) return;
        loader.classList.remove('trix-loader-visible');
        loader.classList.add('trix-loader-hidden');
        loader.setAttribute('aria-hidden', 'true');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createLoader, { once: true });
    } else {
        createLoader();
    }

    // Navigasi melalui link internal.
    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest('a[href]');
        if (!link) return;
        if (link.target && link.target.toLowerCase() !== '_self') return;
        if (link.hasAttribute('download') || link.dataset.noLoader === 'true') return;
        if (link.getAttribute('rel')?.split(/\s+/).includes('external')) return;

        let destination;
        try { destination = new URL(link.href, window.location.href); } catch (_) { return; }
        if (destination.origin !== window.location.origin) return;
        if (destination.pathname === window.location.pathname && destination.search === window.location.search) return;
        showLoader();
    }, true);

    // Form GET yang memindahkan halaman juga menampilkan loading.
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented) return;
        if ((form.method || 'get').toLowerCase() !== 'get') return;
        if (form.target && form.target.toLowerCase() !== '_self') return;
        if (form.dataset.noLoader === 'true') return;
        showLoader();
    }, true);

    // Hilangkan overlay setelah halaman selesai dimuat / saat kembali dari BFCache.
    window.addEventListener('pageshow', hideLoader);
    window.addEventListener('load', hideLoader, { once: true });
})();
