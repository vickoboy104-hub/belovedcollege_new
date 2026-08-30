(() => {
    'use strict';

    const isPortalSectionLink = (anchor) => {
        if (!(anchor instanceof HTMLAnchorElement)) return false;

        let url;
        try {
            url = new URL(anchor.href, window.location.href);
        } catch (_) {
            return false;
        }

        return url.pathname === window.location.pathname
            && url.searchParams.has('section')
            && window.location.pathname.includes('/portal');
    };

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const anchor = event.target.closest('a[href]');
        if (!isPortalSectionLink(anchor)) return;

        const target = new URL(anchor.href, window.location.href);
        const current = new URL(window.location.href);

        if (target.href === current.href) return;

        /*
         * The legacy sidebar uses Alpine .prevent + pushState for portal sections.
         * In some states that updates the URL while leaving stale page content.
         * Capture the click first and perform a real navigation so the server and
         * Alpine state always agree. This also gives normal back/forward behavior.
         */
        event.preventDefault();
        event.stopImmediatePropagation();
        window.location.assign(target.href);
    }, true);
})();
