(() => {
    const script = document.currentScript;
    if (!script) return;

    const entries = [
        {
            group: 'Administration',
            after: 'Staff Management',
            label: 'Teacher Access',
            href: script.dataset.teacherAccessUrl,
            icon: 'teacher',
        },
        {
            group: 'Finance',
            after: 'Finance Records',
            label: 'Payment Gateways',
            href: script.dataset.paymentGatewaysUrl,
            icon: 'payment',
        },
    ].filter((entry) => entry.href);

    const icons = {
        teacher: '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14c3.314 0 6-1.79 6-4s-2.686-4-6-4-6 1.79-6 4 2.686 4 6 4Zm0 0v6m-4 0h8M5 10V7.5L12 4l7 3.5V10"/></svg>',
        payment: '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm2 9h4"/></svg>',
    };

    const isCurrent = (href) => {
        try {
            return new URL(href, window.location.origin).pathname === window.location.pathname;
        } catch (error) {
            return false;
        }
    };

    const createLink = (entry) => {
        const link = document.createElement('a');
        link.href = entry.href;
        link.dataset.adminDirectNavigation = entry.label;
        link.className = `app-sidebar-link ${isCurrent(entry.href) ? 'is-active' : ''}`;
        if (isCurrent(entry.href)) link.setAttribute('aria-current', 'page');
        link.innerHTML = `
            <span class="app-sidebar-link-icon">${icons[entry.icon] || ''}</span>
            <span class="app-sidebar-link-text">${entry.label}</span>
        `;
        return link;
    };

    const insertLinks = () => {
        document.querySelectorAll('.app-sidebar-group').forEach((group) => {
            const groupLabel = group.querySelector('.app-sidebar-label')?.textContent?.trim();
            const container = group.querySelector(':scope > div.mt-2');
            if (!groupLabel || !container) return;

            entries
                .filter((entry) => entry.group === groupLabel)
                .forEach((entry) => {
                    if (container.querySelector(`[data-admin-direct-navigation="${entry.label}"]`)) return;

                    const targetText = Array.from(container.querySelectorAll('.app-sidebar-link-text'))
                        .find((element) => element.textContent?.trim() === entry.after);
                    const targetLink = targetText?.closest('.app-sidebar-link');
                    const targetNode = targetLink?.tagName === 'BUTTON' ? targetLink.parentElement : targetLink;
                    const link = createLink(entry);

                    if (targetNode?.parentElement === container) {
                        targetNode.insertAdjacentElement('afterend', link);
                    } else {
                        container.appendChild(link);
                    }
                });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', insertLinks, { once: true });
    } else {
        insertLinks();
    }
})();
