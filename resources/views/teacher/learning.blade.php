<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Lessons, Assignments, & Grading Workspace" eyebrow="Teacher Portal">
            <x-slot name="description">
                @if ($classTeacherMode)
                    Scoped class teacher account for {{ $managedClasses->pluck('display_name')->join(', ') }}.
                @else
                    Manage course note libraries, build CBT exams, assign schoolwork, and log dynamic grades.
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="space-y-5">
        @if ($classTeacherMode)
            <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)]">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="display-font text-lg font-bold text-slate-900 leading-snug">Class Teacher Responsibilities</h2>
                        <p class="text-xs font-semibold text-slate-500 mt-1 leading-relaxed max-w-xl">You hold primary administrative responsibility over student attendance sheets, cumulative score compilations, homework submissions, and final term reviews in your assigned classes.</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-5 py-3 text-left lg:text-right">
                        <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Assigned Classes</div>
                        <div class="mt-1.5 text-sm font-black text-slate-800 tracking-tight">{{ $managedClasses->pluck('display_name')->join(', ') }}</div>
                    </div>
                </div>
            </div>
        @endif

        <div>
            @if ($activeTeachingSection === 'publish-lesson')
                <x-form-card :action="route('teacher.lessons.store')" method="POST" title="Publish Lesson Note" description="Create a new lesson note with optional video or downloadable attachments." enctype="multipart/form-data">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Subject</label>
                            <select name="subject_id" class="theme-input w-full" required>
                                <option value="">Choose Subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Class Group</label>
                            <select name="school_class_id" class="theme-input w-full" required>
                                <option value="">Choose Class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Lesson Title</label>
                        <input name="title" placeholder="e.g. Introduction to Organic Chemistry" class="theme-input w-full" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Short Summary / Description</label>
                        <textarea name="summary" rows="2" placeholder="Write a brief overview..." class="theme-input w-full"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Lesson Note Content</label>
                        <textarea name="body" rows="6" placeholder="Type or paste the complete lesson note here..." class="theme-input w-full" required></textarea>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">External Video URL (YouTube, Vimeo, etc.)</label>
                            <input name="video_url" placeholder="https://..." class="theme-input w-full" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Resource / Download Link</label>
                            <input name="resource_link" placeholder="https://..." class="theme-input w-full" />
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 bg-slate-50 p-4.5 rounded-[14px] border border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide cursor-pointer">
                            <span class="mb-1.5 block font-bold text-slate-800">Upload Video File (.mp4, .webm)</span>
                            <input type="file" name="video_file" accept=".mp4,.webm,.mov,.m4v,video/mp4,video/webm,video/quicktime" class="block w-full text-xs font-semibold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        </label>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide cursor-pointer">
                            <span class="mb-1.5 block font-bold text-slate-800">Lesson Note Attachment Images</span>
                            <input type="file" name="note_images[]" accept="image/*" multiple class="block w-full text-xs font-semibold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        </label>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="success" icon="save">Publish Lesson Note</x-action-button>
                    </x-slot>
                </x-form-card>
            @endif

            @if ($activeTeachingSection === 'create-assignment')
                <x-form-card :action="route('teacher.assignments.store')" method="POST" title="Create Student Assignment" description="Assign schoolwork, set due dates, and track scores." enctype="multipart/form-data">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Subject</label>
                            <select name="subject_id" class="theme-input w-full" required>
                                <option value="">Choose Subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Class Group</label>
                            <select name="school_class_id" class="theme-input w-full" required>
                                <option value="">Choose Class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Assignment Title</label>
                        <input name="title" placeholder="e.g. Quadratic Equations Exercises" class="theme-input w-full" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Detailed Instructions</label>
                        <textarea name="instructions" rows="6" placeholder="Explain the assignment guidelines clearly..." class="theme-input w-full" required></textarea>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Submission Deadline</label>
                            <input name="due_date" type="datetime-local" class="theme-input w-full" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Total Obtainable Mark</label>
                            <input name="total_score" type="number" step="0.01" value="100" class="theme-input w-full" required />
                        </div>
                    </div>
                    <div class="bg-slate-50 p-4.5 rounded-[14px] border border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide cursor-pointer">
                            <span class="mb-1.5 block font-bold text-slate-800">Upload Attachment Images</span>
                            <input type="file" name="attachment_images[]" accept="image/*" multiple class="block w-full text-xs font-semibold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        </label>
                    </div>
                    <input type="hidden" name="status" value="published" />
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="success" icon="save">Publish Assignment</x-action-button>
                    </x-slot>
                </x-form-card>
            @endif

            @if ($activeTeachingSection === 'assessment')
                <x-form-card :action="route('teacher.assessments.store')" method="POST" title="Configure New Assessment" description="Set up standard tests, quizzes, or exams.">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Academic Term</label>
                            <select name="term_id" class="theme-input w-full" required>
                                <option value="">Select Term</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Subject</label>
                                <select name="subject_id" class="theme-input w-full" required>
                                    <option value="">Choose Subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Class Group</label>
                                <select name="school_class_id" class="theme-input w-full" required>
                                    <option value="">Choose Class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Assessment Title</label>
                            <input name="title" placeholder="e.g. Continuous Assessment Test 1" class="theme-input w-full" required />
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Assessment Type</label>
                                <select name="type" class="theme-input w-full" required>
                                    <option value="quiz">Quiz</option>
                                    <option value="test">Test</option>
                                    <option value="exam">Exam</option>
                                    <option value="project">Project</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Total Obtainable Score</label>
                                <input name="total_score" type="number" step="0.01" value="100" class="theme-input w-full" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Scheduled Date & Time</label>
                            <input name="scheduled_at" type="datetime-local" class="theme-input w-full" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Internal Notes</label>
                            <textarea name="notes" rows="3" placeholder="Add administrative notes..." class="theme-input w-full"></textarea>
                        </div>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="success" icon="save">Create Assessment</x-action-button>
                    </x-slot>
                </x-form-card>
            @endif

            @if ($activeTeachingSection === 'record-result')
                <x-form-card :action="route('teacher.results.store')" method="POST" title="Record Student Score" description="Directly save standard marks and custom report comments.">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Assessment Target</label>
                            <select name="assessment_id" class="theme-input w-full" required>
                                <option value="">Select Assessment</option>
                                @foreach ($assessments as $assessment)
                                    <option value="{{ $assessment->id }}">{{ $assessment->title }} - {{ $assessment->schoolClass->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Student</label>
                            <select name="student_id" class="theme-input w-full" required>
                                <option value="">Select Student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->user->name }} - {{ $student->admission_no }} - {{ $student->schoolClass->display_name ?? 'No class' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Obtained Score</label>
                                <input name="score" type="number" step="0.01" placeholder="0.00" class="theme-input w-full" required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Calculated Grade (Optional)</label>
                                <input name="grade" placeholder="e.g. A1" class="theme-input w-full" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Teacher Remark</label>
                            <input name="remark" placeholder="Write academic remark comment..." class="theme-input w-full" />
                        </div>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="success" icon="save">Save Student Result</x-action-button>
                    </x-slot>
                </x-form-card>
            @endif

            @if ($activeTeachingSection === 'attendance')
                <x-form-card :action="route('teacher.attendance.store')" method="POST" title="Submit Attendance Log" description="Log daily present/absent states for students.">
                    <div class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Class Group</label>
                                <select name="school_class_id" class="theme-input w-full" required>
                                    <option value="">Choose Class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Roster Student</label>
                                <select name="student_id" class="theme-input w-full" required>
                                    <option value="">Choose Student</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->user->name }} - {{ $student->schoolClass->display_name ?? 'No class' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Date</label>
                                <input name="attendance_date" type="date" class="theme-input w-full" required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Attendance Status</label>
                                <select name="status" class="theme-input w-full" required>
                                    <option value="present">Present</option>
                                    <option value="late">Late</option>
                                    <option value="absent">Absent</option>
                                    <option value="excused">Excused</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Roster Note / Details</label>
                            <textarea name="note" rows="3" placeholder="Add any details (e.g. sick note verified)..." class="theme-input w-full"></textarea>
                        </div>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="success" icon="save">Save Attendance Log</x-action-button>
                    </x-slot>
                </x-form-card>
            @endif

            @if ($activeTeachingSection === 'cbt-create')
                <x-form-card :action="route('teacher.cbt.assessments.store')" method="POST" title="Configure CBT Assessment" description="Create computer-based objective and subjective tests.">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Academic Term</label>
                            <select name="term_id" class="theme-input w-full">
                                <option value="">Choose Term</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">CBT Type</label>
                            <select name="type" class="theme-input w-full" required>
                                <option value="quiz">Quiz</option>
                                <option value="test">Test</option>
                                <option value="exam">Exam</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Subject</label>
                            <select name="subject_id" class="theme-input w-full" required>
                                <option value="">Choose Subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Target Class Group</label>
                            <select name="school_class_id" class="theme-input w-full" required>
                                <option value="">Choose Class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">CBT Title</label>
                        <input name="title" placeholder="e.g. First Term Biology Mock Exam" class="theme-input w-full" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Assessment Instructions</label>
                        <textarea name="cbt_instructions" rows="4" placeholder="Give students important exam instructions..." class="theme-input w-full"></textarea>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Duration (Minutes)</label>
                            <input name="cbt_duration_minutes" type="number" min="5" max="300" value="60" class="theme-input w-full" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Starts At</label>
                            <input name="cbt_starts_at" type="datetime-local" class="theme-input w-full" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">Ends At</label>
                            <input name="cbt_ends_at" type="datetime-local" class="theme-input w-full" />
                        </div>
                    </div>
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-xs font-bold text-slate-700 hover:bg-slate-50 cursor-pointer transition">
                        <input type="checkbox" name="cbt_show_results" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                        <div>
                            <p class="text-slate-800 font-extrabold">Instant Results Release</p>
                            <p class="text-slate-500 font-medium text-[10px] mt-0.5">Let students view their scored objective answers immediately after final submission.</p>
                        </div>
                    </label>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="success" icon="save">Create CBT Assessment</x-action-button>
                    </x-slot>
                </x-form-card>
            @endif

            @if ($activeTeachingSection === 'cbt-list')
                <x-dashboard-card title="CBT Library" subtitle="Scan computer-based assessments, attempts, and question-builder status." icon="portal" accent="purple">
                    <x-data-table :headers="['Assessment', 'Class', 'Schedule', 'Questions', 'Attempts', 'Status', 'Actions']" minWidth="940px">
                    @forelse ($cbtAssessments as $cbtAssessment)
                        @php
                            $assessmentTitle = $cbtAssessment->title;
                            $assessmentSubtitle = ($cbtAssessment->subject->name ?? 'Subject pending') . ' | ' . ($cbtAssessment->schoolClass->display_name ?? 'Class pending');
                            $assessmentInitials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($cbtAssessment->subject->name ?? 'CBT', 0, 2));
                            $cbtPreview = [
                                'type' => 'assessment',
                                'avatar' => $assessmentInitials,
                                'title' => $assessmentTitle,
                                'subtitle' => ($cbtAssessment->cbt_is_active ? 'Published CBT' : 'Draft CBT') . ' | ' . $assessmentSubtitle,
                                'profileUrl' => route('teacher.cbt.assessments.show', $cbtAssessment),
                                'ctaLabel' => 'Open Question Builder',
                                'fields' => [
                                    ['label' => 'Subject', 'value' => $cbtAssessment->subject->name ?? 'Not assigned'],
                                    ['label' => 'Class', 'value' => $cbtAssessment->schoolClass->display_name ?? 'Not assigned'],
                                    ['label' => 'Duration', 'value' => $cbtAssessment->cbt_duration_minutes . ' minutes'],
                                    ['label' => 'Questions', 'value' => (string) $cbtAssessment->cbtQuestions_count],
                                    ['label' => 'Attempts', 'value' => (string) $cbtAssessment->cbtAttempts_count],
                                    ['label' => 'Starts', 'value' => optional($cbtAssessment->cbt_starts_at)->format('M j, Y g:i A') ?: 'Not scheduled'],
                                ],
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div class="table-person">
                                    <div class="table-avatar">{{ $assessmentInitials }}</div>
                                    <div class="table-person-text">
                                        <strong>{{ $assessmentTitle }}</strong>
                                        <span>{{ $assessmentSubtitle }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $cbtAssessment->schoolClass->display_name ?? 'N/A' }}</td>
                            <td><span class="table-text-clip">{{ optional($cbtAssessment->cbt_starts_at)->format('M j, g:i A') ?: 'Not scheduled' }}</span></td>
                            <td>{{ $cbtAssessment->cbtQuestions_count }}</td>
                            <td>{{ $cbtAssessment->cbtAttempts_count }}</td>
                            <td><x-status-badge :status="$cbtAssessment->cbt_is_active ? 'published' : 'draft'" :label="$cbtAssessment->cbt_is_active ? 'Published' : 'Draft'" /></td>
                            <td>
                                <button type="button" class="table-view-btn" data-preview='@json($cbtPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state title="No CBT Assessments Configured" explanation="No computer-based tests have been created in this category. Navigate to the Create CBT tab to register one." />
                            </td>
                        </tr>
                    @endforelse
                    </x-data-table>
                </x-dashboard-card>
            @endif

            @if ($activeTeachingSection === 'latest-content')
                <div class="space-y-8">
                    <x-dashboard-card title="Latest Published Lesson Notes" subtitle="Recently shared class resources and teacher notes." icon="learning" accent="blue">
                        <x-data-table :headers="['Lesson', 'Class', 'Teacher', 'Published', 'Media', 'Actions']" minWidth="920px">
                            @forelse ($lessons as $lesson)
                                @php
                                    $lessonMedia = collect([
                                        $lesson->video_path || $lesson->video_url ? 'Video' : null,
                                        filled($lesson->note_images) ? count($lesson->note_images) . ' image(s)' : null,
                                        $lesson->resource_link ? 'Resource link' : null,
                                    ])->filter()->join(', ') ?: 'None';
                                    $lessonInitials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($lesson->subject->name ?? 'LN', 0, 2));
                                    $lessonPreview = [
                                        'type' => 'lesson',
                                        'avatar' => $lessonInitials,
                                        'title' => $lesson->title,
                                        'subtitle' => ($lesson->subject->name ?? 'Subject pending') . ' | ' . ($lesson->schoolClass->display_name ?? 'Class pending'),
                                        'profileUrl' => $lesson->resource_link ?: null,
                                        'ctaLabel' => $lesson->resource_link ? 'Open Resource' : null,
                                        'fields' => [
                                            ['label' => 'Subject', 'value' => $lesson->subject->name ?? 'Not assigned'],
                                            ['label' => 'Class', 'value' => $lesson->schoolClass->display_name ?? 'Not assigned'],
                                            ['label' => 'Teacher', 'value' => $lesson->teacher->fullName()],
                                            ['label' => 'Published', 'value' => optional($lesson->created_at)->format('M j, Y') ?: 'Not available'],
                                            ['label' => 'Media', 'value' => $lessonMedia],
                                            ['label' => 'Summary', 'value' => \Illuminate\Support\Str::limit($lesson->summary ?: 'No summary added.', 140)],
                                        ],
                                    ];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="table-person">
                                            <div class="table-avatar">{{ $lessonInitials }}</div>
                                            <div class="table-person-text">
                                                <strong>{{ $lesson->title }}</strong>
                                                <span>{{ $lesson->subject->name ?? 'Subject pending' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $lesson->schoolClass->display_name ?? 'N/A' }}</td>
                                    <td><span class="table-text-clip">{{ $lesson->teacher->fullName() }}</span></td>
                                    <td>{{ optional($lesson->created_at)->format('M j, Y') }}</td>
                                    <td><span class="table-text-clip">{{ $lessonMedia }}</span></td>
                                    <td><button type="button" class="table-view-btn" data-preview='@json($lessonPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No Lessons Published" explanation="No lesson notes exist on the platform. Jump to the Publish Lesson workspace to create one." />
                                    </td>
                                </tr>
                            @endforelse
                        </x-data-table>
                    </x-dashboard-card>

                    <x-dashboard-card title="Latest Assigned Schoolwork" subtitle="Recent assignment records, deadlines, and class targets." icon="assignments" accent="orange">
                        <x-data-table :headers="['Assignment', 'Class', 'Due Date', 'Score', 'Status', 'Actions']" minWidth="880px">
                            @forelse ($assignments as $assignment)
                                @php
                                    $assignmentInitials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($assignment->subject->name ?? 'AS', 0, 2));
                                    $assignmentPreview = [
                                        'type' => 'assignment',
                                        'avatar' => $assignmentInitials,
                                        'title' => $assignment->title,
                                        'subtitle' => ($assignment->subject->name ?? 'Subject pending') . ' | ' . ($assignment->schoolClass->display_name ?? 'Class pending'),
                                        'fields' => [
                                            ['label' => 'Subject', 'value' => $assignment->subject->name ?? 'Not assigned'],
                                            ['label' => 'Class', 'value' => $assignment->schoolClass->display_name ?? 'Not assigned'],
                                            ['label' => 'Due Date', 'value' => optional($assignment->due_date)->format('M j, Y g:i A') ?: 'Open'],
                                            ['label' => 'Total Score', 'value' => number_format((float) $assignment->total_score, 2)],
                                            ['label' => 'Attachments', 'value' => filled($assignment->attachment_images) ? count($assignment->attachment_images) . ' image(s)' : 'None'],
                                            ['label' => 'Instructions', 'value' => \Illuminate\Support\Str::limit($assignment->instructions ?: 'No instructions added.', 140)],
                                        ],
                                    ];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="table-person">
                                            <div class="table-avatar">{{ $assignmentInitials }}</div>
                                            <div class="table-person-text">
                                                <strong>{{ $assignment->title }}</strong>
                                                <span>{{ $assignment->subject->name ?? 'Subject pending' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $assignment->schoolClass->display_name ?? 'N/A' }}</td>
                                    <td>{{ optional($assignment->due_date)->format('M j, Y') ?: 'Open' }}</td>
                                    <td>{{ number_format((float) $assignment->total_score, 2) }}</td>
                                    <td><x-status-badge :status="$assignment->status" /></td>
                                    <td><button type="button" class="table-view-btn" data-preview='@json($assignmentPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state title="No Assignments Assigned" explanation="No assignments have been assigned yet. Jump to the Create Assignment workspace to write one." />
                                    </td>
                                </tr>
                            @endforelse
                        </x-data-table>
                    </x-dashboard-card>
                </div>
            @endif

            @if ($activeTeachingSection === 'submissions')
                <x-dashboard-card title="Assignment Submissions" subtitle="Review student answers and save scores without opening large cards." icon="assignments" accent="green">
                    <x-data-table :headers="['Student', 'Assignment', 'Submitted Answer', 'Score', 'Status', 'Actions']" minWidth="1120px">
                    @forelse ($submissions as $submission)
                        @php
                            $submissionName = $submission->student->user->fullName();
                            $submissionInitials = collect(preg_split('/\s+/', trim($submissionName)))->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->join('') ?: 'ST';
                            $submissionPreview = [
                                'type' => 'submission',
                                'avatar' => $submissionInitials,
                                'title' => $submissionName,
                                'subtitle' => ($submission->score !== null ? 'Graded Submission' : 'Unmarked Submission') . ' | ' . ($submission->student->schoolClass->display_name ?? 'No class'),
                                'fields' => [
                                    ['label' => 'Assignment', 'value' => $submission->assignment->title],
                                    ['label' => 'Class', 'value' => $submission->student->schoolClass->display_name ?? 'No class'],
                                    ['label' => 'Score', 'value' => $submission->score !== null ? number_format((float) $submission->score, 2) : 'Not graded'],
                                    ['label' => 'Feedback', 'value' => $submission->feedback ?: 'No feedback yet'],
                                    ['label' => 'Submitted', 'value' => optional($submission->created_at)->format('M j, Y g:i A') ?: 'Not available'],
                                    ['label' => 'Answer', 'value' => \Illuminate\Support\Str::limit($submission->content ?: 'No written answer.', 180)],
                                ],
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div class="table-person">
                                    <div class="table-avatar">{{ $submissionInitials }}</div>
                                    <div class="table-person-text">
                                        <strong>{{ $submissionName }}</strong>
                                        <span>{{ $submission->student->schoolClass->display_name ?? 'No class' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="table-text-clip">{{ $submission->assignment->title }}</span></td>
                            <td><span class="table-text-clip">{{ \Illuminate\Support\Str::limit($submission->content, 72) }}</span></td>
                            <td>{{ $submission->score !== null ? number_format((float) $submission->score, 2) : 'Pending' }}</td>
                            <td><x-status-badge :status="$submission->score !== null ? 'marked' : 'pending'" :label="$submission->score !== null ? 'Graded' : 'Unmarked'" /></td>
                            <td>
                                <div class="table-action-group">
                                    <button type="button" class="table-view-btn" data-preview='@json($submissionPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                    <form method="POST" action="{{ route('teacher.submissions.grade', $submission) }}" class="table-inline-form">
                                        @csrf
                                        <input name="score" type="number" step="0.01" value="{{ $submission->score }}" placeholder="Score" class="table-score-input" required />
                                        <input name="feedback" value="{{ $submission->feedback }}" placeholder="Feedback" class="table-feedback-input" />
                                        <button type="submit" class="table-toggle-btn">Save</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="No Submissions Awaiting Attention" explanation="You have graded all outstanding student assignments! Take a break, you've earned it." />
                            </td>
                        </tr>
                    @endforelse
                    </x-data-table>
                </x-dashboard-card>
            @endif

            @if ($activeTeachingSection === 'cbt-attempts')
                <x-dashboard-card title="CBT Reviews" subtitle="Subjective attempts awaiting review or recently graded." icon="reports" accent="purple">
                    <x-data-table :headers="['Student', 'Assessment', 'Subject / Class', 'Submitted', 'Status', 'Actions']" minWidth="980px">
                    @forelse ($cbtAttemptsNeedingReview as $attempt)
                        @php
                            $attemptName = $attempt->student->user->fullName();
                            $attemptInitials = collect(preg_split('/\s+/', trim($attemptName)))->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->join('') ?: 'ST';
                            $attemptPreview = [
                                'type' => 'attempt',
                                'avatar' => $attemptInitials,
                                'title' => $attemptName,
                                'subtitle' => ucfirst($attempt->status) . ' CBT Attempt | ' . ($attempt->assessment->schoolClass->display_name ?? 'No class'),
                                'profileUrl' => route('teacher.cbt.attempts.show', $attempt),
                                'ctaLabel' => 'Review Attempt',
                                'fields' => [
                                    ['label' => 'Assessment', 'value' => $attempt->assessment->title],
                                    ['label' => 'Subject', 'value' => $attempt->assessment->subject->name ?? 'Not assigned'],
                                    ['label' => 'Class', 'value' => $attempt->assessment->schoolClass->display_name ?? 'Not assigned'],
                                    ['label' => 'Submitted', 'value' => optional($attempt->submitted_at)->format('M j, Y g:i A') ?: 'Not submitted'],
                                    ['label' => 'Status', 'value' => ucfirst($attempt->status)],
                                    ['label' => 'Score', 'value' => $attempt->total_score !== null ? number_format((float) $attempt->total_score, 2) : 'Pending review'],
                                ],
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div class="table-person">
                                    <div class="table-avatar">{{ $attemptInitials }}</div>
                                    <div class="table-person-text">
                                        <strong>{{ $attemptName }}</strong>
                                        <span>{{ $attempt->student->schoolClass->display_name ?? $attempt->assessment->schoolClass->display_name ?? 'No class' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="table-text-clip">{{ $attempt->assessment->title }}</span></td>
                            <td><span class="table-text-clip">{{ $attempt->assessment->subject->name ?? 'Subject' }} | {{ $attempt->assessment->schoolClass->display_name ?? 'Class' }}</span></td>
                            <td>{{ optional($attempt->submitted_at)->format('M j, Y') ?: 'N/A' }}</td>
                            <td><x-status-badge :status="$attempt->status === 'graded' ? 'marked' : 'pending'" :label="ucfirst($attempt->status)" /></td>
                            <td><button type="button" class="table-view-btn" data-preview='@json($attemptPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="No CBT Reviews Outstanding" explanation="No subjective theory assessments are currently pending manual grading." />
                            </td>
                        </tr>
                    @endforelse
                    </x-data-table>
                </x-dashboard-card>
            @endif
        </div>
    </div>

    <x-entity-preview-modal />
</x-app-layout>
