<?php

namespace App\Services;

use App\Models\AssessmentResult;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentTermReport;
use App\Models\Term;
use Illuminate\Support\Collection;

class StudentReportService
{
    public const CHARACTER_TRAITS = [
        'attentiveness' => 'Attentiveness',
        'attendance' => 'Attendance',
        'punctuality' => 'Punctuality / Lateness',
        'politeness' => 'Politeness / Manner',
        'relationship_with_others' => 'Relationship With Others',
        'self_control' => 'Self Control',
        'attitude_to_school_work' => 'Attitude To School Work',
    ];

    public const PRACTICAL_SKILLS = [
        'drama' => 'Drama',
        'music' => 'Music',
        'craft' => 'Craft',
        'hobbies' => 'Hobbies',
        'clubs' => 'Clubs',
        'sport_game' => 'Sport / Game',
    ];

    public function getOrCreateReport(Student $student, Term $term): StudentTermReport
    {
        $report = StudentTermReport::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'term_id' => $term->id,
            ],
            [
                'academic_session_id' => $term->academic_session_id,
                'school_class_id' => $student->school_class_id,
                'character_traits' => $this->defaultRatings(self::CHARACTER_TRAITS),
                'practical_skills' => $this->defaultRatings(self::PRACTICAL_SKILLS),
            ],
        );

        return $this->refreshReport($report);
    }

    public function refreshReport(StudentTermReport $report): StudentTermReport
    {
        $report->loadMissing('student.user', 'student.schoolClass', 'term.academicSession', 'schoolClass');

        $subjectRows = $this->buildSubjectRows($report->student, $report->term);
        $average = round((float) $subjectRows->avg('percentage'), 2);
        $total = round((float) $subjectRows->sum('percentage'), 2);
        $position = $report->student->school_class_id
            ? $this->determineClassPosition($report->student, $report->term, $report->student->school_class_id)
            : null;

        $report->forceFill([
            'academic_session_id' => $report->term->academic_session_id,
            'school_class_id' => $report->student->school_class_id,
            'character_traits' => $this->mergeRatings($report->character_traits, self::CHARACTER_TRAITS),
            'practical_skills' => $this->mergeRatings($report->practical_skills, self::PRACTICAL_SKILLS),
            'overall_grade' => $subjectRows->isNotEmpty() ? $this->gradeFromScore($average) : null,
            'average_score' => $subjectRows->isNotEmpty() ? $average : null,
            'total_score' => $subjectRows->isNotEmpty() ? $total : null,
            'subject_count' => $subjectRows->count(),
            'class_position' => $position,
        ])->save();

        return $report->fresh(['student.user', 'student.schoolClass', 'term.academicSession', 'approver', 'publisher']);
    }

    public function buildSubjectRows(Student $student, Term $term): Collection
    {
        $results = AssessmentResult::query()
            ->with('assessment.subject', 'assessment.teacher')
            ->where('student_id', $student->id)
            ->whereHas('assessment', fn ($query) => $query->where('term_id', $term->id))
            ->get();

        return $results
            ->groupBy(fn (AssessmentResult $result) => $result->assessment->subject_id)
            ->map(function (Collection $group) {
                $subject = $group->first()->assessment->subject;
                $typed = $group->groupBy(fn (AssessmentResult $result) => $result->assessment->type->value ?? (string) $result->assessment->type);
                $rawScore = round((float) $group->sum('score'), 2);
                $maxScore = round((float) $group->sum(fn (AssessmentResult $result) => (float) $result->assessment->total_score), 2);
                $percentage = $maxScore > 0 ? round(($rawScore / $maxScore) * 100, 2) : $rawScore;

                return [
                    'subject_id' => $subject?->id,
                    'subject_name' => $subject?->name ?? 'Unassigned Subject',
                    'teachers' => $group->pluck('assessment.teacher')
                        ->filter()
                        ->map(fn ($teacher) => $teacher->fullName())
                        ->unique()
                        ->values()
                        ->implode(', '),
                    'quiz_score' => round((float) $typed->get('quiz', collect())->sum('score'), 2),
                    'test_score' => round((float) $typed->get('test', collect())->sum('score'), 2),
                    'project_score' => round((float) $typed->get('project', collect())->sum('score'), 2),
                    'exam_score' => round((float) $typed->get('exam', collect())->sum('score'), 2),
                    'raw_score' => $rawScore,
                    'max_score' => $maxScore,
                    'percentage' => $percentage,
                    'grade' => $this->gradeFromScore($percentage),
                    'remark' => $this->remarkFromScore($percentage),
                ];
            })
            ->sortBy('subject_name')
            ->values();
    }

    public function buildStudentRecordSummary(Student $student): array
    {
        $student->loadMissing('user', 'parent', 'schoolClass', 'academicSession', 'promotions.toAcademicSession', 'promotions.toSchoolClass');

        $payments = $student->payments()
            ->with('feeInvoice.feeItem')
            ->latest('paid_at')
            ->get()
            ->reject(fn ($payment) => data_get($payment->payload, 'source') === 'bundle_allocation')
            ->values();
        $reports = $student->termReports()
            ->with('term.academicSession')
            ->latest('published_at')
            ->get();
        $attendanceCount = $student->attendanceRecords()->count();
        $presentCount = $student->attendanceRecords()->where('status', 'present')->count();

        return [
            'payments' => $payments,
            'reports' => $reports,
            'promotions' => $student->promotions()->with('fromAcademicSession', 'toAcademicSession', 'fromSchoolClass', 'toSchoolClass', 'approver')->latest('approved_at')->get(),
            'attendance_summary' => [
                'total_entries' => $attendanceCount,
                'present_count' => $presentCount,
                'present_rate' => $attendanceCount > 0 ? round(($presentCount / $attendanceCount) * 100, 2) : null,
            ],
            'finance_summary' => [
                'invoice_total' => round((float) $student->feeInvoices()->sum('amount_due'), 2),
                'paid_total' => round((float) $payments->sum('amount'), 2),
                'outstanding_total' => round((float) $student->feeInvoices()->sum('balance'), 2),
            ],
        ];
    }

    public function gradeFromScore(float $score): string
    {
        return match (true) {
            $score >= 70 => 'A',
            $score >= 60 => 'B',
            $score >= 50 => 'C',
            $score >= 45 => 'D',
            $score >= 40 => 'E',
            default => 'F',
        };
    }

    public function remarkFromScore(float $score): string
    {
        return match (true) {
            $score >= 70 => 'Excellent',
            $score >= 60 => 'Very Good',
            $score >= 50 => 'Good',
            $score >= 45 => 'Fair',
            $score >= 40 => 'Pass',
            default => 'Needs Improvement',
        };
    }

    protected function determineClassPosition(Student $student, Term $term, int $schoolClassId): ?int
    {
        $studentIds = Student::query()
            ->where('school_class_id', $schoolClassId)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return null;
        }

        $scores = AssessmentResult::query()
            ->selectRaw('assessment_results.student_id, SUM(assessment_results.score) as raw_score, SUM(assessments.total_score) as max_score')
            ->join('assessments', 'assessments.id', '=', 'assessment_results.assessment_id')
            ->where('assessments.term_id', $term->id)
            ->whereIn('assessment_results.student_id', $studentIds)
            ->groupBy('assessment_results.student_id')
            ->get()
            ->map(fn ($row) => [
                'student_id' => (int) $row->student_id,
                'average' => (float) $row->max_score > 0
                    ? round(((float) $row->raw_score / (float) $row->max_score) * 100, 2)
                    : 0.0,
            ])
            ->sortByDesc('average')
            ->values();

        if ($scores->isEmpty()) {
            return null;
        }

        $position = 0;
        $displayRank = 0;
        $previousAverage = null;

        foreach ($scores as $row) {
            $position++;

            if ($previousAverage === null || $row['average'] !== $previousAverage) {
                $displayRank = $position;
                $previousAverage = $row['average'];
            }

            if ($row['student_id'] === $student->id) {
                return $displayRank;
            }
        }

        return null;
    }

    protected function defaultRatings(array $definitions): array
    {
        return collect($definitions)->mapWithKeys(fn ($label, $key) => [$key => null])->all();
    }

    protected function mergeRatings(?array $ratings, array $definitions): array
    {
        return collect($definitions)->mapWithKeys(fn ($label, $key) => [$key => $ratings[$key] ?? null])->all();
    }
}
