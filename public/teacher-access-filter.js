(() => {
    const start = async () => {
        const endpoint = document.querySelector('meta[name="teacher-access-map-url"]')?.content;
        if (!endpoint) return;

        let payload;
        try {
            const response = await fetch(endpoint, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });
            if (!response.ok) return;
            payload = await response.json();
        } catch (error) {
            return;
        }

        const classSubjectMap = Object.fromEntries(
            Object.entries(payload.class_subject_map || {}).map(([classId, subjectIds]) => [
                String(classId),
                (subjectIds || []).map(String),
            ]),
        );
        const classes = (payload.classes || []).map((item) => ({
            value: String(item.id),
            label: item.label,
        }));
        const subjects = (payload.subjects || []).map((item) => ({
            value: String(item.id),
            label: item.label,
        }));

        const replaceOptions = (select, items, placeholder, selectedValue) => {
            const current = String(selectedValue || select.value || '');
            select.replaceChildren();

            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = placeholder;
            select.append(emptyOption);

            for (const item of items) {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.label;
                option.selected = item.value === current;
                select.append(option);
            }

            if (![...select.options].some((option) => option.value === current)) {
                select.value = '';
            }
        };

        for (const form of document.querySelectorAll('main form')) {
            const classSelect = form.querySelector('select[name="school_class_id"]');
            const subjectSelect = form.querySelector('select[name="subject_id"]');
            if (!classSelect || !subjectSelect) continue;

            const initialClass = classSelect.value;
            const initialSubject = subjectSelect.value;

            const refreshSubjects = () => {
                const classId = classSelect.value;
                const allowedIds = classId
                    ? (classSubjectMap[classId] || [])
                    : [...new Set(Object.values(classSubjectMap).flat())];
                const allowedSubjects = subjects.filter((subject) => allowedIds.includes(subject.value));
                replaceOptions(subjectSelect, allowedSubjects, 'Choose Subject', subjectSelect.value || initialSubject);
            };

            const refreshClasses = () => {
                const subjectId = subjectSelect.value;
                const allowedClasses = subjectId
                    ? classes.filter((schoolClass) => (classSubjectMap[schoolClass.value] || []).includes(subjectId))
                    : classes;
                replaceOptions(classSelect, allowedClasses, 'Choose Class', classSelect.value || initialClass);
            };

            classSelect.addEventListener('change', refreshSubjects);
            subjectSelect.addEventListener('change', refreshClasses);
            refreshClasses();
            refreshSubjects();
        }

        if (payload.has_access) return;

        const main = document.querySelector('.app-main');
        if (main && !main.querySelector('[data-no-teacher-access]')) {
            const notice = document.createElement('div');
            notice.dataset.noTeacherAccess = 'true';
            notice.className = 'mb-5 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900';
            notice.textContent = 'No subject and class permission has been assigned to this teacher account. Contact an administrator or principal.';
            main.prepend(notice);
        }

        document.querySelectorAll('main form').forEach((form) => {
            if (!form.querySelector('[name="subject_id"], [name="school_class_id"], [name="assessment_id"]')) return;
            form.querySelectorAll('input, select, textarea, button').forEach((control) => {
                control.disabled = true;
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
