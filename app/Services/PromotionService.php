<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\Assessment;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    public function buildPromotionPreview(AcademicSession $sourceSession): Collection
    {
        $students = Student::query()
            ->with(['user', 'schoolClass'])
            ->where('academic_session_id', $sourceSession->id)
            ->orderBy('school_class_id')
            ->orderBy('admission_no')
            ->get();

        if ($students->isEmpty()) {
            return collect();
        }

        $classes = SchoolClass::query()->orderBy('name')->get()->keyBy('id');
        $classIds = $students->pluck('school_class_id')->filter()->unique()->values();

        $assessments = Assessment::query()
            ->with([
                'subject',
                'results' => fn ($query) => $query->whereIn('student_id', $students->pluck('id')),
            ])
            ->whereIn('school_class_id', $classIds)
            ->where($this->sessionAssessmentScope($sourceSession))
            ->get()
            ->groupBy('school_class_id');

        return $students->map(function (Student $student) use ($sourceSession, $assessments, $classes): array {
            $studentAssessments = $assessments->get($student->school_class_id, collect());
            $subjects = $studentAssessments
                ->filter(fn (Assessment $assessment) => $assessment->subject_id !== null)
                ->groupBy('subject_id');

            $subjectBreakdown = $subjects->map(function (Collection $subjectAssessments) use ($student): array {
                $subject = $subjectAssessments->first()?->subject;
                $possibleTotal = (float) $subjectAssessments->sum(fn (Assessment $assessment) => max((float) $assessment->total_score, 0));
                $studentScoreTotal = (float) $subjectAssessments->sum(function (Assessment $assessment) use ($student): float {
                    $result = $assessment->results->firstWhere('student_id', $student->id);

                    return $result ? (float) $result->score : 0;
                });

                $percentage = $possibleTotal > 0 ? round(($studentScoreTotal / $possibleTotal) * 100, 2) : 0.0;

                return [
                    'subject_id' => $subject?->id,
                    'subject_name' => $subject?->name ?? 'Unassigned',
                    'score_total' => $studentScoreTotal,
                    'possible_total' => $possibleTotal,
                    'percentage' => $percentage,
                ];
            })->values();

            $subjectCount = $subjectBreakdown->count();
            $subjectTotalPercentage = round((float) $subjectBreakdown->sum('percentage'), 2);
            $overallPercentage = $subjectCount > 0
                ? round($subjectTotalPercentage / $subjectCount, 2)
                : 0.0;
            $threshold = (float) ($sourceSession->promotion_pass_mark ?? 50);
            $recommendedStatus = $subjectCount > 0 && $overallPercentage >= $threshold ? 'promote' : 'repeat';
            $recommendedNextClass = $student->school_class_id
                ? $this->inferNextClass($classes->get($student->school_class_id), $classes)
                : null;

            return [
                'student' => $student,
                'current_class' => $student->schoolClass,
                'subject_breakdown' => $subjectBreakdown,
                'subject_count' => $subjectCount,
                'subject_total_percentage' => $subjectTotalPercentage,
                'overall_percentage' => $overallPercentage,
                'promotion_threshold' => $threshold,
                'recommended_status' => $recommendedStatus,
                'recommended_next_class' => $recommendedNextClass,
            ];
        });
    }

    protected function sessionAssessmentScope(AcademicSession $session): \Closure
    {
        $startDate = $session->start_date?->toDateString();
        $endDate = $session->end_date?->toDateString();

        return function (Builder $query) use ($session, $startDate, $endDate): void {
            $query->where(function (Builder $nested) use ($session, $startDate, $endDate): void {
                $nested->whereHas('term', fn (Builder $termQuery) => $termQuery->where('academic_session_id', $session->id))
                    ->orWhere(function (Builder $termLessQuery) use ($startDate, $endDate): void {
                        $termLessQuery->whereNull('term_id')
                            ->whereBetween(DB::raw('DATE(COALESCE(scheduled_at, created_at))'), [$startDate, $endDate]);
                    });
            });
        };
    }

    protected function inferNextClass(?SchoolClass $currentClass, Collection $classes): ?SchoolClass
    {
        if (! $currentClass) {
            return null;
        }

        $name = trim($currentClass->name);

        if (! preg_match('/^(.*?)(\d+)(.*)$/i', $name, $matches)) {
            return null;
        }

        $nextName = trim($matches[1].(((int) $matches[2]) + 1).$matches[3]);

        return $classes
            ->first(fn (SchoolClass $class) => strcasecmp($class->name, $nextName) === 0 && (string) $class->section === (string) $currentClass->section)
            ?? $classes->first(fn (SchoolClass $class) => strcasecmp($class->name, $nextName) === 0);
    }
}
