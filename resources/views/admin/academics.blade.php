<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Administration</p>
            <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">Academic structure and publishing</h1>
        </div>
    </x-slot>

    @php
        $academicNavItems = [
            ['key' => 'session-setup', 'label' => 'Session', 'href' => route('admin.academics', ['section' => 'session-setup'])],
            ['key' => 'term-setup', 'label' => 'Term', 'href' => route('admin.academics', ['section' => 'term-setup'])],
            ['key' => 'session-rollover', 'label' => 'Rollover', 'href' => route('admin.academics', ['section' => 'session-rollover'])],
            ['key' => 'promotion-review', 'label' => 'Promotions', 'href' => route('admin.academics', ['section' => 'promotion-review'])],
            ['key' => 'class-setup', 'label' => 'Classes', 'href' => route('admin.academics', ['section' => 'class-setup'])],
            ['key' => 'subject-setup', 'label' => 'Subjects', 'href' => route('admin.academics', ['section' => 'subject-setup'])],
            ['key' => 'announcement', 'label' => 'Announcements', 'href' => route('admin.academics', ['section' => 'announcement'])],
            ['key' => 'cbt-control', 'label' => 'CBT Control', 'href' => route('admin.academics', ['section' => 'cbt-control'])],
        ];
    @endphp

    <div class="grid gap-8">
        <x-section-nav :items="$academicNavItems" :active="$activeAcademicSection" />

        @if (in_array($activeAcademicSection, ['session-setup', 'term-setup'], true))
        <div class="grid gap-8 xl:grid-cols-2">
        @endif

        @if ($activeAcademicSection === 'session-setup')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Academic session</h2>
            <form method="POST" action="{{ route('admin.sessions.store') }}" class="mt-6 grid gap-4 md:grid-cols-2">
                @csrf
                <input name="name" placeholder="2026/2027" class="theme-input" required />
                <input name="promotion_pass_mark" type="number" step="0.01" min="0" max="100" value="{{ old('promotion_pass_mark', 50) }}" placeholder="Promotion pass mark" class="theme-input" required />
                <div class="flex min-h-[3.5rem] items-center gap-3 rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    <input id="session-current" name="is_current" type="checkbox" value="1" class="rounded border-slate-300" />
                    <label for="session-current">Set as current session</label>
                </div>
                <div class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-500">
                    Students are promoted when their overall subject percentage meets the pass mark for the closed session.
                </div>
                <input name="start_date" type="date" class="theme-input" required />
                <input name="end_date" type="date" class="theme-input" required />
                <button type="submit" class="theme-button md:col-span-2">Create session</button>
            </form>
        </section>
        @endif

        @if ($activeAcademicSection === 'term-setup')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Term</h2>
            <form method="POST" action="{{ route('admin.terms.store') }}" class="mt-6 grid gap-4 md:grid-cols-2">
                @csrf
                <select name="academic_session_id" class="theme-input" required>
                    <option value="">Select session</option>
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
                <input name="name" placeholder="First Term" class="theme-input" required />
                <input name="start_date" type="date" class="theme-input" required />
                <input name="end_date" type="date" class="theme-input" required />
                <div class="flex min-h-[3.5rem] items-center gap-3 rounded-2xl border border-slate-300 px-4 py-3 text-sm md:col-span-2">
                    <input id="term-current" name="is_current" type="checkbox" value="1" class="rounded border-slate-300" />
                    <label for="term-current">Set as current term</label>
                </div>
                <button type="submit" class="theme-button md:col-span-2">Create term</button>
            </form>
        </section>
        @endif

        @if (in_array($activeAcademicSection, ['session-setup', 'term-setup'], true))
        </div>
        @endif

    @if ($activeAcademicSection === 'session-rollover')
    <section class="section-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Session status and rollover</h2>
                <p class="mt-2 text-sm text-slate-500">Close a finished session first. After you create and mark the next session as current, review each student and either promote them to the next class or keep them in the same class for the new session.</p>
            </div>
            @if ($currentSession)
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900">
                    <div class="text-xs uppercase tracking-[0.24em] text-emerald-700">Current session</div>
                    <div class="mt-2 font-semibold">{{ $currentSession->name }}</div>
                </div>
            @endif
        </div>

        <div class="mt-6 grid gap-4 xl:grid-cols-2">
            @foreach ($sessions as $session)
                <article class="rounded-3xl border border-slate-200 px-5 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="font-semibold text-slate-900">{{ $session->name }}</div>
                            <div class="mt-2 text-sm text-slate-500">
                                {{ $session->start_date->format('M j, Y') }} to {{ $session->end_date->format('M j, Y') }}
                            </div>
                            <div class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-500">
                                Pass mark {{ number_format((float) $session->promotion_pass_mark, 2) }}%
                                @if ($session->is_current)
                                    | Current
                                @endif
                                @if ($session->closed_at)
                                    | Closed {{ $session->closed_at->format('M j, Y g:i A') }}
                                @endif
                            </div>
                            @if ($session->closedByUser)
                                <div class="mt-2 text-sm text-slate-500">Approved by {{ $session->closedByUser->fullName() }}</div>
                            @endif
                        </div>

                        @if (! $session->closed_at)
                            <form method="POST" action="{{ route('admin.sessions.close', $session) }}" class="w-full max-w-xs space-y-3">
                                @csrf
                                @method('PATCH')
                                <input name="promotion_pass_mark" type="number" step="0.01" min="0" max="100" value="{{ old('promotion_pass_mark', $session->promotion_pass_mark) }}" class="theme-input w-full" required />
                                <button type="submit" class="theme-button-secondary w-full border-amber-300 text-amber-800" onclick="return confirm('Close this academic session? Students will remain in this session until you process promotions into the new current session.');">
                                    Approve and close session
                                </button>
                            </form>
                        @else
                            <div class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Session closed</div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
    @endif

    @if ($activeAcademicSection === 'promotion-review' && $promotionSourceSession && $currentSession)
        <section class="section-card">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">Promotion review</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Source session: {{ $promotionSourceSession->name }}.
                        Target session: {{ $currentSession->name }}.
                        Overall percentage is the total of subject percentages divided by the number of subjects offered in the student's class for the closed session.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm">
                        <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Students</div>
                        <div class="mt-2 text-xl font-bold text-slate-950">{{ $promotionSummary['students'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm">
                        <div class="text-xs uppercase tracking-[0.24em] text-emerald-700">Recommended promote</div>
                        <div class="mt-2 text-xl font-bold text-emerald-900">{{ $promotionSummary['recommended_promotions'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm">
                        <div class="text-xs uppercase tracking-[0.24em] text-amber-700">Recommended repeat</div>
                        <div class="mt-2 text-xl font-bold text-amber-900">{{ $promotionSummary['recommended_repeats'] }}</div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.sessions.promotions.process') }}" class="mt-6 space-y-6">
                @csrf
                <input type="hidden" name="source_session_id" value="{{ $promotionSourceSession->id }}" />
                <input type="hidden" name="target_session_id" value="{{ $currentSession->id }}" />

                @foreach ($promotionPreviewByClass as $className => $rows)
                    <div class="rounded-3xl border border-slate-200">
                        <div class="border-b border-slate-200 px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $className }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ $rows->count() }} student(s)</div>
                        </div>

                        <div class="desktop-table table-wrap">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50">
                                    <tr class="text-left text-slate-500">
                                        <th class="px-5 py-4 font-semibold">Student</th>
                                        <th class="px-5 py-4 font-semibold">Subjects</th>
                                        <th class="px-5 py-4 font-semibold">Overall %</th>
                                        <th class="px-5 py-4 font-semibold">Recommendation</th>
                                        <th class="px-5 py-4 font-semibold">Admin decision</th>
                                        <th class="px-5 py-4 font-semibold">Target class</th>
                                        <th class="px-5 py-4 font-semibold">Note</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach ($rows as $row)
                                        @php
                                            $student = $row['student'];
                                            $recommendedPromote = $row['recommended_status'] === 'promote';
                                            $selectedClassId = old("target_class_ids.{$student->id}", $recommendedPromote ? $row['recommended_next_class']?->id : $row['current_class']?->id);
                                        @endphp
                                        <tr>
                                            <td class="px-5 py-4 align-top">
                                                <div class="font-semibold text-slate-900">{{ $student->user->fullName() }}</div>
                                                <div class="mt-1 text-xs uppercase tracking-[0.2em] text-slate-500">{{ $student->admission_no }}</div>
                                            </td>
                                            <td class="px-5 py-4 align-top text-slate-600">{{ $row['subject_count'] }}</td>
                                            <td class="px-5 py-4 align-top">
                                                <div class="font-semibold text-slate-900">{{ number_format((float) $row['overall_percentage'], 2) }}%</div>
                                                <div class="mt-1 text-xs text-slate-500">Pass mark {{ number_format((float) $row['promotion_threshold'], 2) }}%</div>
                                            </td>
                                            <td class="px-5 py-4 align-top">
                                                <div class="rounded-full {{ $recommendedPromote ? 'border border-emerald-300 text-emerald-700' : 'border border-amber-300 text-amber-700' }} px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">
                                                    {{ $recommendedPromote ? 'Promote' : 'Repeat' }}
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 align-top">
                                                <select name="decisions[{{ $student->id }}]" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                                                    <option value="promote" @selected(old("decisions.{$student->id}", $row['recommended_status']) === 'promote')>Promote</option>
                                                    <option value="repeat" @selected(old("decisions.{$student->id}", $row['recommended_status']) === 'repeat')>Repeat class</option>
                                                </select>
                                            </td>
                                            <td class="px-5 py-4 align-top">
                                                <select name="target_class_ids[{{ $student->id }}]" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                                                    <option value="">Select class</option>
                                                    @foreach ($classes as $class)
                                                        <option value="{{ $class->id }}" @selected((string) $selectedClassId === (string) $class->id)>
                                                            {{ $class->display_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if ($row['recommended_next_class'])
                                                    <div class="mt-2 text-xs text-slate-500">Suggested next class: {{ $row['recommended_next_class']->display_name }}</div>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 align-top">
                                                <input
                                                    name="notes[{{ $student->id }}]"
                                                    value="{{ old("notes.{$student->id}") }}"
                                                    placeholder="Optional note"
                                                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm"
                                                />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mobile-record-list p-5">
                            @foreach ($rows as $row)
                                @php
                                    $student = $row['student'];
                                    $recommendedPromote = $row['recommended_status'] === 'promote';
                                    $selectedClassId = old("target_class_ids.{$student->id}", $recommendedPromote ? $row['recommended_next_class']?->id : $row['current_class']?->id);
                                @endphp
                                <article class="mobile-record-card">
                                    <div class="mobile-record-title">{{ $student->user->fullName() }}</div>
                                    <div class="mobile-record-subtitle">{{ $student->admission_no }}</div>
                                    <div class="mobile-record-grid mt-4">
                                        <div class="mobile-record-item">
                                            <div class="mobile-record-label">Subjects</div>
                                            <div class="mobile-record-value">{{ $row['subject_count'] }}</div>
                                        </div>
                                        <div class="mobile-record-item">
                                            <div class="mobile-record-label">Overall %</div>
                                            <div class="mobile-record-value">{{ number_format((float) $row['overall_percentage'], 2) }}%</div>
                                        </div>
                                        <div class="mobile-record-item">
                                            <div class="mobile-record-label">Recommendation</div>
                                            <div class="mobile-record-value">{{ $recommendedPromote ? 'Promote' : 'Repeat' }}</div>
                                        </div>
                                        <div class="mobile-record-item md:col-span-2">
                                            <div class="mobile-record-label">Admin decision</div>
                                            <select name="decisions[{{ $student->id }}]" class="theme-input mt-2 w-full">
                                                <option value="promote" @selected(old("decisions.{$student->id}", $row['recommended_status']) === 'promote')>Promote</option>
                                                <option value="repeat" @selected(old("decisions.{$student->id}", $row['recommended_status']) === 'repeat')>Repeat class</option>
                                            </select>
                                        </div>
                                        <div class="mobile-record-item md:col-span-2">
                                            <div class="mobile-record-label">Target class</div>
                                            <select name="target_class_ids[{{ $student->id }}]" class="theme-input mt-2 w-full">
                                                <option value="">Select class</option>
                                                @foreach ($classes as $class)
                                                    <option value="{{ $class->id }}" @selected((string) $selectedClassId === (string) $class->id)>
                                                        {{ $class->display_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if ($row['recommended_next_class'])
                                                <div class="mt-2 text-xs text-slate-500">Suggested next class: {{ $row['recommended_next_class']->display_name }}</div>
                                            @endif
                                        </div>
                                        <div class="mobile-record-item md:col-span-2">
                                            <div class="mobile-record-label">Note</div>
                                            <input
                                                name="notes[{{ $student->id }}]"
                                                value="{{ old("notes.{$student->id}") }}"
                                                placeholder="Optional note"
                                                class="theme-input mt-2 w-full"
                                            />
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @if ($promotionPreview->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No students remain in the closed session for promotion processing.</div>
                @else
                    <button type="submit" class="theme-button">Continue students into {{ $currentSession->name }}</button>
                @endif
            </form>
        </section>
    @elseif ($activeAcademicSection === 'promotion-review' && $sessions->contains(fn ($session) => $session->closed_at !== null))
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Promotion review</h2>
            <p class="mt-3 text-sm text-slate-500">A session has been closed, but there is no different current session yet. Create the next session and set it as current before processing promotions.</p>
        </section>
    @endif

    @if (in_array($activeAcademicSection, ['class-setup', 'subject-setup'], true))
    <div class="grid gap-8 xl:grid-cols-2">
    @endif
        @if ($activeAcademicSection === 'class-setup')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Class setup</h2>
            <form method="POST" action="{{ route('admin.classes.store') }}" class="mt-6 grid gap-4 md:grid-cols-2">
                @csrf
                <input name="name" placeholder="SS 1" class="theme-input" required />
                <input name="section" placeholder="Science / Arts / General" class="theme-input" />
                <select name="class_teacher_id" class="theme-input">
                    <option value="">Class teacher</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->fullName() }}</option>
                    @endforeach
                </select>
                <input name="capacity" type="number" min="1" placeholder="Capacity" class="theme-input" />
                <input name="room" placeholder="Room" class="theme-input" />
                <textarea name="description" rows="3" placeholder="Description" class="theme-input md:col-span-2"></textarea>
                <button type="submit" class="theme-button md:col-span-2">Create class</button>
            </form>

            <div class="mt-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="display-font text-xl font-bold text-slate-950">Existing class teacher accounts</h3>
                        <p class="mt-2 text-sm text-slate-500">Assign one class teacher account to each class. That teacher will use the teaching workspace to manage attendance, classwork, assignments, assessments, exams, and student results for the class.</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($classes as $schoolClass)
                        <form method="POST" action="{{ route('admin.classes.update', $schoolClass) }}" class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                            @csrf
                            @method('PATCH')

                            <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="display-font text-xl font-bold text-slate-950">{{ $schoolClass->display_name }}</div>
                                    <div class="mt-1 text-sm text-slate-500">Current class teacher: {{ $schoolClass->classTeacher?->fullName() ?? 'Not assigned yet' }}</div>
                                </div>
                                <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                    Room {{ $schoolClass->room ?: 'Not set' }} | Capacity {{ $schoolClass->capacity ?: 'Not set' }}
                                </div>
                            </div>

                            <div class="mt-5 grid gap-4 md:grid-cols-2">
                                <input name="name" value="{{ old('name', $schoolClass->name) }}" class="theme-input" required />
                                <input name="section" value="{{ old('section', $schoolClass->section) }}" class="theme-input" placeholder="Section" />
                                <select name="class_teacher_id" class="theme-input">
                                    <option value="">Class teacher</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" @selected((string) old('class_teacher_id', $schoolClass->class_teacher_id) === (string) $teacher->id)>{{ $teacher->fullName() }}</option>
                                    @endforeach
                                </select>
                                <input name="capacity" type="number" min="1" value="{{ old('capacity', $schoolClass->capacity) }}" class="theme-input" placeholder="Capacity" />
                                <input name="room" value="{{ old('room', $schoolClass->room) }}" class="theme-input" placeholder="Room" />
                                <textarea name="description" rows="3" class="theme-input md:col-span-2" placeholder="Description">{{ old('description', $schoolClass->description) }}</textarea>
                            </div>

                            <div class="mt-5 flex justify-end">
                                <button type="submit" class="theme-button">Save class teacher</button>
                            </div>
                        </form>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No classes have been created yet.</div>
                    @endforelse
                </div>
            </div>
        </section>
        @endif

        @if ($activeAcademicSection === 'subject-setup')
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Subject setup</h2>
            <form method="POST" action="{{ route('admin.subjects.store') }}" class="mt-6 space-y-4">
                @csrf
                <input name="name" placeholder="Mathematics" class="theme-input w-full" required />
                <input name="code" placeholder="MTH101" class="theme-input w-full" />
                <textarea name="description" rows="4" placeholder="Subject description" class="theme-input w-full"></textarea>
                <button type="submit" class="theme-button">Create subject</button>
            </form>
        </section>
        @endif
    @if (in_array($activeAcademicSection, ['class-setup', 'subject-setup'], true))
    </div>
    @endif

    @if ($activeAcademicSection === 'announcement')
    <section class="section-card">
        <h2 class="display-font text-2xl font-bold text-slate-950">Website announcement</h2>
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="mt-6 space-y-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <input name="title" placeholder="Announcement title" class="theme-input" required />
                <input name="category" placeholder="news / event / update" class="theme-input" value="news" required />
            </div>
            <textarea name="excerpt" rows="2" placeholder="Short excerpt" class="theme-input w-full"></textarea>
            <textarea name="body" rows="5" placeholder="Announcement body" class="theme-input w-full" required></textarea>
            <label class="flex items-center gap-3 text-sm text-slate-600">
                <input type="checkbox" name="is_published" value="1" checked class="rounded border-slate-300" />
                Publish immediately
            </label>
            <button type="submit" class="theme-button">Publish announcement</button>
        </form>
    </section>
    @endif

    @if ($activeAcademicSection === 'cbt-control')
    <section class="section-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">CBT control room</h2>
                <p class="mt-2 text-sm text-slate-500">Turn school CBT on or off globally and activate or deactivate each CBT exam whenever the school is ready.</p>
            </div>
            <form method="POST" action="{{ route('admin.cbt.toggle') }}" class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-5">
                @csrf
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">School CBT</div>
                <div class="mt-3 text-lg font-semibold text-slate-900">{{ $cbtEnabled ? 'Enabled' : 'Disabled' }}</div>
                <input type="hidden" name="enabled" value="{{ $cbtEnabled ? 0 : 1 }}" />
                <button type="submit" class="mt-4 rounded-full {{ $cbtEnabled ? 'border border-rose-300 text-rose-700' : 'bg-slate-900 text-white' }} px-5 py-3 text-sm font-semibold">
                    {{ $cbtEnabled ? 'Turn CBT off' : 'Turn CBT on' }}
                </button>
            </form>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($cbtAssessments as $cbtAssessment)
                <article class="rounded-3xl border border-slate-200 px-5 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="font-semibold text-slate-900">{{ $cbtAssessment->title }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ $cbtAssessment->teacher->fullName() }} | {{ $cbtAssessment->subject->name }} | {{ $cbtAssessment->schoolClass->display_name }}</div>
                            <div class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-500">
                                {{ $cbtAssessment->cbtQuestions_count }} question(s)
                                |
                                {{ $cbtAssessment->cbtAttempts_count }} attempt(s)
                                |
                                {{ $cbtAssessment->cbt_is_active ? 'Live' : 'Offline' }}
                            </div>
                            @if ($cbtAssessment->cbt_starts_at)
                                <div class="mt-2 text-sm text-slate-600">Starts {{ $cbtAssessment->cbt_starts_at->format('M j, Y g:i A') }}</div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.cbt.assessments.toggle', $cbtAssessment) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-full {{ $cbtAssessment->cbt_is_active ? 'border border-rose-300 text-rose-700' : 'bg-slate-900 text-white' }} px-5 py-3 text-sm font-semibold">
                                {{ $cbtAssessment->cbt_is_active ? 'Deactivate exam' : 'Activate exam' }}
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No CBT assessments have been created yet.</div>
            @endforelse
        </div>
    </section>
    @endif
    </div>
</x-app-layout>
