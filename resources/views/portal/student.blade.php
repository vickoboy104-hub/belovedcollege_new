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
            <div class="grid gap-5 xl:grid-cols-[1.1fr,0.9fr]">
                <!-- Left Column: Lessons Preview -->
                <div class="space-y-4">
                    <x-dashboard-card title="Recent Lesson Notes" subtitle="Curriculum notes and reference resources published by your teachers." icon="learning" accent="blue">
                        <div class="space-y-3">
                            @forelse ($lessons->take(2) as $lesson)
                                <article class="rounded-[14px] border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-500 transition-all space-y-3">
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
                                        <p class="truncate text-xs font-semibold text-slate-550 bg-slate-50 border border-slate-100 px-3 py-2 rounded-[10px]">
                                            {{ $lesson->summary }}
                                        </p>
                                    @endif
                                    <div class="pt-2 border-t border-slate-100 flex justify-end">
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
                <div class="space-y-4">
                    <x-dashboard-card title="Subject Performance" subtitle="Realtime average calculations based on cumulative academic grading entries." icon="reports" accent="blue">
                        <div class="space-y-3">
                            @forelse ($reportSummary->take(3) as $subject => $summary)
                                @php
                                    $average = max(0, min(100, (float) $summary['average']));
                                    $colorType = $average >= 70 ? 'green' : ($average >= 50 ? 'blue' : ($average >= 40 ? 'orange' : 'red'));
                                @endphp
                                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm space-y-2">
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
                            <div class="flex items-center gap-2 border-t border-slate-100 pt-2 flex-wrap">
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
                <div class="space-y-3">
                    @forelse ($lessons as $lesson)
                        <article class="rounded-[18px] border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-500 hover:shadow-md transition-all space-y-4">
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
                                <p class="text-xs font-semibold text-slate-500 leading-relaxed bg-slate-50 border border-slate-100 px-3 py-2 rounded-[10px]">
                                    {{ $lesson->summary }}
                                </p>
                            @endif

                            <p class="text-sm text-slate-700 whitespace-pre-line leading-6 font-medium">
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

                            <div class="border-t border-slate-100 pt-2 flex">
                                @if ($lesson->resource_link)
                                    <a href="{{ $lesson->resource_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-extrabold text-blue-600 hover:text-blue-700">
                                        <span>Open Supporting Resource Link</span>
                                        <span>&rarr;</span>
                                    </a>
                                @else
                                    <button
                                        type="button"
                                        onclick="window.alert('No resources available yet. Your teacher has not uploaded any learning material.')"
                                        class="inline-flex items-center gap-1 text-xs font-extrabold text-slate-500 hover:text-slate-700"
                                    >
                                        <span>Open Supporting Resource Link</span>
                                        <span>&rarr;</span>
                                    </button>
                                @endif
                            </div>
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
                <div class="space-y-3">
                    @forelse ($assignments as $assignment)
                        @php
                            $hasSubmitted = $submissions->has($assignment->id);
                        @endphp
                        <article class="rounded-[18px] border border-slate-200 bg-white p-4 shadow-sm space-y-4">
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
            <div
                class="space-y-5"
                x-data="{
                    selectedInvoices: [],
                    totals: @js($invoices->filter(fn ($invoice) => (float) $invoice->balance > 0)->mapWithKeys(fn ($invoice) => [$invoice->id => (float) $invoice->balance])),
                    paymentStep: 1,
                    selectedGateway: 'paystack',
                    get selectedTotal() {
                        return this.selectedInvoices.reduce((sum, id) => sum + Number(this.totals[id] || 0), 0);
                    }
                }"
            >
                <!-- Compact balance summary -->
                <section class="overflow-hidden rounded-[22px] border border-[#d8e2ef] bg-white shadow-sm">
                    <div class="finance-contrast-banner grid gap-3 bg-[#071833] px-5 py-4 text-white sm:grid-cols-[1fr,auto] sm:items-center sm:px-6">
                        <div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-amber-300">Financial account</p>
                            <h2 class="finance-banner-title display-font mt-1 text-lg font-black">Make a payment</h2>
                            <p class="finance-banner-summary mt-1 text-xs font-semibold">Select only the school fees you want to pay now.</p>
                        </div>
                        <div class="rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 sm:text-right">
                            <p class="finance-banner-label text-[10px] font-extrabold uppercase tracking-wider">Outstanding balance</p>
                            <p class="finance-banner-value display-font mt-0.5 text-xl font-black">NGN {{ number_format((float) $invoices->sum('balance'), 2) }}</p>
                        </div>
                    </div>

                    @if ($errors->has('payment'))
                        <div class="border-b border-red-200 bg-red-50 px-5 py-3 text-sm font-bold text-red-800 sm:px-7">
                            {{ $errors->first('payment') }}
                        </div>
                    @endif

                    <form method="POST" class="p-4 sm:p-5">
                        @csrf
                        <template x-for="invoiceId in selectedInvoices" :key="invoiceId">
                            <input type="hidden" name="invoice_ids[]" :value="invoiceId">
                        </template>

                        <!-- Step indicator -->
                        <div class="mb-4 flex items-center gap-3" aria-label="Payment progress">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black"
                                      :class="paymentStep === 1 ? 'bg-blue-600 text-white' : 'bg-emerald-600 text-white'">1</span>
                                <span class="text-xs font-extrabold text-[#071833]">Select fees</span>
                            </div>
                            <span class="h-px flex-1 bg-slate-200"></span>
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black"
                                      :class="paymentStep === 2 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500'">2</span>
                                <span class="text-xs font-extrabold" :class="paymentStep === 2 ? 'text-[#071833]' : 'text-slate-500'">Payment method</span>
                            </div>
                        </div>

                        <!-- Step 1: fee selection -->
                        <div x-show="paymentStep === 1" x-transition.opacity>
                            <div class="mb-3 flex items-end justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-black text-[#071833]">Choose what you want to pay</h3>
                                    <p class="mt-0.5 text-xs font-semibold text-slate-600">You can select one or several unpaid items.</p>
                                </div>
                                <button
                                    type="button"
                                    class="text-xs font-extrabold text-blue-700 hover:text-blue-900"
                                    @click="selectedInvoices = selectedInvoices.length === Object.keys(totals).length ? [] : Object.keys(totals)"
                                >
                                    <span x-text="selectedInvoices.length === Object.keys(totals).length ? 'Clear all' : 'Select all'"></span>
                                </button>
                            </div>

                            <div class="overflow-hidden rounded-2xl border border-slate-200">
                                @forelse ($invoices->filter(fn ($invoice) => (float) $invoice->balance > 0) as $invoice)
                                    @php
                                        $isCleared = false;
                                    @endphp
                                    <label class="grid cursor-pointer grid-cols-[auto,1fr,auto] items-center gap-3 border-b border-slate-100 px-4 py-3 last:border-b-0 hover:bg-blue-50/40 {{ $isCleared ? 'cursor-default bg-slate-50 opacity-70' : 'bg-white' }}">
                                        <input
                                            type="checkbox"
                                            value="{{ $invoice->id }}"
                                            x-model="selectedInvoices"
                                            @disabled($isCleared)
                                            class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-extrabold text-[#071833]">{{ $invoice->feeItem->name ?? 'Custom Invoice Item' }}</span>
                                            <span class="mt-0.5 block text-[11px] font-semibold text-slate-500">
                                                {{ $invoice->invoice_no }} &bull; Due {{ optional($invoice->due_date)->format('M j, Y') ?: 'anytime' }}
                                            </span>
                                        </span>
                                        <span class="text-right">
                                            <span class="block whitespace-nowrap text-sm font-black text-[#071833]">NGN {{ number_format((float) $invoice->balance, 2) }}</span>
                                            <span class="mt-0.5 block text-[10px] font-extrabold {{ $isCleared ? 'text-emerald-700' : 'text-amber-700' }}">{{ $isCleared ? 'PAID' : 'UNPAID' }}</span>
                                        </span>
                                    </label>
                                @empty
                                    <div class="px-5 py-10 text-center">
                                        <p class="text-sm font-black text-[#071833]">No fee items available</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-500">New invoices will appear here when the school creates them.</p>
                                    </div>
                                @endforelse
                            </div>

                            <div class="sticky bottom-3 mt-4 flex flex-col gap-3 rounded-xl border border-[#c8d6ea] bg-white p-3 shadow-lg sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                                        <span x-text="selectedInvoices.length"></span> item(s) selected
                                    </p>
                                    <p class="display-font mt-0.5 text-xl font-black text-[#071833]"
                                       x-text="'NGN ' + selectedTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></p>
                                </div>
                                <button
                                    type="button"
                                    @click="if (selectedInvoices.length) paymentStep = 2"
                                    :disabled="selectedInvoices.length === 0"
                                    class="inline-flex min-h-11 items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-600"
                                >
                                    Continue to payment
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: payment method -->
                        <div x-show="paymentStep === 2" x-cloak x-transition.opacity>
                            <div class="mb-4">
                                <h3 class="text-sm font-black text-[#071833]">How would you like to pay?</h3>
                                <p class="mt-0.5 text-xs font-semibold text-slate-600">The secure gateway will show the payment channels available to you.</p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="cursor-pointer rounded-2xl border-2 p-4 transition"
                                       :class="selectedGateway === 'paystack' ? 'border-blue-600 bg-blue-50' : 'border-slate-200 bg-white hover:border-slate-300'">
                                    <input type="radio" x-model="selectedGateway" value="paystack" class="sr-only">
                                    <span class="flex items-start gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-lg text-white">💳</span>
                                        <span>
                                            <span class="block text-sm font-black text-[#071833]">Card, bank or USSD</span>
                                            <span class="mt-1 block text-xs font-semibold leading-relaxed text-slate-600">Continue securely with the school's Paystack checkout.</span>
                                        </span>
                                    </span>
                                </label>

                                <label class="cursor-pointer rounded-2xl border-2 p-4 transition"
                                       :class="selectedGateway === 'palmpay' ? 'border-blue-600 bg-blue-50' : 'border-slate-200 bg-white hover:border-slate-300'">
                                    <input type="radio" x-model="selectedGateway" value="palmpay" class="sr-only">
                                    <span class="flex items-start gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-lg text-white">📱</span>
                                        <span>
                                            <span class="block text-sm font-black text-[#071833]">Wallet payment</span>
                                            <span class="mt-1 block text-xs font-semibold leading-relaxed text-slate-600">Pay through the configured PalmPay wallet gateway.</span>
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs font-extrabold text-slate-600">Amount to pay</span>
                                    <span class="display-font text-xl font-black text-[#071833]"
                                          x-text="'NGN ' + selectedTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                                </div>
                                <p class="mt-2 text-[11px] font-semibold text-slate-500">Your card or banking details are entered only on the secure payment provider page.</p>
                            </div>

                            <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <button type="button" @click="paymentStep = 1" class="min-h-11 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-black text-[#071833] hover:bg-slate-50">
                                    Back to fees
                                </button>
                                <button
                                    x-show="selectedGateway === 'paystack'"
                                    type="submit"
                                    formaction="{{ route('payments.selection.checkout', 'paystack') }}"
                                    class="min-h-11 rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-sm hover:bg-blue-700"
                                >
                                    Pay securely now
                                </button>
                                <button
                                    x-show="selectedGateway === 'palmpay'"
                                    type="submit"
                                    formaction="{{ route('payments.selection.checkout', 'palmpay') }}"
                                    class="min-h-11 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-sm hover:bg-emerald-700"
                                >
                                    Continue to wallet
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- Transfer details and receipts -->
                <div class="grid gap-5 lg:grid-cols-2">
                    @if ($bankAccounts->isNotEmpty() || filled($schoolSettings['payment_instruction'] ?? null))
                        <section class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-4 flex items-start gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-lg">🏦</span>
                                <div>
                                    <h3 class="text-sm font-black text-[#071833]">Direct bank transfer</h3>
                                    <p class="mt-0.5 text-xs font-semibold text-slate-600">Use only the official school account below.</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                @foreach ($bankAccounts as $account)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">{{ $account['bank'] ?: 'School bank account' }}</p>
                                        <p class="mt-1 text-sm font-bold text-[#071833]">{{ $account['account_name'] ?: 'Pending name verification' }}</p>
                                        <div class="mt-2 flex items-center justify-between gap-3">
                                            <span class="display-font text-lg font-black tracking-wide text-[#071833]">{{ $account['account_number'] ?: 'N/A' }}</span>
                                            @if (filled($account['account_number']))
                                                <button type="button" class="rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-xs font-black text-blue-700 hover:bg-blue-50" onclick="navigator.clipboard.writeText('{{ $account['account_number'] }}')">Copy</button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if (filled($schoolSettings['payment_instruction'] ?? null))
                                <p class="mt-3 rounded-xl bg-amber-50 p-3 text-xs font-semibold leading-relaxed text-amber-900">{{ $schoolSettings['payment_instruction'] }}</p>
                            @endif
                        </section>
                    @endif

                    <section class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-sm {{ $bankAccounts->isEmpty() && blank($schoolSettings['payment_instruction'] ?? null) ? 'lg:col-span-2' : '' }}">
                        <div class="mb-4 flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-lg">🧾</span>
                            <div>
                                <h3 class="text-sm font-black text-[#071833]">Recent receipts</h3>
                                <p class="mt-0.5 text-xs font-semibold text-slate-600">Confirmed payments for this student account.</p>
                            </div>
                        </div>
                        <div class="space-y-2.5">
                            @forelse ($payments->take(5) as $payment)
                                <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-3.5 py-3 transition hover:border-blue-300 hover:bg-blue-50/40">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-black text-[#071833]">NGN {{ number_format((float) $payment->amount, 2) }}</span>
                                        <span class="mt-0.5 block truncate text-[10px] font-semibold text-slate-500">{{ $payment->provider->label() }} &bull; {{ $payment->created_at?->format('M j, Y') }}</span>
                                    </span>
                                    <span class="shrink-0 text-xs font-black text-blue-700">View receipt →</span>
                                </a>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center">
                                    <p class="text-sm font-black text-[#071833]">No confirmed receipts yet</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">Successful payments will appear here automatically.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>
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
