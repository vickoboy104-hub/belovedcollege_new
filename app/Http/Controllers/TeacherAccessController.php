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
use Illuminate\Validation\Rule;
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
        $validated = $request->validate([
            'teacher_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', UserRole::Teacher->value)
                    ->where('status', 'active')),
            ],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);

        $assignment = TeacherSubjectAssignment::query()->firstOrNew([
            'teacher_id' => $validated['teacher_id'],
            'school_class_id' => $validated['school_class_id'],
            'subject_id' => $validated['subject_id'],
        ]);

        $wasActive = $assignment->exists && $assignment->is_active;

        $assignment->fill([
            'is_active' => true,
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
            'revoked_by' => null,
            'revoked_at' => null,
        ])->save();

        $teacherAccess->refresh(User::query()->findOrFail($validated['teacher_id']));

        return back()->with('status', $wasActive
            ? 'That teacher already has this subject and class permission.'
            : 'Teacher subject permission granted successfully.');
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
