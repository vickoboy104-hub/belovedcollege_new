<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Teacher Subject Access"
            eyebrow="Academic Permissions"
            description="Grant or remove exact subject-and-class permissions. Teachers only see and manage the combinations listed here."
        >
            <x-slot name="actions">
                <x-action-button variant="secondary" :href="route('admin.academics', ['section' => 'class-setup'])">
                    Class Teacher Setup
                </x-action-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    @php
        $oldTeacherIds = collect(old('teacher_ids', []))->map(fn ($id) => (string) $id);
        $oldClassIds = collect(old('school_class_ids', []))->map(fn ($id) => (string) $id);
        $oldSubjectIds = collect(old('subject_ids', []))->map(fn ($id) => (string) $id);
    @endphp

    <div class="grid gap-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-stat-card label="Active permissions" :value="$activeCount" accent="blue" icon="learning" />
            <x-stat-card label="Teachers with access" :value="$teacherCount" accent="green" icon="staff" />
        </div>

        <x-form-card
            :action="route('admin.teacher-access.store')"
            method="POST"
            title="Grant teaching permissions"
            description="Choose one or many teachers, one or many classes, and multiple subjects. Every selected combination takes effect immediately."
            x-data="{ allTeachers: {{ old('all_teachers') ? 'true' : 'false' }} }"
        >
            <div class="grid gap-5 xl:grid-cols-3">
                <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-900">Teachers</h4>
                            <p class="mt-1 text-xs text-slate-500">Select individual teachers or apply the permission to every active teacher.</p>
                        </div>
                        <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-slate-500 shadow-sm">{{ $teachers->count() }}</span>
                    </div>

                    <label class="mb-3 flex cursor-pointer items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2.5 text-xs font-bold text-blue-900">
                        <input
                            type="checkbox"
                            name="all_teachers"
                            value="1"
                            x-model="allTeachers"
                            class="rounded border-blue-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span>Select every active teacher</span>
                    </label>

                    <div class="max-h-72 space-y-2 overflow-y-auto pr-1" :class="allTeachers ? 'opacity-50' : ''">
                        @forelse ($teachers as $teacher)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 hover:border-blue-300">
                                <input
                                    type="checkbox"
                                    name="teacher_ids[]"
                                    value="{{ $teacher->id }}"
                                    :disabled="allTeachers"
                                    @checked($oldTeacherIds->contains((string) $teacher->id))
                                    class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="min-w-0">
                                    <span class="block truncate text-xs font-extrabold text-slate-900">{{ $teacher->fullName() }}</span>
                                    <span class="mt-0.5 block truncate text-[11px] text-slate-500">{{ $teacher->staffProfile?->employee_no ?: $teacher->email }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-300 bg-white p-4 text-center text-xs text-slate-500">No active teachers are available.</p>
                        @endforelse
                    </div>
                    <x-input-error :messages="$errors->get('teacher_ids')" class="mt-2" />
                    <x-input-error :messages="$errors->get('teacher_ids.*')" class="mt-2" />
                </section>

                <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-900">Classes</h4>
                            <p class="mt-1 text-xs text-slate-500">Choose the class groups where the selected teachers may work.</p>
                        </div>
                        <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-slate-500 shadow-sm">{{ $classes->count() }}</span>
                    </div>

                    <div class="max-h-72 space-y-2 overflow-y-auto pr-1">
                        @forelse ($classes as $schoolClass)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 hover:border-blue-300">
                                <input
                                    type="checkbox"
                                    name="school_class_ids[]"
                                    value="{{ $schoolClass->id }}"
                                    @checked($oldClassIds->contains((string) $schoolClass->id))
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="text-xs font-bold text-slate-800">{{ $schoolClass->display_name }}</span>
                            </label>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-300 bg-white p-4 text-center text-xs text-slate-500">No classes have been created.</p>
                        @endforelse
                    </div>
                    <x-input-error :messages="$errors->get('school_class_ids')" class="mt-2" />
                    <x-input-error :messages="$errors->get('school_class_ids.*')" class="mt-2" />
                </section>

                <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-900">Subjects</h4>
                            <p class="mt-1 text-xs text-slate-500">Select as many subjects as the teacher or teacher group should manage.</p>
                        </div>
                        <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-slate-500 shadow-sm">{{ $subjects->count() }}</span>
                    </div>

                    <div class="max-h-72 space-y-2 overflow-y-auto pr-1">
                        @forelse ($subjects as $subject)
                            <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 hover:border-blue-300">
                                <span class="flex min-w-0 items-center gap-3">
                                    <input
                                        type="checkbox"
                                        name="subject_ids[]"
                                        value="{{ $subject->id }}"
                                        @checked($oldSubjectIds->contains((string) $subject->id))
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="truncate text-xs font-bold text-slate-800">{{ $subject->name }}</span>
                                </span>
                                <span class="shrink-0 text-[10px] font-bold text-slate-400">{{ $subject->code ?: 'No code' }}</span>
                            </label>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-300 bg-white p-4 text-center text-xs text-slate-500">No subjects have been created.</p>
                        @endforelse
                    </div>
                    <x-input-error :messages="$errors->get('subject_ids')" class="mt-2" />
                    <x-input-error :messages="$errors->get('subject_ids.*')" class="mt-2" />
                </section>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold leading-relaxed text-amber-900">
                The system grants every selected teacher × class × subject combination. Existing active permissions are kept without duplication.
            </div>

            <x-slot name="actions">
                <x-action-button type="submit" variant="success" icon="save">Grant Selected Access</x-action-button>
            </x-slot>
        </x-form-card>

        <x-filter-card
            title="Current teacher permissions"
            description="Search by teacher, email, class, subject, or subject code. Revoked permissions can be restored without recreating them."
        >
            <form method="GET" action="{{ route('admin.teacher-access.index') }}" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_12rem_auto] md:items-end">
                <div class="space-y-1.5">
                    <label for="teacher-access-search" class="text-xs font-bold text-slate-700">Search</label>
                    <input id="teacher-access-search" name="search" value="{{ $search }}" class="theme-input w-full" placeholder="Teacher, class, subject, or code" />
                </div>
                <div class="space-y-1.5">
                    <label for="teacher-access-status" class="text-xs font-bold text-slate-700">Status</label>
                    <select id="teacher-access-status" name="status" class="theme-input w-full">
                        <option value="active" @selected($statusFilter === 'active')>Active</option>
                        <option value="revoked" @selected($statusFilter === 'revoked')>Revoked</option>
                        <option value="all" @selected($statusFilter === 'all')>All</option>
                    </select>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-action-button type="submit" variant="primary">Filter</x-action-button>
                    <x-action-button variant="secondary" :href="route('admin.teacher-access.index')">Reset</x-action-button>
                </div>
            </form>
        </x-filter-card>

        <x-dashboard-card title="Permission register" subtitle="Select any visible rows and remove or restore them together. Every row remains an exact teacher, class, and subject authorization." icon="learning" accent="blue">
            <form
                id="teacher-access-bulk-form"
                method="POST"
                action="{{ route('admin.teacher-access.bulk') }}"
                class="mb-4 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3"
                onsubmit="return confirm((event.submitter?.value === 'revoke' ? 'Remove' : 'Restore') + ' all selected teacher permissions?');"
            >
                @csrf
                @method('PATCH')
                <span class="mr-auto text-xs font-bold text-slate-600">Bulk action for checked rows</span>
                <button type="submit" name="action" value="revoke" class="table-delete-btn whitespace-nowrap">Remove Selected</button>
                <button type="submit" name="action" value="restore" class="table-toggle-btn whitespace-nowrap">Restore Selected</button>
            </form>
            <x-input-error :messages="$errors->get('assignment_ids')" class="mb-3" />

            <x-data-table :headers="['<input type=&quot;checkbox&quot; aria-label=&quot;Select all visible permissions&quot; data-teacher-access-select-all>', 'Teacher', 'Class', 'Subject', 'Granted by', 'Status', 'Actions']" minWidth="1080px" :stickyEdges="false">
                @forelse ($assignments as $assignment)
                    <tr>
                        <td class="w-12 text-center">
                            <input
                                type="checkbox"
                                name="assignment_ids[]"
                                value="{{ $assignment->id }}"
                                form="teacher-access-bulk-form"
                                aria-label="Select {{ $assignment->teacher->fullName() }} {{ $assignment->subject->name }} permission"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                data-teacher-access-row
                            />
                        </td>
                        <td>
                            <div class="table-person">
                                <div class="table-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($assignment->teacher->first_name ?: $assignment->teacher->name, 0, 1).\Illuminate\Support\Str::substr($assignment->teacher->last_name ?: '', 0, 1)) }}</div>
                                <div class="table-person-text">
                                    <strong>{{ $assignment->teacher->fullName() }}</strong>
                                    <span>{{ $assignment->teacher->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $assignment->schoolClass->display_name }}</td>
                        <td>
                            <div class="font-bold text-slate-900">{{ $assignment->subject->name }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $assignment->subject->code ?: 'No code' }}</div>
                        </td>
                        <td>
                            <div class="font-semibold text-slate-800">{{ $assignment->assignedByUser?->fullName() ?? 'System' }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $assignment->assigned_at?->format('M j, Y g:i A') ?? $assignment->created_at?->format('M j, Y g:i A') }}</div>
                        </td>
                        <td>
                            <x-status-badge :status="$assignment->is_active ? 'active' : 'inactive'" :label="$assignment->is_active ? 'Active' : 'Revoked'" />
                            @if (! $assignment->is_active && $assignment->revoked_at)
                                <div class="mt-1 text-[11px] text-slate-500">{{ $assignment->revoked_at->format('M j, Y') }}</div>
                            @endif
                        </td>
                        <td>
                            @if ($assignment->is_active)
                                <form method="POST" action="{{ route('admin.teacher-access.revoke', $assignment) }}" onsubmit="return confirm('Remove this teacher’s access to {{ addslashes($assignment->subject->name) }} in {{ addslashes($assignment->schoolClass->display_name) }}?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="table-delete-btn whitespace-nowrap">Remove Access</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.teacher-access.restore', $assignment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="table-toggle-btn whitespace-nowrap">Restore Access</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state
                                title="No teacher permissions found"
                                description="Grant teachers access to classes and subjects using the form above."
                                icon="learning"
                            />
                        </td>
                    </tr>
                @endforelse
            </x-data-table>

            @if ($assignments->hasPages())
                <div class="mt-5">{{ $assignments->links() }}</div>
            @endif
        </x-dashboard-card>
    </div>

    <script>
        (() => {
            const selectAll = document.querySelector('[data-teacher-access-select-all]');
            const rows = Array.from(document.querySelectorAll('[data-teacher-access-row]'));

            if (!selectAll || rows.length === 0) return;

            selectAll.addEventListener('change', () => {
                rows.forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                });
            });

            rows.forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    const checkedCount = rows.filter((item) => item.checked).length;
                    selectAll.checked = checkedCount === rows.length;
                    selectAll.indeterminate = checkedCount > 0 && checkedCount < rows.length;
                });
            });
        })();
    </script>
</x-app-layout>
