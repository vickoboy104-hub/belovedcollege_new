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
        <x-page-header title="{{ $selectedStudent->user->fullName() }}" eyebrow="Result Center">
            <x-slot name="description">
                {{ $selectedStudent->admission_no }} • {{ $selectedStudent->schoolClass->display_name ?? 'Class pending' }} • {{ $selectedTerm?->name ?? 'No term selected' }} {{ $selectedTerm?->academicSession ? '- '.$selectedTerm->academicSession->name : '' }}
            </x-slot>
            <x-slot name="actions">
                <x-action-button variant="secondary" :href="route('admin.reports.index', $backRouteParameters)" icon="back">Back to Categories</x-action-button>
                <x-action-button variant="secondary" :href="route('admin.students.record', $selectedStudent)" target="_blank" icon="print">Print Dossier</x-action-button>
                @if ($report && $selectedTerm)
                    <x-action-button variant="secondary" :href="route('admin.reports.print', [...$printRouteParameters, 'layout' => 'classic'])" target="_blank" icon="print">Classic One-Page Result</x-action-button>
                    <x-action-button variant="accent" :href="route('admin.reports.print', $printRouteParameters)" target="_blank" icon="print">Print Report Card</x-action-button>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <!-- Swtich Academic Term Filter -->
    <div class="mb-8">
        <x-filter-card :action="route('admin.reports.show', ['student' => $selectedStudent, 'section' => $activeReportSection])" method="GET" title="Switch Academic Term" description="Keep your search term when switching categories">
            <select name="term_id" class="theme-input max-w-xs">
                @foreach ($terms as $term)
                    <option value="{{ $term->id }}" @selected($selectedTerm?->id === $term->id)>{{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ $search }}" placeholder="Keep search term..." class="theme-input max-w-sm" />
            @if ($classSlug)
                <input type="hidden" name="classSlug" value="{{ $classSlug }}">
            @endif
            <x-action-button type="submit" variant="primary" icon="search">Open Term</x-action-button>
            <x-action-button variant="secondary" :href="route('admin.reports.show', array_filter(['student' => $selectedStudent, 'section' => $activeReportSection, 'term_id' => $selectedTerm?->id, 'classSlug' => $classSlug], fn ($value) => $value !== null && $value !== ''))">Clear Search</x-action-button>
        </x-filter-card>
    </div>

    <!-- Section Main Content -->
    <div>
        @if ($report && $selectedTerm)
            @if ($activeReportSection === 'overview')
                <!-- Overview Header Info -->
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between mb-8">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ ucfirst($activeReportSection) }} Dashboard</p>
                        <h2 class="display-font mt-2 text-2xl font-bold text-slate-900">{{ $selectedStudent->user->fullName() }}</h2>
                        <p class="mt-2 text-sm text-slate-500 max-w-xl">Work on one result section at a time from the sidebar without losing the selected term.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 shrink-0">
                        <div class="card p-5 bg-white border border-[#c8d6ea] rounded-[18px] shadow-[0_10px_25px_rgba(15,23,42,0.08)] flex flex-col justify-center min-w-[140px]">
                            <div class="text-xs uppercase font-extrabold tracking-wider text-slate-400">Subjects</div>
                            <div class="display-font mt-2 text-3xl font-black text-slate-900">{{ $report->subject_count }}</div>
                        </div>
                        <div class="card p-5 bg-white border border-[#c8d6ea] rounded-[18px] shadow-[0_10px_25px_rgba(15,23,42,0.08)] flex flex-col justify-center min-w-[140px]">
                            <div class="text-xs uppercase font-extrabold tracking-wider text-slate-400">Average</div>
                            <div class="display-font mt-2 text-3xl font-black text-slate-900">{{ $report->average_score !== null ? number_format((float) $report->average_score, 2) . '%' : '--' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Snapshot and Quick Links -->
                <div class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
                    <x-dashboard-card title="Report Snapshot" icon="reports" accent="blue">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Overall Grade</div>
                                <div class="text-sm font-extrabold text-slate-800">{{ $report->overall_grade ?: 'Not available yet' }}</div>
                                <div class="mt-1 text-xs font-medium text-slate-500">Position: {{ $report->class_position ?: 'N/A' }}</div>
                            </div>
                            <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Attendance</div>
                                <div class="text-sm font-extrabold text-slate-800">{{ $report->days_present ?? 0 }} / {{ $report->days_school_open ?? 0 }} Days Present</div>
                                <div class="mt-1 text-xs font-medium text-slate-500">Absent: {{ $report->days_absent ?? 0 }}</div>
                            </div>
                            <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Approval</div>
                                <x-status-badge :status="$report->approved_at ? 'approved' : 'pending'" />
                                <div class="mt-1 text-[10px] font-semibold text-slate-450">{{ $report->approver?->fullName() ?? 'No approver yet' }}</div>
                            </div>
                            <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Student Access</div>
                                <div class="text-xs space-y-1.5 font-bold text-slate-600">
                                    <div class="flex items-center justify-between">
                                        <span>Portal:</span>
                                        <x-status-badge :status="$report->portal_enabled ? 'active' : 'inactive'" :label="$report->portal_enabled ? 'Enabled' : 'Disabled'" />
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>Checker:</span>
                                        <x-status-badge :status="$report->checker_enabled ? 'active' : 'inactive'" :label="$report->checker_enabled ? 'Enabled' : 'Disabled'" />
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4 sm:col-span-2">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 border-b border-slate-200 pb-1.5">Latest Remarks Summary</div>
                                <div class="space-y-3 text-xs leading-relaxed text-slate-600">
                                    <div><span class="font-extrabold text-slate-900 uppercase tracking-wide text-[10px]">Class Teacher:</span> {{ $report->class_teacher_remark ?: 'No remark yet.' }}</div>
                                    <div><span class="font-extrabold text-slate-900 uppercase tracking-wide text-[10px]">Guidance Counselor:</span> {{ $report->guidance_remark ?: 'No remark yet.' }}</div>
                                    <div><span class="font-extrabold text-slate-900 uppercase tracking-wide text-[10px]">Principal:</span> {{ $report->principal_remark ?: 'No remark yet.' }}</div>
                                    <div><span class="font-extrabold text-slate-900 uppercase tracking-wide text-[10px]">House Master / Mistress:</span> {{ $report->house_master_remark ?: 'No remark yet.' }}</div>
                                </div>
                            </div>
                        </div>
                    </x-dashboard-card>

                    <x-dashboard-card title="Quick Actions & Shortcuts" icon="bills" accent="gold">
                        <div class="space-y-3">
                            <a href="{{ route('admin.reports.show', ['student' => $selectedStudent, 'section' => 'scores', ...$reportState]) }}" class="flex items-start gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50/40 hover:bg-slate-50 hover:border-amber-300 transition duration-200 group">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0">
                                    <x-app-icon name="reports" class="w-4 h-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-sm text-slate-900 group-hover:text-blue-600 transition">Review Compiled Scores</h4>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Check per-subject exam, quiz, test, and overall percentages entered by class teachers.</p>
                                </div>
                            </a>

                            <a href="{{ route('admin.reports.show', ['student' => $selectedStudent, 'section' => 'remarks', ...$reportState]) }}" class="flex items-start gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50/40 hover:bg-slate-50 hover:border-amber-300 transition duration-200 group">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                                    <x-app-icon name="staff" class="w-4 h-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-sm text-slate-900 group-hover:text-emerald-600 transition">Update Remarks & Attendance</h4>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Input days present/absent, write official school comments, and rate character traits or practical skills.</p>
                                </div>
                            </a>

                            <a href="{{ route('admin.reports.show', ['student' => $selectedStudent, 'section' => 'publication', ...$reportState]) }}" class="flex items-start gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50/40 hover:bg-slate-50 hover:border-amber-300 transition duration-200 group">
                                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center shrink-0">
                                    <x-app-icon name="settings" class="w-4 h-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-sm text-slate-900 group-hover:text-purple-600 transition">Release Publication Settings</h4>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Control student visibility on the portal, enable scratch card checker PINs, and secure administrative sign-offs.</p>
                                </div>
                            </a>
                        </div>
                    </x-dashboard-card>
                </div>
            @elseif ($activeReportSection === 'scores')
                <!-- Scores Table -->
                <x-data-table :headers="['Subject', 'Quiz', 'Test', 'Project', 'Exam', 'Total %', 'Grade', 'Remark']">
                    @forelse ($subjectRows as $row)
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                <div>{{ $row['subject_name'] }}</div>
                                @if ($row['teachers'])
                                    <div class="mt-1 text-xs text-slate-400 font-medium">{{ $row['teachers'] }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-600">{{ number_format((float) $row['quiz_score'], 2) }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-600">{{ number_format((float) $row['test_score'], 2) }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-600">{{ number_format((float) $row['project_score'], 2) }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-600">{{ number_format((float) $row['exam_score'], 2) }}</td>
                            <td class="px-6 py-4 font-extrabold text-[#1d4ed8]">{{ number_format((float) $row['percentage'], 2) }}%</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-black bg-blue-50 text-blue-700 border border-blue-100">{{ $row['grade'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-500">{{ $row['remark'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8">
                                <x-empty-state title="No Compiled Scores" explanation="No subject score compilation entries have been recorded for this student in the selected term yet." />
                            </td>
                        </tr>
                    @endforelse
                </x-data-table>
            @elseif ($activeReportSection === 'remarks')
                <!-- Remarks Form -->
                <form method="POST" action="{{ route('admin.reports.update', [$selectedStudent, $selectedTerm]) }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="section" value="remarks">
                    @if ($classSlug)
                        <input type="hidden" name="classSlug" value="{{ $classSlug }}">
                    @endif
                    <input type="hidden" name="search" value="{{ $search }}">

                    <div class="grid gap-6 xl:grid-cols-2">
                        <!-- Left side: Attendance and Core remarks -->
                        <div class="space-y-6">
                            <x-dashboard-card title="Attendance & Core Parameters" icon="staff" accent="blue">
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Days Opened</label>
                                        <input name="days_school_open" type="number" min="0" max="365" value="{{ old('days_school_open', $report->days_school_open) }}" placeholder="0" class="theme-input w-full" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Days Present</label>
                                        <input name="days_present" type="number" min="0" max="365" value="{{ old('days_present', $report->days_present) }}" placeholder="0" class="theme-input w-full" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Days Absent</label>
                                        <input name="days_absent" type="number" min="0" max="365" value="{{ old('days_absent', $report->days_absent) }}" placeholder="0" class="theme-input w-full" />
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Next Term Begins</label>
                                    <input name="next_term_begins_on" type="date" value="{{ old('next_term_begins_on', optional($report->next_term_begins_on)->format('Y-m-d')) }}" class="theme-input w-full" />
                                </div>
                            </x-dashboard-card>

                            <x-dashboard-card title="Official Remarks Comments" icon="reports" accent="purple">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Class Teacher Remark</label>
                                        <input name="class_teacher_remark" value="{{ old('class_teacher_remark', $report->class_teacher_remark) }}" placeholder="Write class teacher comment..." class="theme-input w-full" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Guidance Counselor Remark</label>
                                        <input name="guidance_remark" value="{{ old('guidance_remark', $report->guidance_remark) }}" placeholder="Write guidance comment..." class="theme-input w-full" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Principal Remark</label>
                                        <input name="principal_remark" value="{{ old('principal_remark', $report->principal_remark) }}" placeholder="Write principal comment..." class="theme-input w-full" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">House Master / Mistress Remark</label>
                                        <input name="house_master_remark" value="{{ old('house_master_remark', $report->house_master_remark) }}" placeholder="Write house master comment..." class="theme-input w-full" />
                                    </div>
                                </div>
                            </x-dashboard-card>
                        </div>

                        <!-- Right side: Traits Ratings -->
                        <div class="space-y-6">
                            <x-dashboard-card title="Character Development Ratings" icon="student" accent="green">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    @foreach ($characterTraits as $key => $label)
                                        <div>
                                            <span class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">{{ $label }}</span>
                                            <select name="character_traits[{{ $key }}]" class="theme-input w-full">
                                                <option value="">Grade</option>
                                                @foreach ($skillGrades as $grade)
                                                    <option value="{{ $grade }}" @selected(old("character_traits.$key", $report->character_traits[$key] ?? null) === $grade)>{{ $grade }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </x-dashboard-card>

                            <x-dashboard-card title="Practical Skills Ratings" icon="school" accent="gold">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    @foreach ($practicalSkills as $key => $label)
                                        <div>
                                            <span class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">{{ $label }}</span>
                                            <select name="practical_skills[{{ $key }}]" class="theme-input w-full">
                                                <option value="">Grade</option>
                                                @foreach ($skillGrades as $grade)
                                                    <option value="{{ $grade }}" @selected(old("practical_skills.$key", $report->practical_skills[$key] ?? null) === $grade)>{{ $grade }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </x-dashboard-card>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                        <x-action-button type="submit" variant="success" icon="save" class="!px-6 !py-3 font-extrabold text-sm">Save Remarks & Ratings</x-action-button>
                    </div>
                </form>
            @elseif ($activeReportSection === 'publication')
                <!-- Publication Section -->
                <div class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
                    <x-dashboard-card title="Publication Settings" icon="settings" accent="blue">
                        <div class="space-y-4 mb-6">
                            <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Approval Status</div>
                                <div class="mt-2 text-sm font-extrabold text-slate-900">{{ $report->approved_at ? 'Officially Approved' : 'Awaiting Admin Approval Signature' }}</div>
                                <div class="mt-1 text-xs text-slate-500 font-medium leading-relaxed">{{ $report->approver?->fullName() ? $report->approver->fullName().' | '.$report->approved_at?->format('M j, Y g:i A') : 'Awaiting remarks and saving the report form will automatically sign the approval badge.' }}</div>
                            </div>
                            <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Student & Parent Visibility</div>
                                <div class="mt-2 text-xs space-y-2 font-bold text-slate-600">
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-1.5">
                                        <span>Portal Display:</span>
                                        <x-status-badge :status="$report->portal_enabled ? 'active' : 'inactive'" :label="$report->portal_enabled ? 'Released' : 'Withheld'" />
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>External Result PIN Checker:</span>
                                        <x-status-badge :status="$report->checker_enabled ? 'active' : 'inactive'" :label="$report->checker_enabled ? 'Enabled' : 'Disabled'" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.reports.publish', [$selectedStudent, $selectedTerm]) }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="section" value="publication">
                            @if ($classSlug)
                                <input type="hidden" name="classSlug" value="{{ $classSlug }}">
                            @endif
                            <input type="hidden" name="search" value="{{ $search }}">

                            <div class="space-y-3">
                                <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-xs font-bold text-slate-700 hover:bg-slate-50/50 cursor-pointer transition">
                                    <input type="checkbox" name="portal_enabled" value="1" @checked(old('portal_enabled', $report->portal_enabled)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <div>
                                        <p class="text-slate-800 font-extrabold">Allow Portal Viewing</p>
                                        <p class="text-slate-500 font-medium text-[10px] mt-0.5 leading-relaxed">Let student and their parents view this terminal report directly inside their portal accounts.</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-xs font-bold text-slate-700 hover:bg-slate-50/50 cursor-pointer transition">
                                    <input type="checkbox" name="checker_enabled" value="1" @checked(old('checker_enabled', $report->checker_enabled)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                    <div>
                                        <p class="text-slate-800 font-extrabold">Enable Scratch Card PIN Checker</p>
                                        <p class="text-slate-500 font-medium text-[10px] mt-0.5 leading-relaxed">Allow public access using a scratch card pin on the external landing result checker.</p>
                                    </div>
                                </label>
                            </div>

                            <div class="mt-4">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Checker PIN Value</label>
                                <input name="checker_pin" value="{{ old('checker_pin') }}" placeholder="Set or replace custom scratch card checker PIN..." class="theme-input w-full" />
                            </div>
                            <x-action-button type="submit" variant="success" class="w-full !py-3 font-extrabold text-sm" icon="save">Save Publication Settings</x-action-button>
                        </form>
                    </x-dashboard-card>

                    <x-dashboard-card title="System Scope Capabilities" icon="school" accent="gold">
                        <div class="space-y-4 text-xs leading-relaxed text-slate-600">
                            <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                                <h4 class="font-extrabold text-slate-800 uppercase tracking-wider text-[10px]">Printable Official Document</h4>
                                <p class="mt-1 font-medium leading-relaxed">Official PDF output containing modern security details, class signature blocks, academic emblems, and ratings indexes.</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                                <h4 class="font-extrabold text-slate-800 uppercase tracking-wider text-[10px]">Portal Distribution</h4>
                                <p class="mt-1 font-medium leading-relaxed">Allows parents to download terminal records immediately, streamlining end-of-term card collection.</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                                <h4 class="font-extrabold text-slate-800 uppercase tracking-wider text-[10px]">Dossier History</h4>
                                <p class="mt-1 font-medium leading-relaxed">Compiles cumulative average grades, historical term results, payment clearances, and behavioral ratings over their entire session lifecycle.</p>
                            </div>
                        </div>
                    </x-dashboard-card>
                </div>
            @endif
        @else
            <x-empty-state title="Academic Term Pending" explanation="Create at least one academic term before opening a report workspace." />
        @endif
    </div>
</x-app-layout>
