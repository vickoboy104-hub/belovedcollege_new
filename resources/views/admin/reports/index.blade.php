<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Choose Student Report Card" eyebrow="Academic Result Center" description="Search by student name, student ID, or admission number and open the report workspace immediately.">
            <x-slot name="actions">
                <x-action-button variant="accent" :href="route('reports.checker')" target="_blank" class="!rounded-xl text-xs font-bold py-2.5 shadow-sm">
                    Open Public Result Checker
                </x-action-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    @php
        $currentIndexRoute = $activeReportClassPage === 'all'
            ? route('admin.reports.index')
            : route('admin.reports.index', ['classSlug' => $activeReportClassPage]);

        $quickReportStudents = $students->map(function ($student) use ($selectedTerm, $activeReportClassPage, $search) {
            $reportUrl = route('admin.reports.show', array_filter([
                'student' => $student,
                'section' => 'overview',
                'term_id' => $selectedTerm?->id,
                'classSlug' => $activeReportClassPage !== 'all' ? $activeReportClassPage : null,
                'search' => $search !== '' ? $search : null,
            ], fn ($value) => $value !== null && $value !== ''));

            $studentName = $student->user->fullName();
            $studentId = $student->student_id_no ?: 'No student ID';
            $admissionNo = $student->admission_no ?: 'No admission number';

            return [
                'label' => $studentName.' — '.$studentId.' — '.$admissionNo,
                'name' => $studentName,
                'studentId' => $student->student_id_no ?: '',
                'admissionNo' => $student->admission_no ?: '',
                'className' => $student->schoolClass->display_name ?? 'Unassigned Class',
                'url' => $reportUrl,
            ];
        })->values();
    @endphp

    <div class="mb-8">
        <x-filter-card title="Find a student report instantly" subtitle="Start typing a name, student ID, or admission number. Select a suggestion or press Enter to open a single matching report immediately.">
            <form id="report-student-search-form" method="GET" action="{{ $currentIndexRoute }}" class="grid gap-4 md:grid-cols-4 items-end">
                <div class="flex flex-col gap-1.5 md:col-span-2">
                    <label for="report-student-search" class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Student quick search</label>
                    <input
                        id="report-student-search"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Type student name, student ID, or admission number"
                        class="theme-input"
                        list="report-student-options"
                        autocomplete="off"
                        autofocus
                        data-report-search-input
                    />
                    <datalist id="report-student-options">
                        @foreach ($quickReportStudents as $studentOption)
                            <option value="{{ $studentOption['label'] }}">{{ $studentOption['className'] }}</option>
                        @endforeach
                    </datalist>
                    <p class="text-[11px] font-semibold text-slate-500">Choosing an exact suggestion opens that student’s report without another search step.</p>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Academic term</label>
                    <select name="term_id" class="theme-input" data-report-term-select>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" @selected($selectedTerm?->id === $term->id)>{{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-action-button variant="primary" type="submit" class="flex-1 justify-center py-2.5 !rounded-xl text-xs font-bold">
                        Search
                    </x-action-button>
                    <a
                        href="#"
                        class="theme-button-secondary hidden flex-1 justify-center py-2.5 !rounded-xl text-xs font-bold"
                        data-report-open-link
                    >
                        Open Report
                    </a>
                    <x-action-button variant="secondary" :href="$currentIndexRoute" class="flex-1 justify-center py-2.5 !rounded-xl text-xs font-bold">
                        Reset
                    </x-action-button>
                </div>
            </form>
        </x-filter-card>
    </div>

    <div class="mt-8 mb-8">
        <x-section-nav :items="$classNavItems" :active="$activeReportClassPage" />
    </div>

    @if ($activeReportClassPage === 'all' && $search === '')
        <x-dashboard-card title="{{ $pageTitle }}" subtitle="Search above for the fastest access, or open a class category to browse its students.">
            <x-slot name="actions">
                <div class="text-right">
                    <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Available Categories</div>
                    <div class="display-font text-2xl font-bold text-slate-900 mt-0.5">{{ $classDirectory->count() }}</div>
                </div>
            </x-slot>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3 mt-6">
                @foreach ($classDirectory as $classItem)
                    <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-5 hover:border-[#fbbf24] hover:shadow-lg hover:-translate-y-1 transition duration-200 flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shadow-sm mb-4">
                                <x-app-icon name="classes" class="w-5 h-5" />
                            </div>
                            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Category</span>
                            <h3 class="display-font mt-1.5 text-xl font-bold text-slate-900 leading-snug">{{ $classItem['name'] }}</h3>
                            <p class="text-xs text-slate-500 mt-2 font-semibold">{{ $classItem['count'] }} student{{ $classItem['count'] === 1 ? '' : 's' }}</p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end">
                            <x-action-button variant="primary" :href="$classItem['href']" class="!px-4 !py-2 !rounded-xl text-xs font-bold">Open Category</x-action-button>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-dashboard-card>
    @else
        <x-dashboard-card title="{{ $pageTitle }}" subtitle="Open a student directly to review scores, update remarks, publish, and print the selected term report.">
            <x-slot name="actions">
                <div class="text-right">
                    <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Students Shown</div>
                    <div class="display-font text-2xl font-bold text-slate-900 mt-0.5">{{ $students->count() }}</div>
                </div>
            </x-slot>

            <div class="mt-6">
                <x-data-table :headers="['Student', 'Admission No', 'Student ID', 'Class', 'Guardian', 'Term', 'Actions']">
                @forelse ($students as $student)
                    @php
                        $reportUrl = route('admin.reports.show', array_filter([
                            'student' => $student,
                            'section' => 'overview',
                            'term_id' => $selectedTerm?->id,
                            'classSlug' => $activeReportClassPage !== 'all' ? $activeReportClassPage : null,
                            'search' => $search !== '' ? $search : null,
                        ], fn ($value) => $value !== null && $value !== ''));
                    @endphp
                    <tr>
                        <td>
                            <div class="table-person">
                                <div class="table-avatar">{{ substr($student->user->first_name, 0, 1) }}{{ substr($student->user->last_name, 0, 1) }}</div>
                                <div class="table-person-text">
                                    <strong>{{ $student->user->fullName() }}</strong>
                                    <span>{{ $student->user->email ?: 'No email' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="font-mono font-bold">{{ $student->admission_no ?: 'Not set' }}</td>
                        <td>{{ $student->student_id_no ?: 'Not set' }}</td>
                        <td>{{ $student->schoolClass->display_name ?? 'Unassigned Class' }}</td>
                        <td><span class="table-text-clip">{{ $student->guardian_name ?: ($student->parent->name ?? 'No guardian') }}</span></td>
                        <td>{{ $selectedTerm?->name ?? 'Term' }}</td>
                        <td>
                            <a href="{{ $reportUrl }}" class="table-view-btn whitespace-nowrap">Open Report</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state title="No students found" description="No students match this category or search filters. Try a name, student ID, or admission number above." />
                        </td>
                    </tr>
                @endforelse
                </x-data-table>
            </div>
        </x-dashboard-card>
    @endif

    <script type="application/json" id="report-student-search-data">{!! json_encode($quickReportStudents, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script>
        (() => {
            const input = document.querySelector('[data-report-search-input]');
            const openLink = document.querySelector('[data-report-open-link]');
            const form = document.getElementById('report-student-search-form');
            const dataElement = document.getElementById('report-student-search-data');
            const termSelect = document.querySelector('[data-report-term-select]');

            if (!input || !openLink || !form || !dataElement) return;

            let records = [];
            try {
                records = JSON.parse(dataElement.textContent || '[]');
            } catch (error) {
                records = [];
            }

            const normalize = (value) => String(value || '')
                .toLowerCase()
                .replace(/[—–-]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            const matchingRecords = (value) => {
                const query = normalize(value);
                if (!query) return [];
                const words = query.split(' ').filter(Boolean);

                return records.filter((record) => {
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

            const exactRecord = (value) => {
                const query = normalize(value);
                if (!query) return null;

                return records.find((record) => [
                    record.label,
                    record.name,
                    record.studentId,
                    record.admissionNo,
                ].some((candidate) => normalize(candidate) === query)) || null;
            };

            const resolvedRecord = () => {
                const exact = exactRecord(input.value);
                if (exact) return exact;

                const matches = matchingRecords(input.value);
                return matches.length === 1 ? matches[0] : null;
            };

            const updateOpenLink = () => {
                const record = resolvedRecord();
                openLink.classList.toggle('hidden', !record);
                openLink.href = record ? record.url : '#';
                return record;
            };

            input.addEventListener('input', updateOpenLink);
            input.addEventListener('change', () => {
                const record = updateOpenLink();
                if (record && exactRecord(input.value)) {
                    window.location.assign(record.url);
                }
            });

            input.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') return;
                const record = resolvedRecord();
                if (!record) return;

                event.preventDefault();
                window.location.assign(record.url);
            });

            termSelect?.addEventListener('change', () => form.requestSubmit());
            updateOpenLink();
        })();
    </script>
</x-app-layout>
