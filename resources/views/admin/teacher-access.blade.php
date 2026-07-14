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

    <div class="grid gap-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-stat-card label="Active permissions" :value="$activeCount" accent="blue" icon="learning" />
            <x-stat-card label="Teachers with access" :value="$teacherCount" accent="green" icon="staff" />
        </div>

        <x-form-card
            :action="route('admin.teacher-access.store')"
            method="POST"
            title="Grant teaching permission"
            description="Select one teacher, one class, and one subject. The permission takes effect immediately."
        >
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="space-y-1.5">
                    <label for="teacher-access-teacher" class="text-xs font-bold text-slate-700">Teacher</label>
                    <select id="teacher-access-teacher" name="teacher_id" class="theme-input w-full" required>
                        <option value="">Choose teacher</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) old('teacher_id') === (string) $teacher->id)>
                                {{ $teacher->fullName() }}{{ $teacher->staffProfile?->employee_no ? ' — '.$teacher->staffProfile->employee_no : '' }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('teacher_id')" class="mt-1" />
                </div>

                <div class="space-y-1.5">
                    <label for="teacher-access-class" class="text-xs font-bold text-slate-700">Class</label>
                    <select id="teacher-access-class" name="school_class_id" class="theme-input w-full" required>
                        <option value="">Choose class</option>
                        @foreach ($classes as $schoolClass)
                            <option value="{{ $schoolClass->id }}" @selected((string) old('school_class_id') === (string) $schoolClass->id)>
                                {{ $schoolClass->display_name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('school_class_id')" class="mt-1" />
                </div>

                <div class="space-y-1.5">
                    <label for="teacher-access-subject" class="text-xs font-bold text-slate-700">Subject</label>
                    <select id="teacher-access-subject" name="subject_id" class="theme-input w-full" required>
                        <option value="">Choose subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>
                                {{ $subject->name }}{{ $subject->code ? ' — '.$subject->code : '' }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('subject_id')" class="mt-1" />
                </div>
            </div>

            <x-slot name="actions">
                <x-action-button type="submit" variant="success" icon="save">Grant Access</x-action-button>
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

        <x-dashboard-card title="Permission register" subtitle="Every row is an exact teacher, class, and subject authorization." icon="learning" accent="blue">
            <x-data-table :headers="['Teacher', 'Class', 'Subject', 'Granted by', 'Status', 'Actions']" minWidth="980px">
                @forelse ($assignments as $assignment)
                    <tr>
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
                        <td colspan="6">
                            <x-empty-state
                                title="No teacher permissions found"
                                description="Grant a teacher access to a class and subject using the form above."
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
</x-app-layout>
