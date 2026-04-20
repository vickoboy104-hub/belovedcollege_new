<x-app-layout>
    @php
        $reportState = array_filter([
            'student' => $selectedStudent,
            'term_id' => $selectedTerm?->id,
            'classSlug' => $classSlug,
            'search' => $search !== '' ? $search : null,
        ], fn ($value) => $value !== null && $value !== '');
        $reportNavItems = [
            ['key' => 'overview', 'label' => 'Overview', 'href' => route('admin.reports.show', ['section' => 'overview', ...$reportState])],
            ['key' => 'scores', 'label' => 'Scores', 'href' => route('admin.reports.show', ['section' => 'scores', ...$reportState])],
            ['key' => 'remarks', 'label' => 'Remarks', 'href' => route('admin.reports.show', ['section' => 'remarks', ...$reportState])],
            ['key' => 'publication', 'label' => 'Publication', 'href' => route('admin.reports.show', ['section' => 'publication', ...$reportState])],
        ];
        $printRouteParameters = array_filter([
            'student' => $selectedStudent,
            'term' => $selectedTerm,
            'section' => $activeReportSection,
            'classSlug' => $classSlug,
            'search' => $search !== '' ? $search : null,
        ], fn ($value) => $value !== null && $value !== '');
        $backRouteParameters = array_filter([
            'classSlug' => $classSlug,
            'term_id' => $selectedTerm?->id,
            'search' => $search !== '' ? $search : null,
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Result center</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $selectedStudent->user->fullName() }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $selectedStudent->admission_no }} | {{ $selectedStudent->schoolClass->display_name ?? 'Class pending' }} | {{ $selectedTerm?->name ?? 'No term selected' }} {{ $selectedTerm?->academicSession ? '- '.$selectedTerm->academicSession->name : '' }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.reports.index', $backRouteParameters) }}" class="theme-button-secondary">Back to student categories</a>
                <a href="{{ route('admin.students.record', $selectedStudent) }}" target="_blank" class="theme-button-secondary">Print student dossier</a>
                @if ($report && $selectedTerm)
                    <a href="{{ route('admin.reports.print', [...$printRouteParameters, 'layout' => 'classic']) }}" target="_blank" class="theme-button-secondary">Classic one-page result</a>
                    <a href="{{ route('admin.reports.print', $printRouteParameters) }}" target="_blank" class="theme-button">Print report card</a>
                @endif
            </div>
        </div>
    </x-slot>

    <section class="section-card">
        <form method="GET" action="{{ route('admin.reports.show', ['student' => $selectedStudent, 'section' => $activeReportSection]) }}" class="grid gap-4 lg:grid-cols-[0.9fr,1.1fr,auto,auto]">
            <select name="term_id" class="theme-input">
                @foreach ($terms as $term)
                    <option value="{{ $term->id }}" @selected($selectedTerm?->id === $term->id)>{{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ $search }}" placeholder="Keep your search when returning to the category page" class="theme-input" />
            @if ($classSlug)
                <input type="hidden" name="classSlug" value="{{ $classSlug }}">
            @endif
            <button type="submit" class="theme-button">Open term</button>
            <a href="{{ route('admin.reports.show', array_filter(['student' => $selectedStudent, 'section' => $activeReportSection, 'term_id' => $selectedTerm?->id, 'classSlug' => $classSlug], fn ($value) => $value !== null && $value !== '')) }}" class="theme-button-secondary text-center">Clear search</a>
        </form>
    </section>

    <div class="mt-8">
        <x-section-nav :items="$reportNavItems" :active="$activeReportSection" />
    </div>

    <section class="section-card mt-8">
        @if ($report && $selectedTerm)
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ ucfirst($activeReportSection) }} page</p>
                    <h2 class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $selectedStudent->user->fullName() }}</h2>
                    <p class="mt-2 text-sm text-slate-600">Work on one section at a time for this student. The navigation above switches pages without losing the selected term.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="stat-tile">
                        <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Subjects</div>
                        <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $report->subject_count }}</div>
                    </div>
                    <div class="stat-tile">
                        <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Average</div>
                        <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $report->average_score !== null ? number_format((float) $report->average_score, 2) . '%' : '--' }}</div>
                    </div>
                </div>
            </div>

            @if ($activeReportSection === 'overview')
                <div class="mt-8 grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
                    <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                        <h3 class="display-font text-xl font-bold text-slate-950">Report snapshot</h3>
                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Overall grade</div>
                                <div class="mt-2 font-semibold text-slate-900">{{ $report->overall_grade ?: 'Not available yet' }}</div>
                                <div class="mt-1 text-sm text-slate-600">Position: {{ $report->class_position ?: 'N/A' }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Attendance</div>
                                <div class="mt-2 font-semibold text-slate-900">{{ $report->days_present ?? 0 }} / {{ $report->days_school_open ?? 0 }} days present</div>
                                <div class="mt-1 text-sm text-slate-600">Absent: {{ $report->days_absent ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Approval</div>
                                <div class="mt-2 font-semibold text-slate-900">{{ $report->approved_at ? 'Approved' : 'Awaiting approval' }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $report->approver?->fullName() ?? 'No approver yet' }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Student access</div>
                                <div class="mt-2 text-sm text-slate-600">Portal: <span class="font-semibold text-slate-900">{{ $report->portal_enabled ? 'Enabled' : 'Disabled' }}</span></div>
                                <div class="text-sm text-slate-600">Checker PIN: <span class="font-semibold text-slate-900">{{ $report->checker_enabled ? 'Enabled' : 'Disabled' }}</span></div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 md:col-span-2">
                                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Latest remarks</div>
                                <div class="mt-3 space-y-3 text-sm text-slate-600">
                                    <div><span class="font-semibold text-slate-900">Class teacher:</span> {{ $report->class_teacher_remark ?: 'No remark yet.' }}</div>
                                    <div><span class="font-semibold text-slate-900">Guidance:</span> {{ $report->guidance_remark ?: 'No remark yet.' }}</div>
                                    <div><span class="font-semibold text-slate-900">Principal:</span> {{ $report->principal_remark ?: 'No remark yet.' }}</div>
                                    <div><span class="font-semibold text-slate-900">House:</span> {{ $report->house_master_remark ?: 'No remark yet.' }}</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                        <h3 class="display-font text-xl font-bold text-slate-950">Quick actions</h3>
                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            <a href="{{ route('admin.reports.show', ['student' => $selectedStudent, 'section' => 'scores', ...$reportState]) }}" class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-slate-300">Review compiled subject scores and teacher entries for this student.</a>
                            <a href="{{ route('admin.reports.show', ['student' => $selectedStudent, 'section' => 'remarks', ...$reportState]) }}" class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-slate-300">Update attendance, remarks, character development, and practical skills.</a>
                            <a href="{{ route('admin.reports.show', ['student' => $selectedStudent, 'section' => 'publication', ...$reportState]) }}" class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-slate-300">Approve the report and control portal or checker-PIN access.</a>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">Use the buttons above to print the student dossier or the result sheet at any time.</div>
                        </div>
                    </section>
                </div>
            @elseif ($activeReportSection === 'scores')
                <div class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Subject</th>
                                <th class="px-5 py-4">Quiz</th>
                                <th class="px-5 py-4">Test</th>
                                <th class="px-5 py-4">Project</th>
                                <th class="px-5 py-4">Exam</th>
                                <th class="px-5 py-4">Total %</th>
                                <th class="px-5 py-4">Grade</th>
                                <th class="px-5 py-4">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subjectRows as $row)
                                <tr class="border-t border-slate-200">
                                    <td class="px-5 py-4 text-slate-900">
                                        <div class="font-semibold">{{ $row['subject_name'] }}</div>
                                        @if ($row['teachers'])
                                            <div class="mt-1 text-xs text-slate-500">{{ $row['teachers'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['quiz_score'], 2) }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['test_score'], 2) }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['project_score'], 2) }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['exam_score'], 2) }}</td>
                                    <td class="px-5 py-4 font-semibold text-slate-900">{{ number_format((float) $row['percentage'], 2) }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $row['grade'] }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $row['remark'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-6 text-sm text-slate-500">No result entries have been compiled for this student in the selected term yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif ($activeReportSection === 'remarks')
                <form method="POST" action="{{ route('admin.reports.update', [$selectedStudent, $selectedTerm]) }}" class="mt-8 space-y-6">
                    @csrf
                    <input type="hidden" name="section" value="remarks">
                    @if ($classSlug)
                        <input type="hidden" name="classSlug" value="{{ $classSlug }}">
                    @endif
                    <input type="hidden" name="search" value="{{ $search }}">

                    <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                        <h3 class="display-font text-xl font-bold text-slate-950">Attendance and remarks</h3>
                        <div class="mt-5 grid gap-4 md:grid-cols-3">
                            <input name="days_school_open" type="number" min="0" max="365" value="{{ old('days_school_open', $report->days_school_open) }}" placeholder="Days school opened" class="theme-input" />
                            <input name="days_present" type="number" min="0" max="365" value="{{ old('days_present', $report->days_present) }}" placeholder="Days present" class="theme-input" />
                            <input name="days_absent" type="number" min="0" max="365" value="{{ old('days_absent', $report->days_absent) }}" placeholder="Days absent" class="theme-input" />
                        </div>
                        <input name="next_term_begins_on" type="date" value="{{ old('next_term_begins_on', optional($report->next_term_begins_on)->format('Y-m-d')) }}" class="theme-input mt-4 w-full" />
                        <div class="mt-4 space-y-4">
                            <input name="class_teacher_remark" value="{{ old('class_teacher_remark', $report->class_teacher_remark) }}" placeholder="Class teacher remark" class="theme-input w-full" />
                            <input name="guidance_remark" value="{{ old('guidance_remark', $report->guidance_remark) }}" placeholder="Guidance counsellor remark" class="theme-input w-full" />
                            <input name="principal_remark" value="{{ old('principal_remark', $report->principal_remark) }}" placeholder="Principal remark" class="theme-input w-full" />
                            <input name="house_master_remark" value="{{ old('house_master_remark', $report->house_master_remark) }}" placeholder="House master / mistress remark" class="theme-input w-full" />
                        </div>
                    </section>

                    <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                        <h3 class="display-font text-xl font-bold text-slate-950">Character development</h3>
                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            @foreach ($characterTraits as $key => $label)
                                <label class="text-sm text-slate-600">
                                    <span class="mb-2 block font-semibold text-slate-900">{{ $label }}</span>
                                    <select name="character_traits[{{ $key }}]" class="theme-input w-full">
                                        <option value="">Grade</option>
                                        @foreach ($skillGrades as $grade)
                                            <option value="{{ $grade }}" @selected(old("character_traits.$key", $report->character_traits[$key] ?? null) === $grade)>{{ $grade }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                        <h3 class="display-font text-xl font-bold text-slate-950">Practical skills</h3>
                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            @foreach ($practicalSkills as $key => $label)
                                <label class="text-sm text-slate-600">
                                    <span class="mb-2 block font-semibold text-slate-900">{{ $label }}</span>
                                    <select name="practical_skills[{{ $key }}]" class="theme-input w-full">
                                        <option value="">Grade</option>
                                        @foreach ($skillGrades as $grade)
                                            <option value="{{ $grade }}" @selected(old("practical_skills.$key", $report->practical_skills[$key] ?? null) === $grade)>{{ $grade }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <button type="submit" class="theme-button">Save report remarks and ratings</button>
                </form>
            @elseif ($activeReportSection === 'publication')
                <div class="mt-8 grid gap-8 xl:grid-cols-[1.1fr,0.9fr]">
                    <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                        <h3 class="display-font text-xl font-bold text-slate-950">Publication status</h3>
                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Approval</div>
                                <div class="mt-2 font-semibold text-slate-900">{{ $report->approved_at ? 'Approved' : 'Awaiting approval' }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $report->approver?->fullName() ? $report->approver->fullName().' | '.$report->approved_at?->format('M j, Y g:i A') : 'Save the report form on the remarks page to approve the record.' }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Student access</div>
                                <div class="mt-2 text-sm text-slate-600">Portal: <span class="font-semibold text-slate-900">{{ $report->portal_enabled ? 'Enabled' : 'Disabled' }}</span></div>
                                <div class="text-sm text-slate-600">Checker PIN: <span class="font-semibold text-slate-900">{{ $report->checker_enabled ? 'Enabled' : 'Disabled' }}</span></div>
                                <div class="mt-1 text-xs text-slate-500">{{ $report->published_at ? 'Published '.$report->published_at->format('M j, Y g:i A') : 'Not yet released to students.' }}</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.reports.publish', [$selectedStudent, $selectedTerm]) }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="section" value="publication">
                            @if ($classSlug)
                                <input type="hidden" name="classSlug" value="{{ $classSlug }}">
                            @endif
                            <input type="hidden" name="search" value="{{ $search }}">

                            <label class="flex items-center gap-3 rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-700">
                                <input type="checkbox" name="portal_enabled" value="1" @checked(old('portal_enabled', $report->portal_enabled)) class="rounded border-slate-300" />
                                Allow this student to open the report in the portal
                            </label>
                            <label class="flex items-center gap-3 rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-700">
                                <input type="checkbox" name="checker_enabled" value="1" @checked(old('checker_enabled', $report->checker_enabled)) class="rounded border-slate-300" />
                                Allow checker PIN access for this report
                            </label>
                            <input name="checker_pin" value="{{ old('checker_pin') }}" placeholder="Set or replace checker PIN" class="theme-input w-full" />
                            <button type="submit" class="theme-button w-full">Save publication settings</button>
                        </form>
                    </section>

                    <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                        <h3 class="display-font text-xl font-bold text-slate-950">What this report now supports</h3>
                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">Official printable report card with school identity, student profile, per-subject breakdown, remarks, skills, and signatures.</div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">Admin-controlled release to the student portal and optional public checker-PIN access.</div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">Printable student dossier with promotion history, finance summary, attendance snapshot, and report history.</div>
                        </div>
                    </section>
                </div>
            @endif
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-sm text-slate-500">Create at least one academic term before opening a report workspace.</div>
        @endif
    </section>
</x-app-layout>
