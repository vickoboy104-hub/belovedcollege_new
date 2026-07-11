(() => {
    'use strict';

    const desktopQuery = window.matchMedia('(min-width: 1024px)');
    const storageKey = 'beloved:desktop-sidebar-scroll:v1';

    let sidebar = null;
    let saveFrame = 0;
    let userInteracted = false;

    const readPosition = () => {
        try {
            const value = Number.parseInt(window.sessionStorage.getItem(storageKey) ?? '0', 10);
            return Number.isFinite(value) && value > 0 ? value : 0;
        } catch (error) {
            return 0;
        }
    };

    const writePosition = () => {
        if (!sidebar || !desktopQuery.matches) {
            return;
        }

        try {
            window.sessionStorage.setItem(storageKey, String(Math.max(0, Math.round(sidebar.scrollTop))));
        } catch (error) {
            // Storage can be unavailable in hardened/private browser modes.
        }
    };

    const scheduleWrite = () => {
        if (saveFrame) {
            return;
        }

        saveFrame = window.requestAnimationFrame(() => {
            saveFrame = 0;
            writePosition();
        });
    };

    const applySavedPosition = () => {
        if (!sidebar || !desktopQuery.matches || userInteracted) {
            return;
        }

        const savedPosition = readPosition();
        const maximumPosition = Math.max(0, sidebar.scrollHeight - sidebar.clientHeight);
        sidebar.scrollTop = Math.min(savedPosition, maximumPosition);
    };

    const restorePosition = () => {
        if (!sidebar || !desktopQuery.matches) {
            return;
        }

        userInteracted = false;
        applySavedPosition();

        // Alpine expands the active menu group after the first paint. Reapply the
        // saved position as the sidebar reaches its final height, unless the user
        // has already started scrolling it manually.
        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(applySavedPosition);
        });
        window.setTimeout(applySavedPosition, 80);
        window.setTimeout(applySavedPosition, 220);
    };

    const markInteraction = () => {
        userInteracted = true;
    };

    const saveBeforeNavigation = (event) => {
        const link = event.target.closest('a[href]');

        if (!link || link.target === '_blank' || link.hasAttribute('download')) {
            return;
        }

        writePosition();
    };

    const initialise = () => {
        sidebar = document.querySelector('.app-sidebar .app-sidebar-scroll');

        if (!sidebar) {
            return;
        }

        sidebar.addEventListener('scroll', scheduleWrite, { passive: true });
        sidebar.addEventListener('wheel', markInteraction, { passive: true });
        sidebar.addEventListener('touchstart', markInteraction, { passive: true });
        sidebar.addEventListener('pointerdown', markInteraction, { passive: true });
        sidebar.addEventListener('click', saveBeforeNavigation, true);

        window.addEventListener('pagehide', writePosition);
        window.addEventListener('beforeunload', writePosition);
        window.addEventListener('pageshow', restorePosition);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                writePosition();
            }
        });

        desktopQuery.addEventListener?.('change', (event) => {
            if (event.matches) {
                restorePosition();
            }
        });

        restorePosition();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, { once: true });
    } else {
        initialise();
    }
})();
