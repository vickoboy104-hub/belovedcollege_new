<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Services\TeacherAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherAccessController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();
        $status = in_array($status, ['active', 'revoked', 'all'], true) ? $status : 'active';

        $assignments = TeacherSubjectAssignment::query()
            ->with(['teacher.staffProfile', 'schoolClass', 'subject', 'assignedByUser', 'revokedByUser'])
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'revoked', fn ($query) => $query->where('is_active', false))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->whereHas('teacher', fn ($teacherQuery) => $teacherQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('schoolClass', fn ($classQuery) => $classQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('section', 'like', "%{$search}%"))
                        ->orWhereHas('subject', fn ($subjectQuery) => $subjectQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->latest('assigned_at')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.teacher-access', [
            'teachers' => User::query()
                ->where('role', UserRole::Teacher->value)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'classes' => SchoolClass::query()->orderBy('name')->orderBy('section')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'assignments' => $assignments,
            'search' => $search,
            'statusFilter' => $status,
            'activeCount' => TeacherSubjectAssignment::query()->where('is_active', true)->count(),
            'teacherCount' => TeacherSubjectAssignment::query()->where('is_active', true)->distinct('teacher_id')->count('teacher_id'),
        ]);
    }

    public function store(Request $request, TeacherAccessService $teacherAccess): RedirectResponse
    {
        $allTeachers = $request->boolean('all_teachers');
        $teacherRule = Rule::exists('users', 'id')->where(fn ($query) => $query
            ->where('role', UserRole::Teacher->value)
            ->where('status', 'active'));

        $validated = $request->validate([
            'all_teachers' => ['nullable', 'boolean'],
            'teacher_id' => ['nullable', 'integer', $teacherRule],
            'teacher_ids' => ['nullable', 'array', 'max:250'],
            'teacher_ids.*' => ['integer', 'distinct', $teacherRule],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'school_class_ids' => ['nullable', 'array', 'max:50'],
            'school_class_ids.*' => ['integer', 'distinct', 'exists:school_classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'subject_ids' => ['nullable', 'array', 'max:100'],
            'subject_ids.*' => ['integer', 'distinct', 'exists:subjects,id'],
        ]);

        $teacherIds = $allTeachers
            ? User::query()
                ->where('role', UserRole::Teacher->value)
                ->where('status', 'active')
                ->pluck('id')
            : collect($validated['teacher_ids'] ?? [])
                ->push($validated['teacher_id'] ?? null)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

        $classIds = collect($validated['school_class_ids'] ?? [])
            ->push($validated['school_class_id'] ?? null)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $subjectIds = collect($validated['subject_ids'] ?? [])
            ->push($validated['subject_id'] ?? null)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $missing = [];
        if ($teacherIds->isEmpty()) {
            $missing['teacher_ids'] = 'Select at least one active teacher or choose every active teacher.';
        }
        if ($classIds->isEmpty()) {
            $missing['school_class_ids'] = 'Select at least one class.';
        }
        if ($subjectIds->isEmpty()) {
            $missing['subject_ids'] = 'Select at least one subject.';
        }
        if ($missing !== []) {
            throw ValidationException::withMessages($missing);
        }

        $combinationCount = $teacherIds->count() * $classIds->count() * $subjectIds->count();
        if ($combinationCount > 5000) {
            throw ValidationException::withMessages([
                'subject_ids' => 'This request would create more than 5,000 permissions. Select fewer teachers, classes, or subjects and try again.',
            ]);
        }

        $granted = 0;
        $alreadyActive = 0;

        DB::transaction(function () use ($request, $teacherIds, $classIds, $subjectIds, &$granted, &$alreadyActive): void {
            foreach ($teacherIds as $teacherId) {
                foreach ($classIds as $classId) {
                    foreach ($subjectIds as $subjectId) {
                        $assignment = TeacherSubjectAssignment::query()->firstOrNew([
                            'teacher_id' => $teacherId,
                            'school_class_id' => $classId,
                            'subject_id' => $subjectId,
                        ]);

                        if ($assignment->exists && $assignment->is_active) {
                            $alreadyActive++;
                            continue;
                        }

                        $assignment->fill([
                            'is_active' => true,
                            'assigned_by' => $request->user()->id,
                            'assigned_at' => now(),
                            'revoked_by' => null,
                            'revoked_at' => null,
                        ])->save();

                        $granted++;
                    }
                }
            }
        });

        $teacherIds->each(fn ($teacherId) => $teacherAccess->refresh(User::query()->findOrFail($teacherId)));

        $message = $granted === 1
            ? '1 teacher permission was granted successfully.'
            : number_format($granted).' teacher permissions were granted successfully.';

        if ($alreadyActive > 0) {
            $message .= ' '.number_format($alreadyActive).' selected permission(s) were already active.';
        }

        return back()->with('status', $message);
    }

    public function bulkUpdate(Request $request, TeacherAccessService $teacherAccess): RedirectResponse
    {
        $validated = $request->validate([
            'assignment_ids' => ['required', 'array', 'min:1', 'max:5000'],
            'assignment_ids.*' => ['integer', 'distinct', 'exists:teacher_subject_assignments,id'],
            'action' => ['required', Rule::in(['revoke', 'restore'])],
        ]);

        $assignments = TeacherSubjectAssignment::query()
            ->with('teacher')
            ->whereIn('id', $validated['assignment_ids'])
            ->get();

        $affected = 0;
        $teacherIds = collect();

        DB::transaction(function () use ($request, $assignments, $validated, &$affected, &$teacherIds): void {
            foreach ($assignments as $assignment) {
                $teacherIds->push($assignment->teacher_id);

                if ($validated['action'] === 'revoke') {
                    if (! $assignment->is_active) {
                        continue;
                    }

                    $assignment->update([
                        'is_active' => false,
                        'revoked_by' => $request->user()->id,
                        'revoked_at' => now(),
                    ]);
                } else {
                    if ($assignment->is_active) {
                        continue;
                    }

                    $assignment->update([
                        'is_active' => true,
                        'assigned_by' => $request->user()->id,
                        'assigned_at' => now(),
                        'revoked_by' => null,
                        'revoked_at' => null,
                    ]);
                }

                $affected++;
            }
        });

        $teacherIds->unique()->each(fn ($teacherId) => $teacherAccess->refresh(User::query()->findOrFail($teacherId)));

        $verb = $validated['action'] === 'revoke' ? 'removed' : 'restored';

        return back()->with('status', number_format($affected)." selected permission(s) were {$verb}.");
    }

    public function revoke(Request $request, TeacherSubjectAssignment $assignment, TeacherAccessService $teacherAccess): RedirectResponse
    {
        if (! $assignment->is_active) {
            return back()->with('status', 'That teacher permission is already revoked.');
        }

        $assignment->update([
            'is_active' => false,
            'revoked_by' => $request->user()->id,
            'revoked_at' => now(),
        ]);

        $teacherAccess->refresh($assignment->teacher);

        return back()->with('status', 'Teacher subject permission removed immediately.');
    }

    public function restore(Request $request, TeacherSubjectAssignment $assignment, TeacherAccessService $teacherAccess): RedirectResponse
    {
        $assignment->update([
            'is_active' => true,
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
            'revoked_by' => null,
            'revoked_at' => null,
        ]);

        $teacherAccess->refresh($assignment->teacher);

        return back()->with('status', 'Teacher subject permission restored.');
    }
}
