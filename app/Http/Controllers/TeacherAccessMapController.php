<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\TeacherAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherAccessMapController extends Controller
{
    public function __invoke(Request $request, TeacherAccessService $teacherAccess): JsonResponse
    {
        $user = $request->user();
        $privileged = $teacherAccess->isPrivileged($user);
        $assignments = $teacherAccess->activeAssignments($user);
        $classIds = $teacherAccess->classIds($user);
        $subjectIds = $teacherAccess->subjectIds($user);

        $classes = SchoolClass::query()
            ->when(! $privileged, fn ($query) => $query->whereIn('id', $classIds ?? collect()))
            ->orderBy('name')
            ->orderBy('section')
            ->get()
            ->map(fn (SchoolClass $schoolClass) => [
                'id' => $schoolClass->id,
                'label' => $schoolClass->display_name,
            ])
            ->values();
        $subjects = Subject::query()
            ->when(! $privileged, fn ($query) => $query->whereIn('id', $subjectIds ?? collect()))
            ->orderBy('name')
            ->get()
            ->map(fn (Subject $subject) => [
                'id' => $subject->id,
                'label' => $subject->name,
            ])
            ->values();

        $map = $privileged
            ? $classes->mapWithKeys(fn (array $schoolClass) => [
                $schoolClass['id'] => $subjects->pluck('id')->all(),
            ])->all()
            : $teacherAccess->classSubjectMap($user);

        return response()->json([
            'privileged' => $privileged,
            'has_access' => $privileged || $assignments->isNotEmpty(),
            'classes' => $classes,
            'subjects' => $subjects,
            'class_subject_map' => $map,
        ])->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
