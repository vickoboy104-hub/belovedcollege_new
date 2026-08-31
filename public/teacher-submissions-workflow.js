(() => {
    const path = window.location.pathname.replace(/\/+$/, '');
    if (path !== '/teacher/learning/submissions') return;

    document.body.classList.add('teacher-submissions-workflow');

    const panel = Array.from(document.querySelectorAll('.section-card-panel')).find((candidate) => {
        const title = candidate.querySelector('.section-title');
        return title && title.textContent.trim() === 'Assignment Submissions';
    });

    if (!panel) return;
    panel.classList.add('teacher-submissions-panel');

    const table = panel.querySelector('table');
    if (!table || !table.tBodies.length) return;

    const rows = Array.from(table.tBodies[0].rows).filter((row) => row.cells.length >= 6);
    if (!rows.length) return;

    const records = rows.map((row, index) => {
        const cells = row.cells;
        const person = cells[0].querySelector('.table-person-text');
        const avatar = cells[0].querySelector('.table-avatar');
        const student = person?.querySelector('strong')?.textContent.trim() || cells[0].textContent.trim();
        const className = person?.querySelector('span')?.textContent.trim() || '';
        const assignment = cells[1].textContent.trim();
        const answer = cells[2].textContent.trim();
        const scoreText = cells[3].textContent.trim();
        const statusText = cells[4].textContent.trim().toLowerCase();
        const status = statusText.includes('unmarked') || scoreText.toLowerCase().includes('pending') ? 'unmarked' : 'graded';
        const actionsCell = cells[5];
        const viewButton = actionsCell.querySelector('.table-view-btn');
        const gradeForm = actionsCell.querySelector('form');

        return {
            index,
            row,
            student,
            className,
            assignment,
            answer,
            avatarText: avatar?.textContent.trim() || student.split(/\s+/).slice(0, 2).map((part) => part[0] || '').join('').toUpperCase(),
            status,
            viewButton,
            gradeForm,
            searchable: `${student} ${className} ${assignment} ${answer}`.toLowerCase(),
        };
    });

    // Put work that still needs attention first.
    records.sort((a, b) => {
        if (a.status !== b.status) return a.status === 'unmarked' ? -1 : 1;
        return a.index - b.index;
    });

    const shell = document.createElement('div');
    shell.className = 'submission-review-shell';

    const toolbar = document.createElement('div');
    toolbar.className = 'submission-review-toolbar';

    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'submission-review-search';
    search.placeholder = 'Search student, class, assignment or answer...';
    search.setAttribute('aria-label', 'Search assignment submissions');

    const filterGroup = document.createElement('div');
    filterGroup.className = 'submission-filter-group';

    const counts = {
        all: records.length,
        unmarked: records.filter((record) => record.status === 'unmarked').length,
        graded: records.filter((record) => record.status === 'graded').length,
    };

    let activeFilter = counts.unmarked > 0 ? 'unmarked' : 'all';

    const filterButtons = {};
    [
        ['all', `All (${counts.all})`],
        ['unmarked', `Needs grading (${counts.unmarked})`],
        ['graded', `Graded (${counts.graded})`],
    ].forEach(([key, label]) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'submission-filter-btn';
        button.dataset.filter = key;
        button.textContent = label;
        if (key === activeFilter) button.classList.add('is-active');
        filterButtons[key] = button;
        filterGroup.appendChild(button);
    });

    toolbar.append(search, filterGroup);

    const summary = document.createElement('div');
    summary.className = 'submission-review-summary';
    summary.innerHTML = `<span><strong>${counts.unmarked}</strong> need grading</span><span>•</span><span><strong>${counts.graded}</strong> graded</span><span>•</span><span>Unmarked work is shown first.</span>`;

    const list = document.createElement('div');
    list.className = 'submission-review-list';

    records.forEach((record) => {
        const card = document.createElement('article');
        card.className = 'submission-review-card';
        card.dataset.status = record.status;
        card.dataset.search = record.searchable;

        const main = document.createElement('div');
        main.className = 'submission-review-main';

        const personRow = document.createElement('div');
        personRow.className = 'submission-review-person-row';

        const personWrap = document.createElement('div');
        personWrap.className = 'submission-review-person';

        const avatar = document.createElement('div');
        avatar.className = 'submission-review-avatar';
        avatar.textContent = record.avatarText;

        const nameWrap = document.createElement('div');
        nameWrap.style.minWidth = '0';

        const name = document.createElement('div');
        name.className = 'submission-review-name';
        name.textContent = record.student;

        const classLine = document.createElement('div');
        classLine.className = 'submission-review-class';
        classLine.textContent = record.className || 'Class not available';

        nameWrap.append(name, classLine);
        personWrap.append(avatar, nameWrap);

        const status = document.createElement('span');
        status.className = `submission-status-chip ${record.status}`;
        status.textContent = record.status === 'unmarked' ? 'Needs grading' : 'Graded';

        personRow.append(personWrap, status);

        const assignment = document.createElement('div');
        assignment.className = 'submission-review-assignment';
        assignment.textContent = record.assignment;

        const answer = document.createElement('div');
        answer.className = 'submission-answer-preview';
        answer.textContent = record.answer || 'No written answer preview available.';

        const metaActions = document.createElement('div');
        metaActions.className = 'submission-review-meta-actions';
        if (record.viewButton) {
            record.viewButton.textContent = 'View full submission';
            metaActions.appendChild(record.viewButton);
        }

        main.append(personRow, assignment, answer, metaActions);

        const gradeBox = document.createElement('div');
        gradeBox.className = 'submission-grade-box';

        const gradeLabel = document.createElement('div');
        gradeLabel.className = 'submission-grade-label';
        gradeLabel.textContent = record.status === 'unmarked' ? 'Grade now' : 'Update grade';

        if (record.gradeForm) {
            const scoreInput = record.gradeForm.querySelector('input[name="score"]');
            const feedbackInput = record.gradeForm.querySelector('input[name="feedback"]');
            const saveButton = record.gradeForm.querySelector('button[type="submit"]');

            if (scoreInput) {
                scoreInput.placeholder = 'Score';
                scoreInput.setAttribute('aria-label', `Score for ${record.student}`);
            }
            if (feedbackInput) {
                feedbackInput.placeholder = 'Short feedback (optional)';
                feedbackInput.setAttribute('aria-label', `Feedback for ${record.student}`);
            }
            if (saveButton) {
                saveButton.textContent = record.status === 'unmarked' ? 'Save grade' : 'Update';
            }

            gradeBox.append(gradeLabel, record.gradeForm);
        } else {
            gradeBox.appendChild(gradeLabel);
        }

        card.append(main, gradeBox);
        list.appendChild(card);
    });

    const emptyFilter = document.createElement('div');
    emptyFilter.className = 'submission-empty-filter';
    emptyFilter.hidden = true;
    emptyFilter.textContent = 'No submissions match this search or filter.';

    shell.append(toolbar, summary, list, emptyFilter);

    const originalWrapper = table.closest('.overflow-x-auto') || table.parentElement;
    if (originalWrapper) originalWrapper.classList.add('teacher-submissions-original-table');

    const panelContent = panel.querySelector(':scope > .text-sm') || panel;
    panelContent.insertBefore(shell, originalWrapper || panelContent.firstChild);

    const applyFilters = () => {
        const query = search.value.trim().toLowerCase();
        let visible = 0;

        list.querySelectorAll('.submission-review-card').forEach((card) => {
            const matchesFilter = activeFilter === 'all' || card.dataset.status === activeFilter;
            const matchesSearch = !query || card.dataset.search.includes(query);
            const show = matchesFilter && matchesSearch;
            card.hidden = !show;
            if (show) visible += 1;
        });

        emptyFilter.hidden = visible !== 0;
    };

    Object.entries(filterButtons).forEach(([key, button]) => {
        button.addEventListener('click', () => {
            activeFilter = key;
            Object.values(filterButtons).forEach((item) => item.classList.remove('is-active'));
            button.classList.add('is-active');
            applyFilters();
        });
    });

    search.addEventListener('input', applyFilters);
    applyFilters();
})();
