<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Choose Student Report Card" eyebrow="Academic Result Center">
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
        <x-filter-card title="Find a student report instantly" :action="$currentIndexRoute">
            <div class="report-quick-search-field flex flex-col gap-1.5 md:col-span-2">
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
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Academic term</label>
                <select name="term_id" class="theme-input" data-report-term-select>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" @selected($selectedTerm?->id === $term->id)>{{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="admin-report-actions flex flex-wrap items-center gap-2">
                <x-action-button variant="primary" type="submit" class="flex-1 justify-center py-2.5 !rounded-xl text-xs font-bold">
                    Search
                </x-action-button>
                <a
                    class="btn btn-secondary inline-flex items-center justify-center gap-2 flex-1 py-2.5 !rounded-xl text-xs font-bold"
                    data-report-open-link
                    hidden
                >
                    Open Report
                </a>
                <x-action-button variant="secondary" :href="$currentIndexRoute" class="flex-1 justify-center py-2.5 !rounded-xl text-xs font-bold">
                    Reset
                </x-action-button>
            </div>
        </x-filter-card>
    </div>

    <div class="mt-8 mb-8">
        <x-section-nav :items="$classNavItems" :active="$activeReportClassPage" />
    </div>

    @if ($activeReportClassPage === 'all' && $search === '')
        <x-dashboard-card title="{{ $pageTitle }}">
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
        <x-dashboard-card title="{{ $pageTitle }}">
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
                                <x-person-avatar :user="$student->user" />
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
                            <x-empty-state title="No students found" />
                        </td>
                    </tr>
                @endforelse
                </x-data-table>
            </div>
        </x-dashboard-card>
    @endif

    <script type="application/json" id="report-student-search-data">{!! json_encode($quickReportStudents, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</x-app-layout>
