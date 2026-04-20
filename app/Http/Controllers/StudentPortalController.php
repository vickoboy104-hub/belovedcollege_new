<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssessmentResult;
use App\Models\AttendanceRecord;
use App\Models\Assessment;
use App\Models\CbtAttempt;
use App\Models\FeeInvoice;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentTermReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $children = collect();
        $student = $user->studentProfile()->with('schoolClass', 'user')->first();

        if ($user->hasAnyRole(UserRole::Parent)) {
            $children = Student::query()
                ->with('user', 'schoolClass')
                ->where('parent_user_id', $user->id)
                ->get();

            $student = $children->firstWhere('id', (int) $request->integer('student')) ?? $children->first();
        }

        abort_unless($student, 404);

        $lessons = Lesson::query()
            ->with('subject', 'teacher')
            ->where('school_class_id', $student->school_class_id)
            ->latest()
            ->take(8)
            ->get();

        $assignments = Assignment::query()
            ->with('subject', 'teacher')
            ->where('school_class_id', $student->school_class_id)
            ->latest()
            ->take(8)
            ->get();

        $results = AssessmentResult::query()
            ->with('assessment.subject', 'assessment.term')
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        $attendance = AttendanceRecord::query()
            ->where('student_id', $student->id)
            ->latest('attendance_date')
            ->take(10)
            ->get();

        $invoices = FeeInvoice::query()
            ->with('feeItem', 'payments')
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        $payments = Payment::query()
            ->with('feeInvoice.feeItem')
            ->where('student_id', $student->id)
            ->latest()
            ->take(10)
            ->get()
            ->reject(fn (Payment $payment) => data_get($payment->payload, 'source') === 'bundle_allocation')
            ->values();

        $submissions = AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->pluck('id', 'assignment_id');

        $reportSummary = $results->groupBy(fn (AssessmentResult $result) => $result->assessment->subject->name ?? 'Unassigned')
            ->map(fn ($group) => [
                'average' => round($group->avg('score'), 1),
                'entries' => $group->count(),
            ]);
        $publishedReports = StudentTermReport::query()
            ->with('term.academicSession')
            ->where('student_id', $student->id)
            ->where('portal_enabled', true)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->get();

        $cbtEnabled = (string) Setting::getValue('cbt_enabled', '1') === '1';
        $cbtAssessments = $user->hasAnyRole(UserRole::Student) && $cbtEnabled
            ? Assessment::query()
                ->with('subject', 'teacher', 'cbtQuestions')
                ->where('is_cbt', true)
                ->where('cbt_is_active', true)
                ->where('school_class_id', $student->school_class_id)
                ->where(function ($query): void {
                    $query->whereNull('cbt_starts_at')->orWhere('cbt_starts_at', '<=', now());
                })
                ->where(function ($query): void {
                    $query->whereNull('cbt_ends_at')->orWhere('cbt_ends_at', '>=', now());
                })
                ->latest('cbt_starts_at')
                ->get()
            : collect();
        $cbtAttempts = $user->hasAnyRole(UserRole::Student)
            ? CbtAttempt::query()
                ->with('assessment.subject')
                ->where('student_id', $student->id)
                ->latest('started_at')
                ->get()
            : collect();

        return view('portal.student', compact(
            'user',
            'student',
            'children',
            'lessons',
            'assignments',
            'results',
            'attendance',
            'invoices',
            'payments',
            'submissions',
            'reportSummary',
            'publishedReports',
            'cbtEnabled',
            'cbtAssessments',
            'cbtAttempts',
        ));
    }

    public function submitAssignment(Request $request, Assignment $assignment): RedirectResponse
    {
        $student = $request->user()->studentProfile;

        abort_unless($student, 403);
        abort_unless($student->school_class_id === $assignment->school_class_id, 403);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:10000'],
        ]);

        AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'content' => $validated['content'],
                'submitted_at' => now(),
            ],
        );

        return back()->with('status', 'Assignment submitted successfully.');
    }
}
