<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Choose Student Report Card" eyebrow="Academic Result Center" description="Browse students by class category, then open a dedicated report workspace for one student at a time.">
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
    @endphp

    <div class="mb-8">
        <x-filter-card title="Search Filter Workspace" subtitle="Refine academic term or student lookup.">
            <form method="GET" action="{{ $currentIndexRoute }}" class="grid gap-4 md:grid-cols-4 items-end">
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Select Academic Term</label>
                    <select name="term_id" class="theme-input">
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" @selected($selectedTerm?->id === $term->id)>{{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1.5 md:col-span-2">
                    <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Search student name, ID or admission number</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="e.g. Daniel Adeyemi..." class="theme-input" />
                </div>
                <div class="flex items-center gap-2">
                    <x-action-button variant="primary" type="submit" class="flex-1 justify-center py-2.5 !rounded-xl text-xs font-bold">
                        Filter
                    </x-action-button>
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
        <x-dashboard-card title="{{ $pageTitle }}" subtitle="Pick a class category first so the report center only shows the students you want to work on.">
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
        <x-dashboard-card title="{{ $pageTitle }}" subtitle="Each student opens into a dedicated report workspace where you can review scores, update remarks, publish, and print.">
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
                        $reportPreview = [
                            'type' => 'student',
                            'title' => $student->user->fullName(),
                            'subtitle' => 'Report Workspace - '.($student->schoolClass->display_name ?? 'Unassigned Class'),
                            'avatar' => substr($student->user->first_name, 0, 1).substr($student->user->last_name, 0, 1),
                            'profileUrl' => $reportUrl,
                            'ctaLabel' => 'Open Report Workspace',
                            'fields' => [
                                ['label' => 'Admission No', 'value' => $student->admission_no ?: 'Not set'],
                                ['label' => 'Student ID', 'value' => $student->student_id_no ?: 'Not set'],
                                ['label' => 'Class', 'value' => $student->schoolClass->display_name ?? 'Unassigned Class'],
                                ['label' => 'Guardian', 'value' => $student->guardian_name ?: ($student->parent->name ?? 'No guardian')],
                                ['label' => 'Term', 'value' => $selectedTerm?->name ?? 'Term not selected'],
                                ['label' => 'Session', 'value' => $selectedTerm?->academicSession->name ?? 'No session'],
                            ],
                        ];
                    @endphp
                    <tr>
                        <td>
                            <div class="table-person">
                                <div class="table-avatar">{{ $reportPreview['avatar'] ?: 'ST' }}</div>
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
                            <button type="button" class="table-view-btn" data-preview='@json($reportPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state title="No students found" description="No students match this category or search filters. Try refining your filters above." />
                        </td>
                    </tr>
                @endforelse
                </x-data-table>
            </div>
        </x-dashboard-card>
    @endif

<x-entity-preview-modal />
</x-app-layout>
