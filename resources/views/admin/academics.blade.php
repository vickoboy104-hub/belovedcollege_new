<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Academic structure and publishing" eyebrow="Administration" description="Manage sessions, terms, student rollovers, promotions, class allocations, subjects, and digital CBT settings." />
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
        <!-- 1. ACADEMIC SESSION SETUP -->
        @if ($activeAcademicSection === 'session-setup')
            <div class="max-w-2xl mx-auto w-full">
                <x-form-card :action="route('admin.sessions.store')" method="POST" title="Academic session" description="Create a new academic session, set active dates, and define promotion thresholds.">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Session Name <span class="text-rose-500">*</span></label>
                            <input name="name" placeholder="e.g. 2026/2027" class="theme-input w-full" required />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Promotion Pass Mark (%) <span class="text-rose-500">*</span></label>
                            <input name="promotion_pass_mark" type="number" step="0.01" min="0" max="100" value="{{ old('promotion_pass_mark', 50) }}" placeholder="e.g. 50" class="theme-input w-full" required />
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-700">Session Dates</label>
                            <div class="grid gap-3 md:grid-cols-2">
                                <input name="start_date" type="date" class="theme-input w-full" required />
                                <input name="end_date" type="date" class="theme-input w-full" required />
                            </div>
                        </div>
                        <div class="md:col-span-2 flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3 text-xs font-semibold text-slate-650">
                            <input id="session-current" name="is_current" type="checkbox" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                            <label for="session-current" class="cursor-pointer">Set as current active academic session</label>
                        </div>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="success">Create Session</x-action-button>
                    </x-slot>
                </x-form-card>
            </div>
        @endif

        <!-- 2. TERM SETUP -->
        @if ($activeAcademicSection === 'term-setup')
            <div class="max-w-2xl mx-auto w-full">
                <x-form-card :action="route('admin.terms.store')" method="POST" title="Create term" description="Establish a new educational term under an active academic session.">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-700">Academic Session <span class="text-rose-500">*</span></label>
                            <select name="academic_session_id" class="theme-input w-full" required>
                                <option value="">Select session</option>
                                @foreach ($sessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-700">Term Name <span class="text-rose-500">*</span></label>
                            <input name="name" placeholder="e.g. First Term" class="theme-input w-full" required />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Start Date <span class="text-rose-500">*</span></label>
                            <input name="start_date" type="date" class="theme-input w-full" required />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">End Date <span class="text-rose-500">*</span></label>
                            <input name="end_date" type="date" class="theme-input w-full" required />
                        </div>
                        <div class="md:col-span-2 flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3 text-xs font-semibold text-slate-655">
                            <input id="term-current" name="is_current" type="checkbox" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                            <label for="term-current" class="cursor-pointer">Set as current active term</label>
                        </div>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="success">Create Term</x-action-button>
                    </x-slot>
                </x-form-card>
            </div>
        @endif

        <!-- 3. SESSION STATUS & ROLLOVER -->
        @if ($activeAcademicSection === 'session-rollover')
            <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)]">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between border-b border-slate-100 pb-5 mb-6">
                    <div>
                        <h2 class="display-font text-xl font-bold text-slate-900 leading-snug">Session status and rollover</h2>
                        <p class="text-xs font-semibold text-slate-500 mt-1 max-w-2xl leading-relaxed">
                            To roll over, first approve and close the finished session. Once the next session is set as current, process student promotions into their target classes.
                        </p>
                    </div>
                    @if ($currentSession)
                        <div class="bg-emerald-50 text-emerald-800 px-4 py-3 rounded-2xl border border-emerald-100 shrink-0 text-center">
                            <div class="text-[9px] font-extrabold uppercase tracking-wider text-emerald-600">Current active session</div>
                            <div class="display-font text-lg font-black mt-0.5">{{ $currentSession->name }}</div>
                        </div>
                    @endif
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($sessions as $session)
                        <div class="card bg-white border border-slate-200/80 rounded-[18px] p-5 shadow-sm">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-1">
                                    <div class="font-extrabold text-slate-900 text-lg leading-tight flex items-center gap-2">
                                        {{ $session->name }}
                                        @if ($session->is_current)
                                            <x-status-badge status="Active" class="scale-75 origin-left" />
                                        @endif
                                    </div>
                                    <div class="text-xs font-semibold text-slate-400">
                                        {{ $session->start_date->format('M j, Y') }} &mdash; {{ $session->end_date->format('M j, Y') }}
                                    </div>
                                    <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-450 pt-2 block">
                                        Pass mark {{ number_format((float) $session->promotion_pass_mark, 2) }}%
                                        @if ($session->closed_at)
                                            &bull; Closed {{ $session->closed_at->format('M j, Y g:i A') }}
                                        @endif
                                    </div>
                                    @if ($session->closedByUser)
                                        <div class="text-[10px] font-semibold text-slate-500 mt-2">Approved by: {{ $session->closedByUser->fullName() }}</div>
                                    @endif
                                </div>

                                @if (! $session->closed_at)
                                    <form method="POST" action="{{ route('admin.sessions.close', $session) }}" class="w-full max-w-xs space-y-3">
                                        @csrf
                                        @method('PATCH')
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-bold text-slate-700">Promotion Threshold (%)</label>
                                            <input name="promotion_pass_mark" type="number" step="0.01" min="0" max="100" value="{{ old('promotion_pass_mark', $session->promotion_pass_mark) }}" class="theme-input w-full text-xs !py-2" required />
                                        </div>
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[12px] font-bold text-xs uppercase tracking-wider transition duration-200 border border-rose-205 bg-rose-50 text-rose-700 hover:bg-rose-100/70 focus:outline-none" onclick="return confirm('Close this academic session? Students will remain in this session until you process promotions into the new current session.');">
                                            Approve & Close Session
                                        </button>
                                    </form>
                                @else
                                    <div class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-bold text-slate-500 shrink-0 uppercase tracking-wider">Session closed</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 4. STUDENT PROMOTIONS REVIEW -->
        @if ($activeAcademicSection === 'promotion-review' && $promotionSourceSession && $currentSession)
            <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)]">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between border-b border-slate-100 pb-5 mb-6">
                    <div>
                        <h2 class="display-font text-xl font-bold text-slate-900 leading-snug">Student promotion review dashboard</h2>
                        <p class="text-xs font-semibold text-slate-500 mt-1 max-w-3xl leading-relaxed">
                            Source session: {{ $promotionSourceSession->name }} &bull; Target session: {{ $currentSession->name }}. Student recommendations are calculated using cumulative score percentages.
                        </p>
                    </div>
                    <div class="grid gap-3 grid-cols-3 shrink-0">
                        <div class="bg-blue-50 text-blue-700 px-4 py-3 rounded-2xl border border-blue-100 text-center">
                            <div class="text-[9px] font-extrabold uppercase tracking-wider text-blue-500">Students</div>
                            <div class="display-font text-xl font-black mt-0.5">{{ $promotionSummary['students'] }}</div>
                        </div>
                        <div class="bg-emerald-50 text-emerald-800 px-4 py-3 rounded-2xl border border-emerald-100 text-center">
                            <div class="text-[9px] font-extrabold uppercase tracking-wider text-emerald-600">Promoted</div>
                            <div class="display-font text-xl font-black mt-0.5">{{ $promotionSummary['recommended_promotions'] }}</div>
                        </div>
                        <div class="bg-amber-50 text-amber-800 px-4 py-3 rounded-2xl border border-amber-100 text-center">
                            <div class="text-[9px] font-extrabold uppercase tracking-wider text-amber-600">Repeats</div>
                            <div class="display-font text-xl font-black mt-0.5">{{ $promotionSummary['recommended_repeats'] }}</div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.sessions.promotions.process') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="source_session_id" value="{{ $promotionSourceSession->id }}" />
                    <input type="hidden" name="target_session_id" value="{{ $currentSession->id }}" />

                    @foreach ($promotionPreviewByClass as $className => $rows)
                        <div class="card bg-white border border-slate-200 rounded-[18px] p-5 shadow-sm space-y-4">
                            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                                <div>
                                    <h3 class="display-font text-base font-extrabold text-slate-900">{{ $className }}</h3>
                                    <p class="text-xs font-semibold text-slate-400 mt-1">{{ $rows->count() }} student record{{ $rows->count() === 1 ? '' : 's' }}</p>
                                </div>
                                <x-status-badge status="Active" />
                            </div>

                            <x-data-table :headers="['Student', 'Subjects', 'Overall Score %', 'System recommendation', 'Admin Decision', 'Target Class Assignment', 'Action Notes']">
                                @foreach ($rows as $row)
                                    @php
                                        $student = $row['student'];
                                        $recommendedPromote = $row['recommended_status'] === 'promote';
                                        $selectedClassId = old("target_class_ids.{$student->id}", $recommendedPromote ? $row['recommended_next_class']?->id : $row['current_class']?->id);
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition duration-150">
                                        <td class="px-6 py-4 align-top">
                                            <div class="font-bold text-slate-900 text-xs">{{ $student->user->fullName() }}</div>
                                            <div class="text-[10px] font-semibold text-slate-400 mt-0.5 font-mono">{{ $student->admission_no }}</div>
                                        </td>
                                        <td class="px-6 py-4 align-top text-xs font-semibold text-slate-600">{{ $row['subject_count'] }}</td>
                                        <td class="px-6 py-4 align-top text-xs">
                                            <div class="font-bold text-slate-900">{{ number_format((float) $row['overall_percentage'], 2) }}%</div>
                                            <div class="text-[10px] font-semibold text-slate-400 mt-0.5">Threshold: {{ number_format((float) $row['promotion_threshold'], 2) }}%</div>
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <x-status-badge :status="$recommendedPromote ? 'Present' : 'Absent'" class="scale-90" />
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <select name="decisions[{{ $student->id }}]" class="theme-input text-xs !py-1.5 w-full">
                                                <option value="promote" @selected(old("decisions.{$student->id}", $row['recommended_status']) === 'promote')>Promote</option>
                                                <option value="repeat" @selected(old("decisions.{$student->id}", $row['recommended_status']) === 'repeat')>Repeat</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <select name="target_class_ids[{{ $student->id }}]" class="theme-input text-xs !py-1.5 w-full">
                                                <option value="">Select class</option>
                                                @foreach ($classes as $class)
                                                    <option value="{{ $class->id }}" @selected((string) $selectedClassId === (string) $class->id)>
                                                        {{ $class->display_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if ($row['recommended_next_class'])
                                                <div class="text-[10px] font-semibold text-blue-600 mt-1.5">Next suggested class: {{ $row['recommended_next_class']->display_name }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <input
                                                name="notes[{{ $student->id }}]"
                                                value="{{ old("notes.{$student->id}") }}"
                                                placeholder="Add review notes"
                                                class="theme-input text-xs !py-1.5 w-full"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </x-data-table>
                        </div>
                    @endforeach

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        @if ($promotionPreview->isEmpty())
                            <div class="rounded-xl border border-dashed border-slate-350 p-4 text-xs font-semibold text-slate-500 text-center w-full">No student records require promotion processing in this closed session.</div>
                        @else
                            <x-action-button type="submit" variant="success">Promote Eligible Students Into {{ $currentSession->name }}</x-action-button>
                        @endif
                    </div>
                </form>
            </div>
        @elseif ($activeAcademicSection === 'promotion-review' && $sessions->contains(fn ($session) => $session->closed_at !== null))
            <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)] max-w-2xl mx-auto">
                <x-empty-state title="Roll over targets unconfigured" description="A session has been closed, but there is no active target current session. Create a new session first." icon="classes" />
            </div>
        @endif

        <!-- 5. CLASS SETUP & TEACHER ALLOCATION -->
        @if ($activeAcademicSection === 'class-setup')
            <div class="grid gap-8 xl:grid-cols-[0.8fr,1.2fr]">
                <!-- Create Class Card -->
                <x-form-card :action="route('admin.classes.store')" method="POST" title="Class setup workspace" description="Add a new class index, assign teachers, room spaces, and student capacities." class="self-start">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-700">Class Name <span class="text-rose-500">*</span></label>
                            <input name="name" placeholder="e.g. SS 1" class="theme-input w-full" required />
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-700">Section Division</label>
                            <input name="section" placeholder="e.g. Science / Arts / General" class="theme-input w-full" />
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-700">Class Teacher Assignment</label>
                            <select name="class_teacher_id" class="theme-input w-full">
                                <option value="">Select class teacher</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->fullName() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Max Capacity</label>
                            <input name="capacity" type="number" min="1" placeholder="e.g. 40" class="theme-input w-full" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Room Location</label>
                            <input name="room" placeholder="e.g. Block C" class="theme-input w-full" />
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-700">Description</label>
                            <textarea name="description" rows="3" placeholder="Additional details..." class="theme-input w-full"></textarea>
                        </div>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="success">Create Class</x-action-button>
                    </x-slot>
                </x-form-card>

                <!-- Class List / Update Teachers Cards -->
                <div x-data="{ selectedClassId: '{{ $classes->first()?->id ?? '' }}' }" class="space-y-6">
                    <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)]">
                        <h3 class="display-font text-lg font-bold text-slate-900 leading-snug">Existing class setups</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-1 mb-4">Configure class directories, edit room slots, and update assigned educational managers.</p>
                        
                        <!-- Premium Class Selector Tabs -->
                        <div class="flex flex-wrap gap-2 pt-4 border-t border-slate-100">
                            @foreach ($classes as $schoolClass)
                                <button 
                                    type="button" 
                                    @click="selectedClassId = '{{ $schoolClass->id }}'"
                                    class="px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider transition duration-200 border"
                                    :class="selectedClassId === '{{ $schoolClass->id }}' 
                                        ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/20' 
                                        : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100 hover:text-slate-900'"
                                >
                                    {{ $schoolClass->display_name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @forelse ($classes as $schoolClass)
                        <div x-show="selectedClassId === '{{ $schoolClass->id }}'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                            <x-form-card :action="route('admin.classes.update', $schoolClass)" method="PATCH" title="{{ $schoolClass->display_name }}" description="Current Class Teacher: {{ $schoolClass->classTeacher?->fullName() ?? 'Not allocated' }}">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-slate-750">Class Name <span class="text-rose-500">*</span></label>
                                        <input name="name" value="{{ old('name', $schoolClass->name) }}" class="theme-input w-full" required />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-slate-750">Section</label>
                                        <input name="section" value="{{ old('section', $schoolClass->section) }}" class="theme-input w-full" placeholder="Section" />
                                    </div>
                                    <div class="space-y-1 md:col-span-2">
                                        <label class="text-xs font-bold text-slate-750">Class Teacher Allocation</label>
                                        <select name="class_teacher_id" class="theme-input w-full">
                                            <option value="">Class teacher</option>
                                            @foreach ($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" @selected((string) old('class_teacher_id', $schoolClass->class_teacher_id) === (string) $teacher->id)>{{ $teacher->fullName() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-slate-750">Capacity</label>
                                        <input name="capacity" type="number" min="1" value="{{ old('capacity', $schoolClass->capacity) }}" class="theme-input w-full" placeholder="Capacity" />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-slate-750">Room</label>
                                        <input name="room" value="{{ old('room', $schoolClass->room) }}" class="theme-input w-full" placeholder="Room" />
                                    </div>
                                    <div class="space-y-1 md:col-span-2">
                                        <label class="text-xs font-bold text-slate-750">Description</label>
                                        <textarea name="description" rows="3" class="theme-input w-full" placeholder="Description">{{ old('description', $schoolClass->description) }}</textarea>
                                    </div>
                                </div>
                                <x-slot name="actions">
                                    <x-action-button type="submit" variant="success">Save Class Details</x-action-button>
                                </x-slot>
                            </x-form-card>
                        </div>
                    @empty
                        <x-empty-state title="No active class records" description="No school classroom or grade records have been established." icon="classes" />
                    @endforelse
                </div>
            </div>
        @endif

        <!-- 6. SUBJECT SETUP -->
        @if ($activeAcademicSection === 'subject-setup')
            <div class="max-w-2xl mx-auto w-full">
                <x-form-card :action="route('admin.subjects.store')" method="POST" title="Subject setup workspace" description="Establish a new subject directory index in Beloved Schools.">
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Subject Name <span class="text-rose-500">*</span></label>
                            <input name="name" placeholder="e.g. Mathematics" class="theme-input w-full" required />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Subject Code</label>
                            <input name="code" placeholder="e.g. MTH101" class="theme-input w-full" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Description</label>
                            <textarea name="description" rows="4" placeholder="Course syllabus overview details..." class="theme-input w-full"></textarea>
                        </div>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="success">Create Subject Record</x-action-button>
                    </x-slot>
                </x-form-card>
            </div>
        @endif

        <!-- 7. ANNOUNCEMENT MANAGEMENT -->
        @if ($activeAcademicSection === 'announcement')
            <div class="max-w-3xl mx-auto w-full">
                <x-form-card :action="route('admin.announcements.store')" method="POST" title="Publish website announcement" description="Broadcast notices, news, and school events to parents and student dashboards immediately.">
                    <div class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700">Announcement Title <span class="text-rose-500">*</span></label>
                                <input name="title" placeholder="e.g. Inter-house sports day" class="theme-input w-full" required />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700">Category Tag <span class="text-rose-500">*</span></label>
                                <input name="category" placeholder="e.g. news / event / update" class="theme-input w-full" value="news" required />
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Short Summary Excerpt</label>
                            <textarea name="excerpt" rows="2" placeholder="Brief visual overview..." class="theme-input w-full"></textarea>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700">Announcement Body Contents <span class="text-rose-500">*</span></label>
                            <textarea name="body" rows="6" placeholder="Write announcement details..." class="theme-input w-full" required></textarea>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3 text-xs font-semibold text-slate-655">
                            <input type="checkbox" id="announce-publish" name="is_published" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                            <label for="announce-publish" class="cursor-pointer">Publish immediately onto active dashboards</label>
                        </div>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="success">Publish Announcement</x-action-button>
                    </x-slot>
                </x-form-card>
            </div>
        @endif

        <!-- 8. CBT全局控制与考试管理 -->
        @if ($activeAcademicSection === 'cbt-control')
            <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)]">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-5 mb-6">
                    <div>
                        <h2 class="display-font text-xl font-bold text-slate-900 leading-snug">Computer Based Testing (CBT) control room</h2>
                        <p class="text-xs font-semibold text-slate-500 mt-1 max-w-2xl leading-relaxed">
                            Toggle system-wide CBT examinations immediately. Deactivated exams will restrict student launches while live setups allow portal logins.
                        </p>
                    </div>
                    
                    <form method="POST" action="{{ route('admin.cbt.toggle') }}" class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 shrink-0 text-center flex flex-col justify-between items-center gap-2">
                        @csrf
                        <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Global System status</div>
                        <div class="font-extrabold text-sm text-slate-850 flex items-center gap-1.5 mt-0.5">
                            <span class="w-2.5 h-2.5 rounded-full {{ $cbtEnabled ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                            CBT System {{ $cbtEnabled ? 'LIVE' : 'OFFLINE' }}
                        </div>
                        <input type="hidden" name="enabled" value="{{ $cbtEnabled ? 0 : 1 }}" />
                        <x-action-button type="submit" :variant="$cbtEnabled ? 'danger' : 'accent'" class="!px-3 !py-1.5 !rounded-lg text-[10px] mt-2">
                            {{ $cbtEnabled ? 'Deactivate globally' : 'Activate globally' }}
                        </x-action-button>
                    </form>
                </div>

                <div>
                    <x-data-table :headers="['Assessment', 'Subject', 'Class', 'Teacher', 'Questions', 'Attempts', 'Status', 'Actions']">
                    @forelse ($cbtAssessments as $cbtAssessment)
                        @php
                            $cbtPreview = [
                                'type' => 'subject',
                                'title' => $cbtAssessment->title,
                                'subtitle' => ($cbtAssessment->cbt_is_active ? 'Live Assessment' : 'Offline Draft').' - '.($cbtAssessment->schoolClass->display_name ?? 'No class'),
                                'avatar' => 'CB',
                                'profileUrl' => route('admin.academics', ['section' => 'cbt-control']),
                                'ctaLabel' => 'View Full Details',
                                'fields' => [
                                    ['label' => 'Subject', 'value' => $cbtAssessment->subject->name ?? 'No subject'],
                                    ['label' => 'Class', 'value' => $cbtAssessment->schoolClass->display_name ?? 'No class'],
                                    ['label' => 'Teacher', 'value' => $cbtAssessment->teacher?->fullName() ?? 'No teacher'],
                                    ['label' => 'Questions', 'value' => $cbtAssessment->cbtQuestions_count.' question(s)'],
                                    ['label' => 'Attempts', 'value' => $cbtAssessment->cbtAttempts_count.' attempt(s)'],
                                    ['label' => 'Window Starts', 'value' => $cbtAssessment->cbt_starts_at?->format('M j, Y g:i A') ?? 'No start window'],
                                    ['label' => 'Status', 'value' => $cbtAssessment->cbt_is_active ? 'Live and accessible' : 'Offline draft'],
                                ],
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div class="table-person">
                                    <div class="table-avatar">CB</div>
                                    <div class="table-person-text">
                                        <strong>{{ $cbtAssessment->title }}</strong>
                                        <span>{{ $cbtAssessment->cbt_starts_at?->format('M j, Y g:i A') ?? 'No start window' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $cbtAssessment->subject->name ?? 'No subject' }}</td>
                            <td>{{ $cbtAssessment->schoolClass->display_name ?? 'No class' }}</td>
                            <td><span class="table-text-clip">{{ $cbtAssessment->teacher?->fullName() ?? 'No teacher' }}</span></td>
                            <td>{{ $cbtAssessment->cbtQuestions_count }}</td>
                            <td>{{ $cbtAssessment->cbtAttempts_count }}</td>
                            <td>
                                <x-status-badge
                                    :status="$cbtAssessment->cbt_is_active ? 'Active' : 'Inactive'"
                                    :label="$cbtAssessment->cbt_is_active ? 'Live' : 'Offline'"
                                />
                            </td>
                            <td>
                                <div class="table-action-group">
                                    <button type="button" class="table-view-btn" data-preview='@json($cbtPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                    <form method="POST" action="{{ route('admin.cbt.assessments.toggle', $cbtAssessment) }}" class="contents">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="{{ $cbtAssessment->cbt_is_active ? 'table-delete-btn' : 'table-toggle-btn' }}">
                                            {{ $cbtAssessment->cbt_is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state title="No active CBT assessments" description="Educational CBT templates or questions have not been set up yet." icon="cbt" />
                            </td>
                        </tr>
                    @endforelse
                    </x-data-table>
                </div>
            </div>
        @endif
    </div>
    <x-entity-preview-modal />
</x-app-layout>
