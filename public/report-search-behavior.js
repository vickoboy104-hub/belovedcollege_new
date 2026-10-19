(() => {
    const neutraliseForcedSearch = () => {
        const originalInput = document.getElementById('report-student-search');
        if (!originalInput || originalInput.dataset.optionalSearchReady === 'true') return originalInput;

        const input = originalInput.cloneNode(true);
        input.removeAttribute('list');
        input.removeAttribute('autofocus');
        input.removeAttribute('data-report-search-input');
        input.dataset.optionalSearchReady = 'true';
        originalInput.replaceWith(input);

        return input;
    };

    if (document.readyState === 'loading') {
        const observer = new MutationObserver(() => {
            const input = neutraliseForcedSearch();
            if (input) observer.disconnect();
        });

        observer.observe(document.documentElement, {
            childList: true,
            subtree: true,
        });
    }

    const initialiseReportSearch = () => {
        if (!document.body.classList.contains('route-admin-reports-index')) return;

        const input = neutraliseForcedSearch();
        const dataElement = document.getElementById('report-student-search-data');

        if (!input || !dataElement || input.dataset.optionalSearchInitialised === 'true') return;
        input.dataset.optionalSearchInitialised = 'true';

        let records = [];
        try {
            records = JSON.parse(dataElement.textContent || '[]');
        } catch (error) {
            records = [];
        }

        document.getElementById('report-student-options')?.remove();
        const openLink = document.querySelector('[data-report-open-link]');

        const searchFieldWrapper = input.closest('div');
        const helperText = searchFieldWrapper?.querySelector('p');
        if (helperText) {
            helperText.textContent = 'Type freely and click Search. Student suggestions are optional, and an Open Report link appears once the text points at exactly one student.';
        }

        const normalize = (value) => String(value || '')
            .toLowerCase()
            .replace(/[—–-]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();

        const matchingRecords = () => {
            const query = normalize(input.value);
            const words = query.split(' ').filter(Boolean);

            return records.filter((record) => {
                if (words.length === 0) return true;

                const haystack = normalize([
                    record.label,
                    record.name,
                    record.studentId,
                    record.admissionNo,
                    record.className,
                ].join(' '));

                return words.every((word) => haystack.includes(word));
            });
        };

        const getMatches = () => matchingRecords().slice(0, 20);

        const exactRecord = () => {
            const query = normalize(input.value);
            if (!query) return null;

            return records.find((record) => [
                record.label,
                record.name,
                record.studentId,
                record.admissionNo,
            ].some((candidate) => normalize(candidate) === query)) || null;
        };

        const resolvedRecord = () => {
            const exact = exactRecord();
            if (exact) return exact;

            const matches = matchingRecords();
            return normalize(input.value) && matches.length === 1 ? matches[0] : null;
        };

        const updateOpenLink = () => {
            if (!openLink) return;

            const record = resolvedRecord();
            openLink.hidden = !record;

            if (record) openLink.href = record.url;
            else openLink.removeAttribute('href');
        };

        input.addEventListener('input', updateOpenLink);

        if (searchFieldWrapper && records.length > 0) {
            const picker = document.createElement('details');
            picker.className = 'report-suggestion-picker';

            const summary = document.createElement('summary');
            summary.textContent = 'Browse student suggestions (optional)';
            summary.setAttribute('aria-label', 'Show optional student suggestions');

            const panel = document.createElement('div');
            panel.className = 'report-suggestion-panel';

            const renderSuggestions = () => {
                panel.replaceChildren();
                const matches = getMatches();

                if (matches.length === 0) {
                    const empty = document.createElement('p');
                    empty.className = 'report-suggestion-empty';
                    empty.textContent = 'No matching students. Continue typing or submit the normal search.';
                    panel.appendChild(empty);
                    return;
                }

                matches.forEach((record) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'report-suggestion-option';

                    const title = document.createElement('strong');
                    title.textContent = record.name || record.label;

                    const meta = document.createElement('span');
                    meta.textContent = [record.studentId, record.admissionNo, record.className].filter(Boolean).join(' • ');

                    button.append(title, meta);
                    button.addEventListener('click', () => {
                        input.value = record.studentId || record.admissionNo || record.name || '';
                        picker.open = false;
                        updateOpenLink();
                        input.focus();
                    });

                    panel.appendChild(button);
                });
            };

            picker.append(summary, panel);
            searchFieldWrapper.appendChild(picker);

            picker.addEventListener('toggle', () => {
                if (picker.open) renderSuggestions();
            });

            input.addEventListener('input', () => {
                if (picker.open) renderSuggestions();
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') picker.open = false;
            });
        }

        /* The field can arrive pre-filled from a previous search, so resolve once up front. */
        updateOpenLink();

        /* Class/category navigation must browse independently of any search text. */
        document.querySelectorAll('a[href]').forEach((anchor) => {
            let url;
            try {
                url = new URL(anchor.href, window.location.href);
            } catch (error) {
                return;
            }

            const isReportDirectoryRoute = /^\/admin\/reports(?:\/[^/]+)?\/?$/.test(url.pathname);
            if (!isReportDirectoryRoute || url.origin !== window.location.origin) return;

            url.searchParams.delete('search');
            anchor.href = url.pathname + url.search + url.hash;
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialiseReportSearch, { once: true });
    } else {
        initialiseReportSearch();
    }
})();
