(() => {
    const normalize = (value) => String(value || '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    const routeClass = Array.from(document.body.classList)
        .find((className) => className.startsWith('route-')) || '';

    const directoryProfiles = [
        {
            route: 'route-admin-students-index',
            required: ['student', 'student id', 'class', 'status', 'actions'],
            secondary: ['admission no', 'guardian', 'phone'],
        },
        {
            route: 'route-admin-parents-index',
            required: ['parent guardian', 'children', 'status', 'actions'],
            secondary: ['classes', 'phone'],
        },
        {
            route: 'route-admin-staff-index',
            required: ['staff member', 'designation staff id', 'actions'],
            secondary: ['salary setup'],
        },
        {
            route: 'route-admin-reports-index',
            required: ['student', 'student id', 'class', 'term', 'actions'],
            secondary: ['admission no', 'guardian'],
        },
    ];

    const rowCells = (row) => Array.from(row?.children || [])
        .filter((cell) => cell.matches('th, td'));

    const markColumn = (table, columnIndex, className) => {
        if (columnIndex < 0) return;

        table.querySelectorAll('tr').forEach((row) => {
            const cell = rowCells(row)[columnIndex];
            cell?.classList.add(className);
        });
    };

    const applyDirectoryProfile = (table, headerLabels) => {
        const profile = directoryProfiles.find((candidate) => {
            if (candidate.route !== routeClass) return false;
            return candidate.required.every((requiredLabel) => headerLabels.includes(requiredLabel));
        });

        if (!profile) return;

        table.classList.add('is-entity-directory-table');

        profile.secondary.forEach((secondaryLabel) => {
            const columnIndex = headerLabels.indexOf(secondaryLabel);
            markColumn(table, columnIndex, 'table-secondary-column');
        });
    };

    const enhanceTable = (table) => {
        if (!(table instanceof HTMLTableElement) || table.dataset.tableConsistencyReady === 'true') return;

        const headerRow = table.tHead?.rows?.[0];
        const headerCells = rowCells(headerRow);
        if (headerCells.length === 0) return;

        const headerLabels = headerCells.map((header) => normalize(header.textContent));
        let actionColumnIndex = headerLabels.findIndex((label) => label === 'action' || label === 'actions');

        if (actionColumnIndex < 0 && table.hasAttribute('data-sticky-actions')) {
            actionColumnIndex = headerCells.length - 1;
        }

        if (actionColumnIndex >= 0) {
            table.classList.add('has-sticky-actions', 'is-table-consistency-ready');
            table.closest('.admin-table-card, .admin-table-wrap')?.classList.add('has-pinned-table-actions');
            markColumn(table, actionColumnIndex, 'table-action-column');

            const actionCells = Array.from(table.tBodies)
                .flatMap((body) => Array.from(body.rows))
                .map((row) => rowCells(row)[actionColumnIndex])
                .filter(Boolean);

            const hasMultipleActions = actionCells.some((cell) => {
                if (cell.querySelector('.table-action-group')) return true;
                return cell.querySelectorAll(':scope > a, :scope > button, :scope > form, :scope > div > a, :scope > div > button, :scope > div > form').length > 1;
            });

            if (hasMultipleActions) table.classList.add('has-multiple-row-actions');
        }

        if (table.classList.contains('has-sticky-edge-columns')) {
            const primaryColumnIndex = headerLabels.findIndex((label, index) => {
                if (index === actionColumnIndex) return false;
                const header = headerCells[index];
                return !header.querySelector('input[type="checkbox"]');
            });

            markColumn(table, primaryColumnIndex, 'table-primary-column');
        }

        applyDirectoryProfile(table, headerLabels);
        table.dataset.tableConsistencyReady = 'true';
    };

    const enhanceAllTables = (root = document) => {
        if (root instanceof HTMLTableElement && root.matches('.admin-data-table')) {
            enhanceTable(root);
        }

        root.querySelectorAll?.('.admin-data-table').forEach(enhanceTable);
    };

    const initialise = () => {
        enhanceAllTables();

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node instanceof Element) enhanceAllTables(node);
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, { once: true });
    } else {
        initialise();
    }
})();
