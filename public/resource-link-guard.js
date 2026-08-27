(() => {
    const NO_RESOURCE_MESSAGE = 'No resources here. Your teacher has not added a supporting resource link yet.';

    const isPlaceholderUrl = (value) => {
        if (!value || value === '#') return true;

        try {
            const url = new URL(value, window.location.origin);
            return /^(www\.)?example\.(com|org|net)$/i.test(url.hostname);
        } catch (error) {
            return true;
        }
    };

    const showNoResourceMessage = (event) => {
        event.preventDefault();
        window.alert(NO_RESOURCE_MESSAGE);
    };

    const enhanceLessonResources = () => {
        if (!document.body.classList.contains('route-portal-index')) return;

        const lessonSection = Array.from(document.querySelectorAll('[x-show]')).find((element) =>
            (element.getAttribute('x-show') || '').includes("activeSection === 'lessons'")
        );

        if (!lessonSection) return;

        lessonSection.querySelectorAll('article').forEach((article) => {
            if (article.dataset.resourceGuardReady === 'true') return;
            article.dataset.resourceGuardReady = 'true';

            const resourceLink = Array.from(article.querySelectorAll('a')).find((link) =>
                (link.textContent || '').toLowerCase().includes('supporting resource')
            );

            if (resourceLink) {
                resourceLink.classList.add('lesson-resource-action');

                if (isPlaceholderUrl(resourceLink.getAttribute('href'))) {
                    resourceLink.removeAttribute('target');
                    resourceLink.setAttribute('href', '#');
                    resourceLink.setAttribute('aria-label', 'No supporting resources available');
                    resourceLink.addEventListener('click', showNoResourceMessage);
                }

                return;
            }

            const fallbackRow = document.createElement('div');
            fallbackRow.className = 'lesson-resource-fallback-row border-t border-slate-100 pt-3 flex';

            const fallbackButton = document.createElement('button');
            fallbackButton.type = 'button';
            fallbackButton.className = 'lesson-resource-action';
            fallbackButton.textContent = 'Supporting Resources →';
            fallbackButton.setAttribute('aria-label', 'No supporting resources available');
            fallbackButton.addEventListener('click', showNoResourceMessage);

            fallbackRow.appendChild(fallbackButton);
            article.appendChild(fallbackRow);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhanceLessonResources, { once: true });
    } else {
        enhanceLessonResources();
    }
})();
