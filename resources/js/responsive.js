/* TRIX WAY - Responsive Navigation */
(() => {
    const initResponsiveNavigation = () => {

        // Hindari membuat menu ganda pada Dashboard
        const existingMobileDrawer = document.querySelector(
            'aside.fixed.inset-y-0.left-0.z-50.lg\\:hidden, aside[data-mobile-drawer]'
        );

        if (existingMobileDrawer) return;

        // Cari sidebar desktop
        const desktopSidebar = [...document.querySelectorAll(
            'aside.fixed.inset-y-0.left-0'
        )].find(el => el.querySelector('nav a[href]'));

        const header = document.querySelector('main > header, main header');

        if (!desktopSidebar || !header ||
            document.getElementById('trixMobileMenuButton')) return;

        // Overlay
        const overlay = document.createElement('button');
        overlay.type = 'button';
        overlay.id = 'trixMobileMenuOverlay';
        overlay.setAttribute('aria-label', 'Tutup menu navigasi');
        overlay.className =
            'fixed inset-0 z-[90] bg-slate-950/55 opacity-0 pointer-events-none transition-opacity duration-200';

        overlay.style.border = '0';

        // Mobile drawer
        const drawer = desktopSidebar.cloneNode(true);

        drawer.id = 'trixMobileDrawer';
        drawer.setAttribute('data-mobile-drawer', 'true');

        drawer.classList.remove('hidden', 'lg:flex', 'lg:hidden');
        drawer.classList.add('flex');

        drawer.style.cssText += `
            ;display:flex!important;
            position:fixed!important;
            inset:0 auto 0 0!important;
            width:min(84vw,300px)!important;
            max-width:300px!important;
            height:100dvh!important;
            z-index:100!important;
            transform:translateX(-105%);
            transition:transform .25s ease;
            overflow-y:auto;
            box-shadow:0 20px 50px rgba(0,0,0,.25);
        `;

        drawer.querySelectorAll('[id]').forEach(el => {
            el.removeAttribute('id');
        });

        // Tombol hamburger
        const button = document.createElement('button');

        button.type = 'button';
        button.id = 'trixMobileMenuButton';
        button.setAttribute('aria-label', 'Buka menu navigasi');
        button.setAttribute('aria-expanded', 'false');

        button.className =
            'mr-3 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm lg:hidden';

        button.innerHTML = `
            <svg viewBox="0 0 24 24"
                width="22" height="22"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round">
                <path d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        `;

        const headerFirst = header.firstElementChild;

        if (headerFirst) {
            headerFirst.classList.add('min-w-0');
            headerFirst.parentNode.insertBefore(button, headerFirst);
        } else {
            header.insertBefore(button, header.firstChild);
        }

        document.body.append(overlay, drawer);

        // Buka menu
        function openMenu() {
            drawer.style.transform = 'translateX(0)';
            overlay.style.opacity = '1';
            overlay.style.pointerEvents = 'auto';

            button.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        // Tutup menu
        function closeMenu() {
            drawer.style.transform = 'translateX(-105%)';
            overlay.style.opacity = '0';
            overlay.style.pointerEvents = 'none';

            button.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        button.addEventListener('click', openMenu);
        overlay.addEventListener('click', closeMenu);

        drawer.querySelectorAll('a[href]').forEach(link => {
            link.addEventListener('click', closeMenu);
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeMenu();
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) closeMenu();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initResponsiveNavigation,
            { once: true }
        );
    } else {
        initResponsiveNavigation();
    }
})();