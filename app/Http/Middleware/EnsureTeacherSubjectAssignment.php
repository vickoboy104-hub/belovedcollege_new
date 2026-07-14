<?php

namespace App\Http\Middleware;

use App\Models\Assessment;
use App\Models\CbtAnswer;
use App\Models\CbtAttempt;
use App\Models\CbtQuestion;
use App\Services\TeacherAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacherSubjectAssignment
{
    public function __construct(protected TeacherAccessService $teacherAccess)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $this->teacherAccess->isPrivileged($user)) {
            return $next($request);
        }

        if ($request->routeIs('teacher.cbt.assessments.store')) {
            $classId = filter_var($request->input('school_class_id'), FILTER_VALIDATE_INT);
            $subjectId = filter_var($request->input('subject_id'), FILTER_VALIDATE_INT);

            if ($classId && $subjectId) {
                $this->teacherAccess->authorizePair($user, (int) $classId, (int) $subjectId);
            }

            return $next($request);
        }

        $assessment = $this->resolveAssessment($request);

        if ($assessment) {
            $this->teacherAccess->authorizePair(
                $user,
                (int) $assessment->school_class_id,
                (int) $assessment->subject_id,
            );
        }

        return $next($request);
    }

    protected function resolveAssessment(Request $request): ?Assessment
    {
        $assessment = $request->route('assessment');
        if ($assessment instanceof Assessment) {
            return $assessment;
        }

        $question = $request->route('question');
        if ($question instanceof CbtQuestion) {
            return $question->assessment;
        }

        $attempt = $request->route('attempt');
        if ($attempt instanceof CbtAttempt) {
            return $attempt->assessment;
        }

        $answer = $request->route('answer');
        if ($answer instanceof CbtAnswer) {
            return $answer->attempt?->assessment;
        }

        return null;
    }
}
