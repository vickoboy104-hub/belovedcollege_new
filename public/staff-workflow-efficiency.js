(() => {
    'use strict';

    const ready = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    };

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    const splitLast = (text) => {
        const parts = String(text || '').split(' - ').map((part) => part.trim()).filter(Boolean);
        return {
            head: parts.length > 1 ? parts.slice(0, -1).join(' - ') : (parts[0] || ''),
            tail: parts.length > 1 ? parts[parts.length - 1] : '',
        };
    };

    const element = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
    };

    const optionClone = (source, fallbackLabel) => {
        const select = element('select', 'theme-input workflow-control');
        Array.from(source?.options || []).forEach((option) => {
            const clone = document.createElement('option');
            clone.value = option.value;
            clone.textContent = option.textContent;
            clone.selected = option.selected;
            select.appendChild(clone);
        });
        if (!select.options.length && fallbackLabel) {
            const option = document.createElement('option');
            option.textContent = fallbackLabel;
            option.value = '';
            select.appendChild(option);
        }
        return select;
    };

    const updateSectionHeading = (form, title, description) => {
        const section = form.closest('.form-section');
        if (!section) return null;
        const heading = section.querySelector('.form-section-header .section-title');
        const copy = section.querySelector('.form-section-header .section-description');
        if (heading) heading.textContent = title;
        if (copy) copy.textContent = description;
        return section;
    };

    const postMany = async ({ action, rows, payloadFor, detectError, onProgress }) => {
        const token = csrfToken();
        let completed = 0;
        const failed = [];
        const batchSize = 6;

        for (let start = 0; start < rows.length; start += batchSize) {
            const batch = rows.slice(start, start + batchSize);
            const settled = await Promise.allSettled(batch.map(async (row) => {
                const payload = payloadFor(row);
                const body = new FormData();
                body.append('_token', token);
                Object.entries(payload).forEach(([key, value]) => {
                    body.append(key, value ?? '');
                });

                const response = await fetch(action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    redirect: 'follow',
                    headers: {
                        'Accept': 'application/json, text/html;q=0.9',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token,
                    },
                    body,
                });

                const text = await response.text();
                let errorMessage = '';

                if (!response.ok) {
                    try {
                        const json = JSON.parse(text);
                        errorMessage = json.message || Object.values(json.errors || {}).flat()[0] || `HTTP ${response.status}`;
                    } catch (_) {
                        errorMessage = `HTTP ${response.status}`;
                    }
                } else if (detectError) {
                    errorMessage = detectError(text) || '';
                }

                if (errorMessage) throw new Error(errorMessage);
                return row;
            }));

            settled.forEach((result, index) => {
                completed += 1;
                if (result.status === 'rejected') {
                    failed.push({ row: batch[index], message: result.reason?.message || 'Could not save this row.' });
                }
                onProgress?.(completed, rows.length, failed.length);
            });
        }

        return { failed, saved: rows.length - failed.length };
    };

    const buildStatusSelect = (value = 'present') => {
        const select = element('select', 'workflow-status-select');
        [
            ['present', 'Present'],
            ['late', 'Late'],
            ['absent', 'Absent'],
            ['excused', 'Excused'],
        ].forEach(([status, label]) => {
            const option = document.createElement('option');
            option.value = status;
            option.textContent = label;
            option.selected = status === value;
            select.appendChild(option);
        });
        return select;
    };

    const enhanceAttendance = () => {
        const form = Array.from(document.forms).find((candidate) => candidate.action.includes('/teacher/attendance'));
        if (!form || form.dataset.bulkEnhanced === '1') return false;

        const classSource = form.querySelector('select[name="school_class_id"]');
        const studentSource = form.querySelector('select[name="student_id"]');
        const dateSource = form.querySelector('input[name="attendance_date"]');
        if (!classSource || !studentSource || !dateSource) return false;

        const students = Array.from(studentSource.options)
            .filter((option) => option.value)
            .map((option) => {
                const parsed = splitLast(option.textContent);
                return {
                    id: option.value,
                    name: parsed.head || option.textContent.trim(),
                    className: parsed.tail,
                };
            });

        if (!students.length) return false;

        form.dataset.bulkEnhanced = '1';
        const section = updateSectionHeading(
            form,
            'Class Attendance Register',
            'Choose a class and date, mark the whole roster on one screen, then save once.'
        );
        if (!section) return false;

        form.hidden = true;

        const panel = element('div', 'workflow-bulk-panel');
        panel.setAttribute('data-workflow', 'attendance');

        const toolbar = element('div', 'workflow-toolbar');
        const classWrap = element('label', 'workflow-field');
        classWrap.append(element('span', 'workflow-label', 'Class Group'));
        const classSelect = optionClone(classSource, 'Choose class');
        classWrap.append(classSelect);

        const dateWrap = element('label', 'workflow-field');
        dateWrap.append(element('span', 'workflow-label', 'Date'));
        const dateInput = dateSource.cloneNode(true);
        dateInput.className = 'theme-input workflow-control';
        dateWrap.append(dateInput);

        const searchWrap = element('label', 'workflow-field workflow-search-field');
        searchWrap.append(element('span', 'workflow-label', 'Find student'));
        const searchInput = element('input', 'theme-input workflow-control');
        searchInput.type = 'search';
        searchInput.placeholder = 'Search name...';
        searchWrap.append(searchInput);

        toolbar.append(classWrap, dateWrap, searchWrap);
        panel.append(toolbar);

        const quickBar = element('div', 'workflow-quickbar');
        const helper = element('div', 'workflow-helper', 'Tip: mark everyone Present first, then change only the exceptions.');
        const actions = element('div', 'workflow-quick-actions');
        const allPresent = element('button', 'workflow-secondary-button', 'Mark all Present');
        allPresent.type = 'button';
        const allAbsent = element('button', 'workflow-secondary-button', 'Mark all Absent');
        allAbsent.type = 'button';
        actions.append(allPresent, allAbsent);
        quickBar.append(helper, actions);
        panel.append(quickBar);

        const summary = element('div', 'workflow-summary');
        panel.append(summary);

        const roster = element('div', 'workflow-roster');
        panel.append(roster);

        const footer = element('div', 'workflow-sticky-footer');
        const status = element('div', 'workflow-save-status', 'Ready to mark attendance.');
        const saveButton = element('button', 'workflow-primary-button', 'Save Class Attendance');
        saveButton.type = 'button';
        footer.append(status, saveButton);
        panel.append(footer);

        section.append(panel);

        let currentRows = [];

        const recalculate = () => {
            const visible = currentRows.filter((row) => !row.node.hidden);
            const counts = { present: 0, late: 0, absent: 0, excused: 0 };
            currentRows.forEach((row) => { counts[row.status.value] = (counts[row.status.value] || 0) + 1; });
            summary.textContent = `${currentRows.length} students • ${counts.present} present • ${counts.late} late • ${counts.absent} absent • ${counts.excused} excused${visible.length !== currentRows.length ? ` • ${visible.length} shown` : ''}`;
        };

        const render = () => {
            roster.replaceChildren();
            const className = classSelect.options[classSelect.selectedIndex]?.textContent?.trim() || '';
            const query = searchInput.value.trim().toLowerCase();
            const filtered = students.filter((student) => student.className === className);
            currentRows = filtered.map((student, index) => {
                const row = element('div', 'workflow-roster-row');
                row.dataset.studentId = student.id;

                const identity = element('div', 'workflow-student-cell');
                identity.append(element('span', 'workflow-row-number', String(index + 1)));
                const identityCopy = element('div', 'workflow-student-copy');
                identityCopy.append(element('strong', 'workflow-student-name', student.name));
                identityCopy.append(element('span', 'workflow-student-meta', className));
                identity.append(identityCopy);

                const statusSelect = buildStatusSelect('present');
                const noteInput = element('input', 'theme-input workflow-note-input');
                noteInput.type = 'text';
                noteInput.maxLength = 500;
                noteInput.placeholder = 'Optional note';

                row.append(identity, statusSelect, noteInput);
                roster.append(row);

                statusSelect.addEventListener('change', recalculate);
                const visible = !query || student.name.toLowerCase().includes(query);
                row.hidden = !visible;

                return { node: row, student, status: statusSelect, note: noteInput };
            });

            if (!currentRows.length) {
                roster.append(element('div', 'workflow-empty', 'No students were found for this class.'));
            }
            recalculate();
        };

        classSelect.addEventListener('change', render);
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            currentRows.forEach((row) => {
                row.node.hidden = Boolean(query) && !row.student.name.toLowerCase().includes(query);
            });
            recalculate();
        });
        allPresent.addEventListener('click', () => {
            currentRows.forEach((row) => { row.status.value = 'present'; });
            recalculate();
        });
        allAbsent.addEventListener('click', () => {
            currentRows.forEach((row) => { row.status.value = 'absent'; });
            recalculate();
        });

        saveButton.addEventListener('click', async () => {
            if (!currentRows.length) return;
            if (!dateInput.value) {
                status.textContent = 'Choose an attendance date first.';
                status.className = 'workflow-save-status is-error';
                dateInput.focus();
                return;
            }

            saveButton.disabled = true;
            status.className = 'workflow-save-status';
            status.textContent = `Saving 0 of ${currentRows.length}...`;

            const result = await postMany({
                action: form.action,
                rows: currentRows,
                payloadFor: (row) => ({
                    school_class_id: classSelect.value,
                    student_id: row.student.id,
                    attendance_date: dateInput.value,
                    status: row.status.value,
                    note: row.note.value.trim(),
                }),
                detectError: (text) => {
                    const lower = text.toLowerCase();
                    if (lower.includes('does not belong to the selected class')) return 'Student/class mismatch.';
                    return '';
                },
                onProgress: (done, total, failed) => {
                    status.textContent = `Saving ${done} of ${total}${failed ? ` • ${failed} failed` : ''}...`;
                },
            });

            saveButton.disabled = false;
            if (result.failed.length) {
                status.className = 'workflow-save-status is-error';
                status.textContent = `Saved ${result.saved} of ${currentRows.length}. ${result.failed.length} row(s) need retry.`;
            } else {
                status.className = 'workflow-save-status is-success';
                status.textContent = `Attendance saved for all ${result.saved} students.`;
            }
        });

        render();
        return true;
    };

    const enhanceResults = () => {
        const form = Array.from(document.forms).find((candidate) => candidate.action.includes('/teacher/results'));
        if (!form || form.dataset.bulkEnhanced === '1') return false;

        const assessmentSource = form.querySelector('select[name="assessment_id"]');
        const studentSource = form.querySelector('select[name="student_id"]');
        if (!assessmentSource || !studentSource) return false;

        const students = Array.from(studentSource.options)
            .filter((option) => option.value)
            .map((option) => {
                const parsed = splitLast(option.textContent);
                return { id: option.value, label: parsed.head || option.textContent.trim(), className: parsed.tail };
            });
        if (!students.length) return false;

        form.dataset.bulkEnhanced = '1';
        const section = updateSectionHeading(
            form,
            'Class Score Sheet',
            'Choose one assessment, enter scores for the whole class, and save all entered rows together.'
        );
        if (!section) return false;
        form.hidden = true;

        const panel = element('div', 'workflow-bulk-panel');
        panel.setAttribute('data-workflow', 'results');

        const toolbar = element('div', 'workflow-toolbar workflow-toolbar-results');
        const assessmentWrap = element('label', 'workflow-field workflow-field-wide');
        assessmentWrap.append(element('span', 'workflow-label', 'Assessment'));
        const assessmentSelect = optionClone(assessmentSource, 'Choose assessment');
        assessmentWrap.append(assessmentSelect);

        const searchWrap = element('label', 'workflow-field');
        searchWrap.append(element('span', 'workflow-label', 'Find student'));
        const searchInput = element('input', 'theme-input workflow-control');
        searchInput.type = 'search';
        searchInput.placeholder = 'Search name or admission no...';
        searchWrap.append(searchInput);
        toolbar.append(assessmentWrap, searchWrap);
        panel.append(toolbar);

        const helper = element('div', 'workflow-helper workflow-helper-block', 'Enter only the rows you want to save. Blank rows are left unchanged. Use Tab or Enter to move quickly through scores.');
        panel.append(helper);

        const summary = element('div', 'workflow-summary');
        panel.append(summary);
        const roster = element('div', 'workflow-score-roster');
        panel.append(roster);

        const footer = element('div', 'workflow-sticky-footer');
        const status = element('div', 'workflow-save-status', 'Choose an assessment to begin.');
        const saveButton = element('button', 'workflow-primary-button', 'Save Entered Scores');
        saveButton.type = 'button';
        footer.append(status, saveButton);
        panel.append(footer);
        section.append(panel);

        let currentRows = [];

        const selectedClassName = () => {
            const option = assessmentSelect.options[assessmentSelect.selectedIndex];
            if (!option?.value) return '';
            return splitLast(option.textContent).tail;
        };

        const updateSummary = () => {
            const entered = currentRows.filter((row) => row.score.value !== '').length;
            const visible = currentRows.filter((row) => !row.node.hidden).length;
            summary.textContent = `${currentRows.length} students • ${entered} score${entered === 1 ? '' : 's'} entered${visible !== currentRows.length ? ` • ${visible} shown` : ''}`;
            status.textContent = entered ? `${entered} row${entered === 1 ? '' : 's'} ready to save.` : 'Enter at least one score.';
            status.className = 'workflow-save-status';
        };

        const render = () => {
            roster.replaceChildren();
            const className = selectedClassName();
            const query = searchInput.value.trim().toLowerCase();
            const filtered = className ? students.filter((student) => student.className === className) : [];

            currentRows = filtered.map((student, index) => {
                const row = element('div', 'workflow-score-row');
                const identity = element('div', 'workflow-student-cell');
                identity.append(element('span', 'workflow-row-number', String(index + 1)));
                const copy = element('div', 'workflow-student-copy');
                copy.append(element('strong', 'workflow-student-name', student.label));
                copy.append(element('span', 'workflow-student-meta', className));
                identity.append(copy);

                const score = element('input', 'theme-input workflow-score-input');
                score.type = 'number';
                score.step = '0.01';
                score.min = '0';
                score.placeholder = 'Score';
                const grade = element('input', 'theme-input workflow-grade-input');
                grade.type = 'text';
                grade.maxLength = 10;
                grade.placeholder = 'Grade';
                const remark = element('input', 'theme-input workflow-remark-input');
                remark.type = 'text';
                remark.maxLength = 255;
                remark.placeholder = 'Optional remark';

                row.append(identity, score, grade, remark);
                roster.append(row);
                const visible = !query || student.label.toLowerCase().includes(query);
                row.hidden = !visible;

                score.addEventListener('input', updateSummary);
                score.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter') return;
                    event.preventDefault();
                    const indexInRows = currentRows.findIndex((candidate) => candidate.score === score);
                    currentRows[indexInRows + 1]?.score.focus();
                });
                return { node: row, student, score, grade, remark };
            });

            if (!assessmentSelect.value) {
                roster.append(element('div', 'workflow-empty', 'Select an assessment to load its class roster.'));
            } else if (!currentRows.length) {
                roster.append(element('div', 'workflow-empty', 'No students were found for the class attached to this assessment.'));
            }
            updateSummary();
        };

        assessmentSelect.addEventListener('change', render);
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            currentRows.forEach((row) => {
                row.node.hidden = Boolean(query) && !row.student.label.toLowerCase().includes(query);
            });
            updateSummary();
        });

        saveButton.addEventListener('click', async () => {
            const rowsToSave = currentRows.filter((row) => row.score.value !== '');
            if (!assessmentSelect.value) {
                status.className = 'workflow-save-status is-error';
                status.textContent = 'Choose an assessment first.';
                assessmentSelect.focus();
                return;
            }
            if (!rowsToSave.length) {
                status.className = 'workflow-save-status is-error';
                status.textContent = 'Enter at least one student score.';
                return;
            }

            saveButton.disabled = true;
            status.className = 'workflow-save-status';
            status.textContent = `Saving 0 of ${rowsToSave.length}...`;

            const result = await postMany({
                action: form.action,
                rows: rowsToSave,
                payloadFor: (row) => ({
                    assessment_id: assessmentSelect.value,
                    student_id: row.student.id,
                    score: row.score.value,
                    grade: row.grade.value.trim(),
                    remark: row.remark.value.trim(),
                }),
                detectError: (text) => {
                    const lower = text.toLowerCase();
                    if (lower.includes('cannot be greater than the assessment total score')) return 'Score is above the assessment total.';
                    if (lower.includes('does not belong to the selected class assessment')) return 'Student/assessment class mismatch.';
                    return '';
                },
                onProgress: (done, total, failed) => {
                    status.textContent = `Saving ${done} of ${total}${failed ? ` • ${failed} failed` : ''}...`;
                },
            });

            saveButton.disabled = false;
            if (result.failed.length) {
                status.className = 'workflow-save-status is-error';
                status.textContent = `Saved ${result.saved} of ${rowsToSave.length}. Check ${result.failed.length} failed row(s).`;
            } else {
                status.className = 'workflow-save-status is-success';
                status.textContent = `Saved all ${result.saved} entered scores.`;
            }
        });

        render();
        return true;
    };

    const compactTeacherWorkspace = () => {
        if (!document.body.classList.contains('route-teacher-learning')) return;
        document.querySelectorAll('.form-section').forEach((section) => section.classList.add('workflow-compact-form-section'));
    };

    ready(() => {
        if (!document.body.classList.contains('route-teacher-learning')) return;
        compactTeacherWorkspace();
        enhanceAttendance();
        enhanceResults();
    });
})();
