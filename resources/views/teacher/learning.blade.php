<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Teacher workspace</p>
            <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">Lessons, assignments, assessments, and grading</h1>
            @if ($classTeacherMode)
                <p class="mt-2 text-sm text-slate-600">Class teacher account for {{ $managedClasses->pluck('display_name')->join(', ') }}. This workspace is scoped to your assigned class records only.</p>
            @endif
        </div>
    </x-slot>

    @php
        $teachingNavItems = [
            ['key' => 'publish-lesson', 'label' => 'Lessons', 'href' => route('teacher.learning', ['section' => 'publish-lesson'])],
            ['key' => 'create-assignment', 'label' => 'Assignments', 'href' => route('teacher.learning', ['section' => 'create-assignment'])],
            ['key' => 'assessment', 'label' => 'Assessments', 'href' => route('teacher.learning', ['section' => 'assessment'])],
            ['key' => 'record-result', 'label' => 'Results', 'href' => route('teacher.learning', ['section' => 'record-result'])],
            ['key' => 'attendance', 'label' => 'Attendance', 'href' => route('teacher.learning', ['section' => 'attendance'])],
            ['key' => 'cbt-create', 'label' => 'Create CBT', 'href' => route('teacher.learning', ['section' => 'cbt-create'])],
            ['key' => 'cbt-list', 'label' => 'CBT Library', 'href' => route('teacher.learning', ['section' => 'cbt-list'])],
            ['key' => 'latest-content', 'label' => 'Latest Content', 'href' => route('teacher.learning', ['section' => 'latest-content'])],
            ['key' => 'submissions', 'label' => 'Submissions', 'href' => route('teacher.learning', ['section' => 'submissions'])],
            ['key' => 'cbt-attempts', 'label' => 'CBT Reviews', 'href' => route('teacher.learning', ['section' => 'cbt-attempts'])],
        ];
    @endphp

    <div class="grid gap-8">
        @if ($classTeacherMode)
            <section class="section-card">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="display-font text-2xl font-bold text-slate-950">Class teacher account</h2>
                        <p class="mt-2 text-sm text-slate-500">You are responsible for attendance, classwork, assignments, assessments, exams, and results for the students in your assigned class.</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 px-5 py-4 text-right">
                        <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Assigned classes</div>
                        <div class="mt-2 font-semibold text-slate-900">{{ $managedClasses->pluck('display_name')->join(', ') }}</div>
                    </div>
                </div>
            </section>
        @endif

        <x-section-nav :items="$teachingNavItems" :active="$activeTeachingSection" />

        @if (in_array($activeTeachingSection, ['publish-lesson', 'create-assignment'], true))
        <div class="grid gap-8 xl:grid-cols-2">
        @endif

        @if ($activeTeachingSection === 'publish-lesson')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Publish lesson</h2>
            <form method="POST" action="{{ route('teacher.lessons.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <select name="subject_id" class="theme-input" required>
                        <option value="">Subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    <select name="school_class_id" class="theme-input" required>
                        <option value="">Class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <input name="title" placeholder="Lesson title" class="theme-input w-full" required />
                <textarea name="summary" rows="2" placeholder="Short summary" class="theme-input w-full"></textarea>
                <textarea name="body" rows="5" placeholder="Lesson note" class="theme-input w-full" required></textarea>
                <div class="grid gap-4 md:grid-cols-2">
                    <input name="video_url" placeholder="Video URL" class="theme-input" />
                    <input name="resource_link" placeholder="Resource link" class="theme-input" />
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                        <span class="mb-2 block font-semibold text-slate-900">Upload video lesson</span>
                        <input type="file" name="video_file" accept=".mp4,.webm,.mov,.m4v,video/mp4,video/webm,video/quicktime" class="block w-full text-sm" />
                    </label>
                    <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                        <span class="mb-2 block font-semibold text-slate-900">Lesson note images</span>
                        <input type="file" name="note_images[]" accept="image/*" multiple class="block w-full text-sm" />
                    </label>
                </div>
                <button type="submit" class="theme-button">Publish lesson</button>
            </form>
        </section>
        @endif

        @if ($activeTeachingSection === 'create-assignment')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Create assignment</h2>
            <form method="POST" action="{{ route('teacher.assignments.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <select name="subject_id" class="theme-input" required>
                        <option value="">Subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    <select name="school_class_id" class="theme-input" required>
                        <option value="">Class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <input name="title" placeholder="Assignment title" class="theme-input w-full" required />
                <textarea name="instructions" rows="5" placeholder="Instructions" class="theme-input w-full" required></textarea>
                <div class="grid gap-4 md:grid-cols-2">
                    <input name="due_date" type="datetime-local" class="theme-input" />
                    <input name="total_score" type="number" step="0.01" value="100" class="theme-input" required />
                </div>
                <label class="block rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                    <span class="mb-2 block font-semibold text-slate-900">Assignment images</span>
                    <input type="file" name="attachment_images[]" accept="image/*" multiple class="block w-full text-sm" />
                </label>
                <input name="status" value="published" class="theme-input w-full" />
                <button type="submit" class="theme-button">Create assignment</button>
            </form>
        </section>
        @endif

        @if (in_array($activeTeachingSection, ['publish-lesson', 'create-assignment'], true))
        </div>
        @endif

        @if (in_array($activeTeachingSection, ['assessment', 'record-result', 'attendance'], true))
        <div class="grid gap-8 xl:grid-cols-3">
        @endif

        @if ($activeTeachingSection === 'assessment')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Assessment</h2>
            <form method="POST" action="{{ route('teacher.assessments.store') }}" class="mt-6 space-y-4">
                @csrf
                <select name="term_id" class="theme-input w-full">
                    <option value="">Term</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                    @endforeach
                </select>
                <select name="subject_id" class="theme-input w-full" required>
                    <option value="">Subject</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
                <select name="school_class_id" class="theme-input w-full" required>
                    <option value="">Class</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
                <input name="title" placeholder="Assessment title" class="theme-input w-full" required />
                <select name="type" class="theme-input w-full" required>
                    <option value="quiz">Quiz</option>
                    <option value="test">Test</option>
                    <option value="exam">Exam</option>
                    <option value="project">Project</option>
                </select>
                <input name="total_score" type="number" step="0.01" value="100" class="theme-input w-full" required />
                <input name="scheduled_at" type="datetime-local" class="theme-input w-full" />
                <textarea name="notes" rows="3" placeholder="Notes" class="theme-input w-full"></textarea>
                <button type="submit" class="theme-button">Create assessment</button>
            </form>
        </section>
        @endif

        @if ($activeTeachingSection === 'record-result')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Record result</h2>
            <form method="POST" action="{{ route('teacher.results.store') }}" class="mt-6 space-y-4">
                @csrf
                <select name="assessment_id" class="theme-input w-full" required>
                    <option value="">Assessment</option>
                    @foreach ($assessments as $assessment)
                        <option value="{{ $assessment->id }}">{{ $assessment->title }} - {{ $assessment->schoolClass->display_name }}</option>
                    @endforeach
                </select>
                <select name="student_id" class="theme-input w-full" required>
                    <option value="">Student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->user->name }} - {{ $student->admission_no }} - {{ $student->schoolClass->display_name ?? 'No class' }}</option>
                    @endforeach
                </select>
                <div class="grid gap-4 md:grid-cols-2">
                    <input name="score" type="number" step="0.01" placeholder="Score" class="theme-input" required />
                    <input name="grade" placeholder="Grade" class="theme-input" />
                </div>
                <input name="remark" placeholder="Remark" class="theme-input w-full" />
                <button type="submit" class="theme-button">Save result</button>
            </form>
        </section>
        @endif

        @if ($activeTeachingSection === 'attendance')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Mark attendance</h2>
            <form method="POST" action="{{ route('teacher.attendance.store') }}" class="mt-6 space-y-4">
                @csrf
                <select name="school_class_id" class="theme-input w-full" required>
                    <option value="">Class</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
                <select name="student_id" class="theme-input w-full" required>
                    <option value="">Student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->user->name }} - {{ $student->schoolClass->display_name ?? 'No class' }}</option>
                    @endforeach
                </select>
                <input name="attendance_date" type="date" class="theme-input w-full" required />
                <select name="status" class="theme-input w-full" required>
                    <option value="present">Present</option>
                    <option value="late">Late</option>
                    <option value="absent">Absent</option>
                    <option value="excused">Excused</option>
                </select>
                <textarea name="note" rows="3" placeholder="Note" class="theme-input w-full"></textarea>
                <button type="submit" class="theme-button">Save attendance</button>
            </form>
        </section>
        @endif

        @if (in_array($activeTeachingSection, ['assessment', 'record-result', 'attendance'], true))
        </div>
        @endif

        @if (in_array($activeTeachingSection, ['cbt-create', 'cbt-list'], true))
        <div class="grid gap-8 xl:grid-cols-[1.1fr,0.9fr]">
        @endif

        @if ($activeTeachingSection === 'cbt-create')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Create CBT assessment</h2>
            <p class="mt-2 text-sm text-slate-500">Set up a CBT test or exam, then open the question builder to add objective or theory questions with text, images, video, and links.</p>
            <form method="POST" action="{{ route('teacher.cbt.assessments.store') }}" class="mt-6 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <select name="term_id" class="theme-input">
                        <option value="">Term</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </select>
                    <select name="type" class="theme-input" required>
                        <option value="quiz">Quiz</option>
                        <option value="test">Test</option>
                        <option value="exam">Exam</option>
                    </select>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <select name="subject_id" class="theme-input" required>
                        <option value="">Subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    <select name="school_class_id" class="theme-input" required>
                        <option value="">Class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <input name="title" placeholder="SS1 Biology Mid-term CBT" class="theme-input w-full" required />
                <textarea name="cbt_instructions" rows="4" placeholder="Instructions for students before they start the CBT" class="theme-input w-full"></textarea>
                <div class="grid gap-4 md:grid-cols-3">
                    <input name="cbt_duration_minutes" type="number" min="5" max="300" value="60" class="theme-input" required />
                    <input name="cbt_starts_at" type="datetime-local" class="theme-input" />
                    <input name="cbt_ends_at" type="datetime-local" class="theme-input" />
                </div>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600">
                    <input type="checkbox" name="cbt_show_results" value="1" class="rounded border-slate-300" />
                    Allow students to see CBT result after grading
                </label>
                <button type="submit" class="theme-button">Create CBT assessment</button>
            </form>
        </section>
        @endif

        @if ($activeTeachingSection === 'cbt-list')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">CBT assessments</h2>
            <div class="mt-5 space-y-4">
                @forelse ($cbtAssessments as $cbtAssessment)
                    <article class="rounded-3xl border border-slate-200 px-5 py-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $cbtAssessment->title }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $cbtAssessment->subject->name }} | {{ $cbtAssessment->schoolClass->display_name }} | {{ $cbtAssessment->type->label() }}</div>
                                <div class="mt-2 text-xs uppercase tracking-[0.2em] text-slate-500">
                                    {{ $cbtAssessment->cbtQuestions_count }} question(s)
                                    |
                                    {{ $cbtAssessment->cbtAttempts_count }} attempt(s)
                                    |
                                    {{ $cbtAssessment->cbt_is_active ? 'Admin active' : 'Waiting for admin activation' }}
                                </div>
                            </div>
                            <a href="{{ route('teacher.cbt.assessments.show', $cbtAssessment) }}" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Manage questions</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No CBT assessments yet.</div>
                @endforelse
            </div>
        </section>
        @endif

        @if (in_array($activeTeachingSection, ['cbt-create', 'cbt-list'], true))
        </div>
        @endif

        @if (in_array($activeTeachingSection, ['latest-content', 'submissions'], true))
        <div class="grid gap-8 xl:grid-cols-2">
        @endif

        @if ($activeTeachingSection === 'latest-content')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Latest lessons and assignments</h2>
            <div class="mt-5 space-y-3">
                @foreach ($lessons as $lesson)
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="font-semibold text-slate-900">{{ $lesson->title }}</div>
                        <div class="text-sm text-slate-500">{{ $lesson->subject->name }} - {{ $lesson->schoolClass->display_name }} @if($classTeacherMode) | {{ $lesson->teacher->fullName() }} @endif</div>
                        @if ($lesson->video_path || $lesson->video_url || filled($lesson->note_images))
                            <div class="mt-2 text-xs uppercase tracking-[0.2em] text-slate-500">
                                {{ $lesson->video_path || $lesson->video_url ? 'Video attached' : '' }}
                                @if (($lesson->video_path || $lesson->video_url) && filled($lesson->note_images))
                                    |
                                @endif
                                {{ filled($lesson->note_images) ? count($lesson->note_images).' image(s)' : '' }}
                            </div>
                        @endif
                    </div>
                @endforeach
                @foreach ($assignments as $assignment)
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="font-semibold text-slate-900">{{ $assignment->title }}</div>
                        <div class="text-sm text-slate-500">{{ $assignment->subject->name }} - {{ $assignment->schoolClass->display_name }} - Due {{ optional($assignment->due_date)->format('M j, Y g:i A') ?: 'not set' }} @if($classTeacherMode) | {{ $assignment->teacher->fullName() }} @endif</div>
                        @if (filled($assignment->attachment_images))
                            <div class="mt-2 text-xs uppercase tracking-[0.2em] text-slate-500">{{ count($assignment->attachment_images) }} image(s) attached</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        @if ($activeTeachingSection === 'submissions')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Submissions awaiting attention</h2>
            <div class="mt-5 space-y-4">
                @foreach ($submissions as $submission)
                    <div class="rounded-3xl border border-slate-200 px-5 py-5">
                        <div class="font-semibold text-slate-900">{{ $submission->student->user->name }} - {{ $submission->assignment->title }}</div>
                        <div class="mt-1 text-sm text-slate-500">{{ $submission->student->schoolClass->display_name ?? 'No class' }} @if($classTeacherMode) | {{ $submission->assignment->teacher->fullName() }} @endif</div>
                        <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($submission->content, 120) }}</p>
                        <form method="POST" action="{{ route('teacher.submissions.grade', $submission) }}" class="mt-4 grid gap-3 md:grid-cols-[120px,1fr,140px]">
                            @csrf
                            <input name="score" type="number" step="0.01" value="{{ $submission->score }}" placeholder="Score" class="theme-input" required />
                            <input name="feedback" value="{{ $submission->feedback }}" placeholder="Feedback" class="theme-input" />
                            <button type="submit" class="theme-button">Grade</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        @if (in_array($activeTeachingSection, ['latest-content', 'submissions'], true))
        </div>
        @endif

    @if ($activeTeachingSection === 'cbt-attempts')
    <section class="section-card">
        <h2 class="display-font text-2xl font-bold text-slate-950">CBT attempts awaiting review</h2>
        <div class="mt-5 space-y-4">
            @forelse ($cbtAttemptsNeedingReview as $attempt)
                <article class="rounded-3xl border border-slate-200 px-5 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="font-semibold text-slate-900">{{ $attempt->student->user->fullName() }} - {{ $attempt->assessment->title }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ $attempt->assessment->subject->name }} | {{ $attempt->assessment->schoolClass->display_name }}</div>
                            <div class="mt-2 text-xs uppercase tracking-[0.2em] text-slate-500">
                                {{ $attempt->status }}
                                @if ($attempt->submitted_at)
                                    | Submitted {{ $attempt->submitted_at->format('M j, Y g:i A') }}
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('teacher.cbt.attempts.show', $attempt) }}" class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Review attempt</a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No CBT attempts to review yet.</div>
            @endforelse
        </div>
    </section>
    @endif
    </div>
</x-app-layout>
