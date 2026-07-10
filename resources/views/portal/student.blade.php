<x-app-layout>
    @php
        $bankAccounts = collect(range(1, 3))
            ->map(fn (int $index) => [
                'bank' => $schoolSettings["bank_name_{$index}"] ?? null,
                'account_name' => $schoolSettings["account_name_{$index}"] ?? null,
                'account_number' => $schoolSettings["account_number_{$index}"] ?? null,
            ])
            ->filter(fn (array $account) => filled($account['bank']) || filled($account['account_name']) || filled($account['account_number']))
            ->values();
        $compactMoney = function (float $amount): string {
            $sign = $amount < 0 ? '-' : '';
            $absolute = abs($amount);

            return match (true) {
                $absolute >= 1000000000 => $sign.'₦'.number_format($absolute / 1000000000, 2).'B',
                $absolute >= 1000000 => $sign.'₦'.number_format($absolute / 1000000, 2).'M',
                $absolute >= 1000 => $sign.'₦'.number_format($absolute / 1000, 1).'K',
                default => $sign.'₦'.number_format($absolute, 0),
            };
        };
    @endphp

    <x-slot name="header">
        <x-page-header :title="'Welcome back, ' . $student->user->name . '!'" eyebrow="Student Portal">
            <x-slot name="description">
                {{ $student->admission_no }} &bull; {{ $student->schoolClass->name ?? 'Class pending assignment' }}
            </x-slot>
            <x-slot name="actions">
                <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                    @if ($children->isNotEmpty())
                        <form method="GET" action="{{ route('portal.index') }}" class="shrink-0">
                            <select name="student" onchange="this.form.submit()" class="theme-input text-xs font-black py-2 rounded-[10px] border-[#c8d6ea] bg-white text-[#071833] focus:ring-blue-500 shadow-sm cursor-pointer pr-8">
                                @foreach ($children as $child)
                                    <option value="{{ $child->id }}" @selected($child->id === $student->id)>Child: {{ $child->user->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                    <x-action-button :href="route('portal.record', $children->isNotEmpty() ? ['student' => $student->id] : [])" target="_blank" variant="secondary" icon="print">
                        Print Record Dossier
                    </x-action-button>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <!-- Student Portal Alpine Container -->
    <div 
        class="space-y-5" 
        x-data="{ 
            activeSection: new URLSearchParams(window.location.search).get('section') || 'overview' 
        }" 
        x-on:section-change.window="activeSection = $event.detail"
    >
        <!-- Student Profile Hero -->
        <x-profile-hero 
            :name="$student->user->fullName()" 
            role="STUDENT PORTAL" 
            :id="$student->admission_no" 
            :classDetails="$student->schoolClass->name ?? 'Pending Class'"
            status="Active"
        />

        <!-- 1. OVERVIEW SECTION -->
        <div x-show="activeSection === 'overview'" x-cloak class="space-y-8" x-transition:enter="transition ease-out duration-250">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <x-stat-card label="Lesson Notes" :value="$lessons->count()" icon="learning" accent="blue" />
                <x-stat-card label="Assignments" :value="$assignments->count()" icon="assignments" accent="orange">
                    {{ $submissions->count() }} submitted so far
                </x-stat-card>
                <x-stat-card label="Report Cards" :value="$publishedReports->count()" icon="reports" accent="green">
                    {{ $reportSummary->count() }} subject summaries
                </x-stat-card>
                <x-stat-card label="Fees Owed" :value="$compactMoney((float) $invoices->sum('balance'))" icon="finance" accent="red" />
            </div>

            <!-- Monolithic page layout view elements divided in clean grid layout -->
            <div class="grid gap-8 xl:grid-cols-[1.1fr,0.9fr]">
                <!-- Left Column: Lessons Preview -->
                <div class="space-y-6">
                    <x-dashboard-card title="Recent Lesson Notes" subtitle="Curriculum notes and reference resources published by your teachers." icon="learning" accent="blue">
                        <div class="space-y-4">
                            @forelse ($lessons->take(3) as $lesson)
                                <article class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-sm hover:border-blue-500 transition-all space-y-4">
                                    <div class="flex items-start justify-between gap-4 flex-wrap sm:flex-nowrap">
                                        <div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 border border-blue-100 text-blue-700">
                                                {{ $lesson->subject->name }}
                                            </span>
                                            <h4 class="display-font text-sm font-extrabold text-slate-900 leading-snug mt-2">
                                                {{ $lesson->title }}
                                            </h4>
                                            <p class="text-xs font-bold text-slate-500 mt-1 flex items-center gap-1">
                                                <x-app-icon name="profile" class="h-3.5 w-3.5 text-slate-400" />
                                                <span>Teacher: {{ $lesson->teacher->name }}</span>
                                            </p>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-150 px-2.5 py-1 rounded-[8px] shrink-0 self-start">
                                            {{ $lesson->created_at?->format('M j, Y') }}
                                        </span>
                                    </div>
                                    @if ($lesson->summary)
                                        <p class="text-xs font-semibold text-slate-550 leading-relaxed bg-slate-50 border border-slate-100 p-3 rounded-[12px]">
                                            {{ $lesson->summary }}
                                        </p>
                                    @endif
                                    <div class="pt-3 border-t border-slate-100 flex justify-end">
                                        <button @click="activeSection = 'lessons'; window.scrollTo({ top: 0, behavior: 'smooth' })" class="text-xs font-extrabold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                            <span>Read Note Details</span>
                                            <span>&rarr;</span>
                                        </button>
                                    </div>
                                </article>
                            @empty
                                <x-empty-state title="No lesson notes available yet" subtitle="When your course teachers publish lesson libraries, notes, and video attachments, they will appear here." icon="learning" />
                            @endforelse
                        </div>
                    </x-dashboard-card>
                </div>

                <!-- Right Column: Quick Performance -->
                <div class="space-y-6">
                    <x-dashboard-card title="Subject Performance" subtitle="Realtime average calculations based on cumulative academic grading entries." icon="reports" accent="blue">
                        <div class="space-y-4">
                            @forelse ($reportSummary->take(4) as $subject => $summary)
                                @php
                                    $average = max(0, min(100, (float) $summary['average']));
                                    $colorType = $average >= 70 ? 'green' : ($average >= 50 ? 'blue' : ($average >= 40 ? 'orange' : 'red'));
                                @endphp
                                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-3">
                                    <div class="flex items-start justify-between gap-3 flex-wrap">
                                        <div>
                                            <span class="text-xs font-extrabold uppercase tracking-wide text-slate-800 block">
                                                {{ $subject }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 block mt-0.5">
                                                {{ $summary['entries'] }} recorded grading {{ \Illuminate\Support\Str::plural('entry', $summary['entries']) }}
                                            </span>
                                        </div>
                                        <span class="inline-flex h-8 w-12 items-center justify-center rounded-[8px] text-xs font-black bg-blue-50 border border-blue-100 text-blue-700 shrink-0 shadow-sm">
                                            {{ number_format($average, 1) }}%
                                        </span>
                                    </div>
                                    <x-progress-bar :percentage="$average" label="" :color="$colorType" />
                                </div>
                            @empty
                                <x-empty-state title="No subject averages logged yet" subtitle="Cumulative performance statistics generate dynamically when test or exam scores load." icon="reports" />
                            @endforelse
                        </div>
                    </x-dashboard-card>
                </div>
            </div>
        </div>

        <!-- 2. OFFICIAL TERM REPORTS SECTION -->
        <div x-show="activeSection === 'reports'" x-cloak x-transition:enter="transition ease-out duration-250">
            <x-dashboard-card title="Official Term Reports" subtitle="Access officially approved report cards and academic summaries." icon="reports" accent="green">
                <div class="grid gap-6 md:grid-cols-2">
                    @forelse ($publishedReports as $publishedReport)
                        <div class="rounded-[18px] border border-slate-200 bg-slate-50/50 p-5 shadow-sm hover:border-emerald-500 transition-all flex flex-col justify-between gap-4">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <x-status-badge status="published" label="Published" />
                                    <span class="text-xs font-semibold text-slate-400">
                                        Released: {{ $publishedReport->published_at?->format('M j, Y') }}
                                    </span>
                                </div>
                                <h4 class="display-font text-lg font-bold text-slate-900 leading-snug">
                                    {{ $publishedReport->term->name }}
                                </h4>
                                <p class="text-xs font-bold text-slate-500">
                                    Session: {{ $publishedReport->term->academicSession->name ?? 'N/A' }}
                                </p>

                                <!-- Academic Metrics Grid -->
                                <div class="grid grid-cols-3 gap-3 bg-white p-3 rounded-[12px] border border-slate-200/60 shadow-sm text-center">
                                    <div>
                                        <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Average</div>
                                        <div class="display-font text-sm font-black text-slate-800 mt-0.5">
                                            {{ $publishedReport->average_score !== null ? number_format((float) $publishedReport->average_score, 2).'%' : 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="border-x border-slate-100">
                                        <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Grade</div>
                                        <div class="display-font text-sm font-black text-[#1d4ed8] mt-0.5">
                                            {{ $publishedReport->overall_grade ?: 'N/A' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Position</div>
                                        <div class="display-font text-sm font-black text-slate-800 mt-0.5">
                                            {{ $publishedReport->class_position ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Action buttons -->
                            <div class="flex items-center gap-2 border-t border-slate-100 pt-3 flex-wrap">
                                <x-action-button :href="route('portal.results.print', [$publishedReport->term]) . ($children->isNotEmpty() ? '?student='.$student->id : '')" target="_blank" variant="primary" icon="eye" class="flex-1 !py-2">
                                    Open Report Card
                                </x-action-button>
                                <x-action-button :href="route('portal.results.print', [$publishedReport->term]) . '?layout=classic' . ($children->isNotEmpty() ? '&student='.$student->id : '')" target="_blank" variant="secondary" icon="print" class="flex-1 !py-2">
                                    Classic Version
                                </x-action-button>
                            </div>
                        </div>
                    @empty
                        <div class="md:col-span-2">
                            <x-empty-state title="No Approved Report Cards" subtitle="When administrators approve and release academic report cards for this term, they will be listed here." icon="reports" />
                        </div>
                    @endforelse
                </div>
            </x-dashboard-card>
        </div>

        <!-- 3. LESSON NOTES SECTION -->
        <div x-show="activeSection === 'lessons'" x-cloak x-transition:enter="transition ease-out duration-250">
            <x-dashboard-card title="Lesson Notes Library" subtitle="Explore curriculum notes and reference resources published by your teachers." icon="learning" accent="blue">
                <div class="space-y-6">
                    @forelse ($lessons as $lesson)
                        <article class="rounded-[18px] border border-slate-200 bg-white p-6 shadow-sm hover:border-blue-500 hover:shadow-md transition-all space-y-4">
                            <div class="flex items-start justify-between gap-4 flex-wrap sm:flex-nowrap">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 border border-blue-100 text-blue-700">
                                        {{ $lesson->subject->name }}
                                    </span>
                                    <h4 class="display-font text-base font-extrabold text-slate-900 leading-snug mt-2">
                                        {{ $lesson->title }}
                                    </h4>
                                    <p class="text-xs font-bold text-slate-500 mt-1 flex items-center gap-1">
                                        <x-app-icon name="profile" class="h-3.5 w-3.5 text-slate-400" />
                                        <span>Teacher: {{ $lesson->teacher->name }}</span>
                                    </p>
                                </div>
                                <span class="text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-150 px-2.5 py-1 rounded-[8px] shrink-0 self-start">
                                    {{ $lesson->created_at?->format('M j, Y') }}
                                </span>
                            </div>

                            @if ($lesson->summary)
                                <p class="text-xs font-semibold text-slate-500 leading-relaxed bg-slate-50 border border-slate-100 p-3 rounded-[12px]">
                                    {{ $lesson->summary }}
                                </p>
                            @endif

                            <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed font-medium">
                                {{ $lesson->body }}
                            </p>

                            @if ($lesson->video_path)
                                <div class="max-w-md">
                                    <video controls preload="metadata" class="w-full rounded-[14px] border border-slate-350 bg-slate-950 shadow-md">
                                        <source src="{{ asset($lesson->video_path) }}">
                                    </video>
                                </div>
                            @elseif ($lesson->video_url)
                                <div>
                                    <x-action-button :href="$lesson->video_url" target="_blank" variant="secondary" icon="video" class="!py-1.5 !px-3">
                                        Watch Video Lesson
                                    </x-action-button>
                                </div>
                            @endif

                            @if (filled($lesson->note_images))
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($lesson->note_images as $image)
                                        <a href="{{ asset($image) }}" target="_blank" class="overflow-hidden rounded-xl border border-slate-200 block shadow-sm hover:opacity-90 transition">
                                            <img src="{{ asset($image) }}" alt="Lesson graphic aid" class="h-36 w-full object-cover" />
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if ($lesson->resource_link)
                                <div class="border-t border-slate-100 pt-3 flex">
                                    <a href="{{ $lesson->resource_link }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-extrabold text-blue-600 hover:text-blue-700">
                                        <span>Open Supporting Resource Link</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            @endif
                        </article>
                    @empty
                        <x-empty-state title="No lesson notes available yet" subtitle="When your course teachers publish lesson libraries, notes, and video attachments, they will appear here." icon="learning" />
                    @endforelse
                </div>
            </x-dashboard-card>
        </div>

        <!-- 4. ASSIGNMENTS SECTION -->
        <div x-show="activeSection === 'assignments'" x-cloak x-transition:enter="transition ease-out duration-250">
            <x-dashboard-card title="Assignments & Tasks" subtitle="Submit homework, review guidelines, and track grading statuses." icon="assignments" accent="orange">
                <div class="space-y-6">
                    @forelse ($assignments as $assignment)
                        @php
                            $hasSubmitted = $submissions->has($assignment->id);
                        @endphp
                        <article class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-sm space-y-4">
                            <div class="flex items-start justify-between gap-4 flex-wrap sm:flex-nowrap">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-50 border border-orange-100 text-orange-700">
                                        {{ $assignment->subject->name }}
                                    </span>
                                    <h4 class="display-font text-base font-extrabold text-slate-900 leading-snug mt-2">
                                        {{ $assignment->title }}
                                    </h4>
                                    <p class="text-xs font-bold text-slate-400 mt-1">
                                        Total Obtainable Marks: {{ number_format((float) $assignment->total_score, 2) }}
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-1.5 shrink-0 self-start">
                                    <x-status-badge :status="$hasSubmitted ? 'submitted' : 'pending'" />
                                    <span class="text-[10px] font-bold text-rose-600 uppercase">
                                        Due: {{ optional($assignment->due_date)->format('M j, g:i A') ?: 'Open' }}
                                    </span>
                                </div>
                            </div>

                            <p class="text-xs font-semibold text-slate-650 leading-relaxed whitespace-pre-line bg-slate-50 border border-slate-100 p-3 rounded-[12px]">
                                {{ $assignment->instructions }}
                            </p>

                            @if (filled($assignment->attachment_images))
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($assignment->attachment_images as $image)
                                        <a href="{{ asset($image) }}" target="_blank" class="overflow-hidden rounded-xl border border-slate-200 block shadow-sm hover:opacity-90 transition">
                                            <img src="{{ asset($image) }}" alt="Assignment graphic attachment" class="h-32 w-full object-cover" />
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if ($user->hasAnyRole(['student']))
                                <form method="POST" action="{{ route('portal.assignments.submit', $assignment) }}" class="space-y-3 pt-3 border-t border-slate-100">
                                    @csrf
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wide mb-1">Your Submission Note / Written Answer</label>
                                        <textarea name="content" rows="3" placeholder="{{ $hasSubmitted ? 'Update your submission content...' : 'Type your answer or reference notes here...' }}" class="theme-input w-full text-xs font-bold" required></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <x-action-button type="submit" :variant="$hasSubmitted ? 'secondary' : 'primary'" icon="save" class="!py-1.5 !px-3.5">
                                            {{ $hasSubmitted ? 'Update Submission' : 'Submit Assignment' }}
                                        </x-action-button>
                                    </div>
                                </form>
                            @endif
                        </article>
                    @empty
                        <x-empty-state title="No active assignments" subtitle="When your subject teachers assign homework or schoolwork tasks, they will appear here." icon="assignments" />
                    @endforelse
                </div>
            </x-dashboard-card>
        </div>

        <!-- 5. TEST GRADES SECTION -->
        <div x-show="activeSection === 'results'" x-cloak x-transition:enter="transition ease-out duration-250">
            <x-dashboard-card title="Assessment Results" subtitle="Recent score summaries and official grade logs." icon="reports" accent="purple">
                <div class="space-y-4">
                    @forelse ($results as $result)
                        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex items-center justify-between gap-3 hover:border-purple-300 transition">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#1d4ed8] block">
                                    {{ $result->assessment->subject->name }}
                                </span>
                                <h4 class="font-extrabold text-slate-800 text-sm leading-snug mt-0.5">
                                    {{ $result->assessment->title }}
                                </h4>
                                <p class="text-[10px] font-bold text-slate-400 mt-0.5">
                                    Term: {{ $result->assessment->term->name ?? 'N/A' }}
                                </p>
                            </div>
                            <span class="inline-flex h-9 w-16 items-center justify-center rounded-[10px] text-xs font-black bg-blue-50 border border-blue-100 text-[#1d4ed8] shrink-0 shadow-sm">
                                {{ $result->score }}{{ $result->grade ? ' - ' . $result->grade : '' }}
                            </span>
                        </div>
                    @empty
                        <x-empty-state title="No recorded results" subtitle="No grading results logged yet in this workspace." icon="reports" />
                    @endforelse
                </div>
            </x-dashboard-card>
        </div>

        <!-- 6. CBT EXAMS SECTION -->
        <div x-show="activeSection === 'cbt'" x-cloak x-transition:enter="transition ease-out duration-250">
            @if ($user->hasAnyRole(['student']))
                @php
                    $attemptsByAssessment = $cbtAttempts->keyBy('assessment_id');
                @endphp
                <x-dashboard-card title="CBT Exams and Tests" subtitle="Initiate active computer-based tests or review submission receipts." icon="portal" accent="purple">
                    <p class="text-xs text-slate-500 mb-5 leading-relaxed">
                        @if ($cbtEnabled)
                            Select an available assessment below to begin. Timers, question navigation blocks, and instructions will launch in focus mode.
                        @else
                            CBT examination modules are currently deactivated by the administrator.
                        @endif
                    </p>

                    @if ($cbtEnabled)
                        <div class="space-y-4">
                            @forelse ($cbtAssessments as $cbtAssessment)
                                @php
                                    $attempt = $attemptsByAssessment->get($cbtAssessment->id);
                                    $hasStarted = $attempt && $attempt->status === 'in_progress';
                                    $hasSubmitted = $attempt && $attempt->status !== 'in_progress';
                                @endphp
                                <article class="rounded-[18px] border border-slate-200 bg-slate-50/50 p-5 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-5 hover:border-purple-500 transition-all">
                                    <div class="flex-1 space-y-3.5">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 border border-purple-100 text-purple-700">
                                                {{ $cbtAssessment->subject->name }}
                                            </span>
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 bg-slate-100 border border-slate-200 rounded-full px-2.5 py-0.5">
                                                {{ $cbtAssessment->cbtQuestions->count() }} Questions &bull; {{ $cbtAssessment->cbt_duration_minutes }} Mins
                                            </span>
                                            @if ($attempt)
                                                <x-status-badge :status="$attempt->status === 'in_progress' ? 'pending' : 'submitted'" :label="$attempt->status === 'in_progress' ? 'In Progress' : 'Submitted'" />
                                            @else
                                                <x-status-badge status="open" label="Ready to Start" />
                                            @endif
                                        </div>

                                        <h4 class="display-font text-base font-extrabold text-slate-900 leading-snug">
                                            {{ $cbtAssessment->title }}
                                        </h4>

                                        <div class="flex flex-wrap gap-x-6 gap-y-2 text-xs font-semibold text-slate-500">
                                            <span>Teacher: {{ $cbtAssessment->teacher->fullName() }}</span>
                                            <span>&bull;</span>
                                            <span>Due: {{ $cbtAssessment->cbt_ends_at?->format('M j, Y g:i A') ?? 'Open' }}</span>
                                            @if ($attempt && ($attempt->status === 'graded' || ($attempt->status === 'submitted' && $cbtAssessment->cbt_show_results)))
                                                <span>&bull;</span>
                                                <span class="text-purple-700 font-bold">
                                                    CBT Grade: {{ number_format((float) $attempt->total_score, 2) }} / {{ number_format((float) $cbtAssessment->total_score, 2) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex items-center">
                                        @if (!$attempt || $hasStarted)
                                            <x-action-button :href="route('portal.cbt.show', $cbtAssessment)" variant="primary" icon="play" class="w-full md:w-auto">
                                                {{ $attempt ? 'Resume Exam' : 'Start Assessment' }}
                                            </x-action-button>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100 px-4 py-2.5 rounded-[12px] shadow-sm select-none">
                                                <x-app-icon name="check-circle" class="h-4 w-4" />
                                                <span>Exam Answer Receipt Logged</span>
                                            </span>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <x-empty-state title="No active CBT assessments available" subtitle="Your assigned curriculum classes do not have any pending computer-based exams currently active." icon="portal" />
                            @endforelse
                        </div>
                    @endif
                </x-dashboard-card>
            @else
                <x-dashboard-card title="CBT Exams and Tests" subtitle="Computer-based examination portal" icon="portal" accent="purple">
                    <x-empty-state title="CBT Not Available" subtitle="CBT exam dashboard access is reserved strictly for student portal roles." icon="portal" />
                </x-dashboard-card>
            @endif
        </div>

        <!-- 7. BILLING & FEES SECTION -->
        <div x-show="activeSection === 'billing'" x-cloak x-transition:enter="transition ease-out duration-250">
            <x-dashboard-card title="Financial Balance & Billing Ledger" subtitle="Review school fee items, generate web checkout links, and view local payment receipts." icon="finance" accent="red">
                <div class="grid gap-8 xl:grid-cols-[1.1fr,0.9fr]">
                    <!-- Payment Selection and Invoices list -->
                    <div class="space-y-6" x-data="{ selectedInvoices: [], totals: @js($invoices->mapWithKeys(fn ($invoice) => [$invoice->id => (float) $invoice->balance])) }">
                        
                        <!-- Alpine dynamic payment basket card -->
                        <div class="rounded-[18px] border border-slate-200 bg-slate-50/50 p-5 shadow-inner">
                            <div class="flex items-start justify-between gap-4 flex-wrap sm:flex-nowrap border-b border-slate-100 pb-4 mb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#fbbf24] border border-[#fbbf24]/20 text-[#071833]">
                                            Checkout Basket
                                        </span>
                                        <span class="text-xs font-bold text-slate-500" x-text="`${selectedInvoices.length} Item(s) Selected`"></span>
                                    </div>
                                    <h4 class="display-font text-base font-extrabold text-[#071833] mt-2 leading-snug">
                                        Combined Invoice Checkout
                                    </h4>
                                    <p class="text-[11px] font-semibold text-slate-500 mt-1 max-w-md">
                                        Check one or more billing rows below to calculate your final combined total before launching online portals.
                                    </p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Total Selection</div>
                                    <div class="display-font mt-1 text-2xl font-black text-[#071833]" x-text="`NGN ${selectedInvoices.reduce((sum, id) => sum + Number(totals[id] || 0), 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></div>
                                </div>
                            </div>

                            <form method="POST" class="flex flex-col sm:flex-row items-center gap-3">
                                @csrf
                                <template x-for="invoiceId in selectedInvoices" :key="invoiceId">
                                    <input type="hidden" name="invoice_ids[]" :value="invoiceId">
                                </template>
                                <button type="submit" formaction="{{ route('payments.selection.checkout', 'paystack') }}" x-bind:disabled="selectedInvoices.length === 0" class="btn inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[12px] font-bold text-xs uppercase tracking-wider transition-all duration-200 focus:outline-none active:scale-[0.98] w-full sm:w-auto bg-[#071833] text-white border border-[#071833] hover:bg-[#0b1f3a] focus:ring-[#071833] disabled:opacity-50 disabled:cursor-not-allowed">
                                    Pay Selected with Paystack
                                </button>
                                <button type="submit" formaction="{{ route('payments.selection.checkout', 'palmpay') }}" x-bind:disabled="selectedInvoices.length === 0" class="btn inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[12px] font-bold text-xs uppercase tracking-wider transition-all duration-200 focus:outline-none active:scale-[0.98] w-full sm:w-auto bg-white text-[#1d4ed8] border border-[#c8d6ea] hover:bg-slate-50 hover:border-[#b0c4de] focus:ring-[#1d4ed8] disabled:opacity-50 disabled:cursor-not-allowed">
                                    Pay Selected with PalmPay
                                </button>
                            </form>
                        </div>

                        <!-- Individual Invoices lists -->
                        <div class="space-y-4">
                            @foreach ($invoices as $invoice)
                                @php
                                    $invoiceTotal = (float) $invoice->amount_paid + (float) $invoice->balance;
                                    $paidPercent = $invoiceTotal > 0 ? max(0, min(100, ((float) $invoice->amount_paid / $invoiceTotal) * 100)) : 100;
                                    $isCleared = (float) $invoice->balance <= 0;
                                @endphp
                                <div class="rounded-[18px] border border-slate-200 bg-white p-5 shadow-sm space-y-4 hover:border-[#fbbf24] transition-all">
                                    <div class="flex items-start justify-between gap-4 flex-wrap">
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 border border-slate-200 text-slate-800">
                                                    {{ $invoice->feeItem->name ?? 'Custom Invoice Item' }}
                                                </span>
                                                <x-status-badge :status="$isCleared ? 'paid' : 'unpaid'" />
                                            </div>
                                            <h4 class="display-font text-base font-extrabold text-slate-800 leading-snug mt-2">
                                                {{ $invoice->invoice_no }}
                                            </h4>
                                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">
                                                Deadline: {{ optional($invoice->due_date)->format('M j, Y') ?: 'Open' }}
                                            </p>
                                        </div>
                                        <div class="text-left sm:text-right">
                                            <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Balance Owed</span>
                                            <div class="display-font text-lg font-black text-slate-900 mt-0.5">
                                                NGN {{ number_format((float) $invoice->balance, 2) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <x-progress-bar :percentage="$paidPercent" label="" :color="$isCleared ? 'green' : 'orange'" />
                                        <div class="flex items-center justify-between text-[10px] font-bold text-slate-400">
                                            <span>Billed Paid: NGN {{ number_format((float) $invoice->amount_paid, 2) }}</span>
                                            <span>{{ number_format($paidPercent, 0) }}% Cleared</span>
                                        </div>
                                    </div>

                                    @if (!$isCleared)
                                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3 flex-wrap">
                                            <label class="flex items-center gap-2 text-xs font-bold text-[#071833] cursor-pointer select-none">
                                                <input type="checkbox" value="{{ $invoice->id }}" x-model="selectedInvoices" class="rounded border-slate-350 text-blue-600 focus:ring-blue-500" />
                                                <span>Add to Selection Basket</span>
                                            </label>
                                            <div class="flex gap-2">
                                                <form method="POST" action="{{ route('payments.checkout', [$invoice, 'paystack']) }}">
                                                    @csrf
                                                    <x-action-button type="submit" variant="primary" icon="finance" class="!py-1.5 !px-3">
                                                        Paystack
                                                    </x-action-button>
                                                </form>
                                                <form method="POST" action="{{ route('payments.checkout', [$invoice, 'palmpay']) }}">
                                                    @csrf
                                                    <x-action-button type="submit" variant="secondary" icon="arrow-right" class="!py-1.5 !px-3">
                                                        PalmPay
                                                    </x-action-button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Right Side: Bank Accounts Transfer Details & Dynamic Receipts -->
                    <div class="space-y-6">
                        @if ($bankAccounts->isNotEmpty() || filled($schoolSettings['payment_instruction'] ?? null))
                            <div class="rounded-[18px] border border-slate-200 bg-slate-50/50 p-5 shadow-sm space-y-4">
                                <h4 class="display-font text-base font-extrabold text-[#071833] flex items-center gap-1.5">
                                    <x-app-icon name="finance-records" class="h-5 w-5 text-amber-500" />
                                    <span>Direct Transfer Instructions</span>
                                </h4>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    You can settle school bills by direct bank transfer using the official bank details specified below:
                                </p>

                                <div class="space-y-3">
                                    @foreach ($bankAccounts as $account)
                                        <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
                                            <span class="text-[9px] font-extrabold uppercase tracking-widest text-slate-400 block bg-slate-100/60 border border-slate-200/50 rounded-full px-2.5 py-0.5 w-max">
                                                {{ $account['bank'] ?: 'School Bank account' }}
                                            </span>
                                            <h5 class="font-bold text-slate-800 text-sm mt-2 leading-snug">
                                                {{ $account['account_name'] ?: 'Pending name verification' }}
                                            </h5>
                                            <div class="display-font font-black text-slate-900 text-base tracking-tight mt-1 flex items-center justify-between bg-slate-50 p-2 rounded-lg border border-slate-100">
                                                <span>{{ $account['account_number'] ?: 'N/A' }}</span>
                                                <span class="text-[10px] font-black text-[#1d4ed8] uppercase cursor-pointer select-none hover:underline" onclick="navigator.clipboard.writeText('{{ $account['account_number'] }}'); alert('Account Number Copied!')">
                                                    Copy
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if (filled($schoolSettings['payment_instruction'] ?? null))
                                    <p class="text-xs font-semibold text-slate-500 italic bg-white p-3 border border-slate-200 rounded-xl leading-relaxed">
                                        &ldquo;{{ $schoolSettings['payment_instruction'] }}&rdquo;
                                    </p>
                                @endif
                            </div>
                        @endif

                        <!-- Payment Receipt logs -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 block">Recent Receipts Log</h4>
                            @forelse ($payments as $payment)
                                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-3.5">
                                    <div class="flex items-start justify-between gap-3 flex-wrap">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 border border-blue-100 text-blue-700">
                                                    {{ $payment->provider->label() }}
                                                </span>
                                                <x-status-badge status="paid" label="Confirmed" />
                                            </div>
                                            <h5 class="display-font text-base font-black text-slate-800 tracking-tight mt-2">
                                                NGN {{ number_format((float) $payment->amount, 2) }}
                                            </h5>
                                            <p class="text-[10px] font-bold text-slate-400 mt-0.5 leading-none">
                                                Ref: {{ $payment->reference }}
                                            </p>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-400 self-start">
                                            {{ $payment->created_at?->format('M j, Y') }}
                                        </span>
                                    </div>

                                    <div class="flex gap-2 border-t border-slate-100 pt-3 flex-wrap">
                                        <x-action-button :href="route('payments.receipt', $payment)" target="_blank" variant="secondary" icon="eye" class="flex-1 !py-1.5 !px-2.5">
                                            Open Receipt
                                        </x-action-button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-xs text-slate-400 bg-slate-50 border border-dashed border-slate-350 p-4 rounded-xl text-center">
                                    No confirmed online payment transactions logged yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-dashboard-card>
        </div>

        <!-- 8. ATTENDANCE LOG SECTION -->
        <div x-show="activeSection === 'attendance'" x-cloak x-transition:enter="transition ease-out duration-250">
            <x-dashboard-card title="Attendance History Sheet" subtitle="View chronological attendance record logs and teacher comments." icon="clock" accent="purple">
                <x-data-table :headers="['Date', 'Status', 'Comment']" class="attendance-table">
                    @forelse ($attendance as $entry)
                        @php
                            $isPresent = strtolower($entry->status->label()) === 'present';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition duration-150">
                            <td class="font-bold text-slate-900 whitespace-nowrap">
                                {{ $entry->attendance_date->format('M j, Y') }}
                                <span class="ml-2 text-xs font-semibold text-slate-500">{{ $entry->attendance_date->format('l') }}</span>
                            </td>
                            <td class="whitespace-nowrap">
                                <x-status-badge :status="$isPresent ? 'present' : 'absent'" />
                            </td>
                            <td class="attendance-comment text-xs font-semibold text-slate-500 italic">
                                {!! $entry->note ? '&ldquo;' . e($entry->note) . '&rdquo;' : '<span class="text-slate-350 font-normal">No teacher comment registered.</span>' !!}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center">
                                <x-empty-state title="No attendance records logged" subtitle="Chronological attendance records generate dynamically when teachers take registry logs." icon="clock" />
                            </td>
                        </tr>
                    @endforelse
                </x-data-table>
            </x-dashboard-card>
        </div>
    </div>
</x-app-layout>
