<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\FeeInvoice;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentTermReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $stats = [];
        $quickAccessCards = collect();
        $financeSnapshot = null;

        if ($user->hasAnyRole([UserRole::Admin, UserRole::Principal])) {
            $metrics = $this->schoolMetrics();
            $studentCount = (int) ($metrics->student_count ?? 0);
            $staffCount = (int) ($metrics->staff_count ?? 0);
            $activeInvoiceCount = (int) ($metrics->active_invoice_count ?? 0);
            $paymentCount = (int) ($metrics->payment_count ?? 0);

            $stats = [
                ['label' => 'Students', 'value' => $studentCount],
                ['label' => 'Staff', 'value' => $staffCount],
                ['label' => 'Active Invoices', 'value' => $activeInvoiceCount],
                ['label' => 'Payments Logged', 'value' => $paymentCount],
            ];

            $quickAccessCards = collect([
                ['title' => 'Register Student', 'route' => route('admin.students.index', ['register' => 1]), 'tone' => 'student', 'icon' => 'student'],
                ['title' => 'Add Parent', 'route' => route('admin.parents.index'), 'tone' => 'parent', 'icon' => 'parents'],
                ['title' => 'Teacher Access', 'route' => route('admin.teacher-access.index'), 'tone' => 'school', 'icon' => 'learning'],
                ['title' => 'Payment Gateways', 'route' => route('admin.payment-gateways.index'), 'tone' => 'finance', 'icon' => 'finance'],
                ['title' => 'Record Payment', 'route' => route('admin.finance', ['section' => 'record-payment']), 'tone' => 'finance', 'icon' => 'bills'],
                ['title' => 'Create Invoice', 'route' => route('admin.finance', ['section' => 'generate-invoice']), 'tone' => 'finance', 'icon' => 'finance-records'],
                ['title' => 'View Debtors', 'route' => route('admin.students.index', ['view' => 'debtors']), 'tone' => 'report', 'icon' => 'reports'],
                ['title' => 'Publish Announcement', 'route' => route('admin.academics', ['section' => 'announcement']), 'tone' => 'school', 'icon' => 'announcement'],
                ['title' => 'Edit Homepage', 'route' => route('admin.settings', ['section' => 'landing-builder']), 'tone' => 'school', 'icon' => 'settings'],
                ['title' => 'Generate Report', 'route' => route('admin.reports.index'), 'tone' => 'report', 'icon' => 'reports'],
            ]);

            $financeSnapshot = $this->financeSnapshot($metrics, $studentCount);
        } elseif ($user->hasAnyRole(UserRole::Accountant)) {
            $metrics = $this->financeMetrics();
            $totalBilled = (float) ($metrics->total_billed ?? 0);
            $totalCollected = (float) ($metrics->total_collected ?? 0);
            $collectionRate = $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 0;

            $stats = [
                ['label' => 'Active Invoices', 'value' => (int) ($metrics->active_invoice_count ?? 0)],
                ['label' => 'Payments Logged', 'value' => (int) ($metrics->payment_count ?? 0)],
                ['label' => 'Outstanding Balance', 'value' => $this->compactMoney((float) ($metrics->outstanding ?? 0))],
                ['label' => 'Collection Rate', 'value' => number_format($collectionRate, 1).'%'],
            ];

            $quickAccessCards = collect([
                ['title' => 'Bills & Payment', 'route' => route('admin.finance'), 'tone' => 'finance', 'icon' => 'bills'],
                ['title' => 'Finance Records', 'route' => route('admin.finance.records'), 'tone' => 'report', 'icon' => 'finance-records'],
            ]);

            $financeSnapshot = $this->financeSnapshot($metrics, (int) ($metrics->student_count ?? 0));
        } elseif ($user->hasAnyRole(UserRole::Teacher)) {
            $stats = [
                ['label' => 'Lessons Published', 'value' => $user->lessons()->count()],
                ['label' => 'Assignments Created', 'value' => $user->assignments()->count()],
                ['label' => 'Assessments Created', 'value' => $user->assessments()->count()],
                ['label' => 'Managed Classes', 'value' => $user->managedClasses()->count()],
            ];

            $quickAccessCards = collect([
                ['title' => 'Publish Lesson Note', 'route' => route('teacher.learning', ['section' => 'publish-lesson']), 'tone' => 'school', 'icon' => 'learning'],
                ['title' => 'Create Assignment', 'route' => route('teacher.learning', ['section' => 'create-assignment']), 'tone' => 'student', 'icon' => 'assignments'],
                ['title' => 'Create Assessment', 'route' => route('teacher.learning', ['section' => 'assessment']), 'tone' => 'report', 'icon' => 'reports'],
                ['title' => 'Submit Attendance', 'route' => route('teacher.learning', ['section' => 'attendance']), 'tone' => 'finance', 'icon' => 'clock'],
                ['title' => 'Review Submissions', 'route' => route('teacher.learning', ['section' => 'submissions']), 'tone' => 'parent', 'icon' => 'eye'],
                ['title' => 'Open CBT Library', 'route' => route('teacher.learning', ['section' => 'cbt-list']), 'tone' => 'school', 'icon' => 'portal'],
            ]);

        } elseif ($user->hasAnyRole(UserRole::Student)) {
            $student = $user->studentProfile()->first();
            $schoolClassId = $student?->school_class_id;
            $studentId = $student?->id;

            $lessonCount = $schoolClassId
                ? Lesson::query()->where('school_class_id', $schoolClassId)->count()
                : 0;
            $assignmentCount = $schoolClassId
                ? Assignment::query()->where('school_class_id', $schoolClassId)->count()
                : 0;
            $publishedReportCount = $studentId
                ? StudentTermReport::query()
                    ->where('student_id', $studentId)
                    ->where('portal_enabled', true)
                    ->whereNotNull('published_at')
                    ->count()
                : 0;
            $outstandingFees = $studentId
                ? (float) FeeInvoice::query()->where('student_id', $studentId)->sum('balance')
                : 0;

            $stats = [
                ['label' => 'Lesson Notes', 'value' => $lessonCount],
                ['label' => 'Assignments', 'value' => $assignmentCount],
                ['label' => 'Published Reports', 'value' => $publishedReportCount],
                ['label' => 'Fees Owed', 'value' => $this->compactMoney($outstandingFees)],
            ];

            $quickAccessCards = collect([
                ['title' => 'Student Portal', 'route' => route('portal.index'), 'tone' => 'student', 'icon' => 'portal'],
            ]);

        } elseif ($user->hasAnyRole(UserRole::Parent)) {
            $children = Student::query()
                ->select(['id', 'school_class_id'])
                ->where('parent_user_id', $user->id)
                ->get();
            $studentIds = $children->pluck('id');
            $classIds = $children->pluck('school_class_id')->filter()->unique();

            $assignmentCount = $classIds->isNotEmpty()
                ? Assignment::query()->whereIn('school_class_id', $classIds)->count()
                : 0;
            $publishedReportCount = $studentIds->isNotEmpty()
                ? StudentTermReport::query()
                    ->whereIn('student_id', $studentIds)
                    ->where('portal_enabled', true)
                    ->whereNotNull('published_at')
                    ->count()
                : 0;
            $outstandingFees = $studentIds->isNotEmpty()
                ? (float) FeeInvoice::query()->whereIn('student_id', $studentIds)->sum('balance')
                : 0;

            $stats = [
                ['label' => 'Linked Children', 'value' => $children->count()],
                ['label' => 'Assignments', 'value' => $assignmentCount],
                ['label' => 'Published Reports', 'value' => $publishedReportCount],
                ['label' => 'Outstanding Fees', 'value' => $this->compactMoney($outstandingFees)],
            ];

            $quickAccessCards = collect([
                ['title' => 'Student Portal', 'route' => route('portal.index'), 'tone' => 'student', 'icon' => 'portal'],
            ]);

        }

        $announcements = Announcement::query()
            ->select(['id', 'title', 'body', 'excerpt', 'category', 'published_at'])
            ->where('is_published', true)
            ->latest('published_at')
            ->take(4)
            ->get();

        return view('dashboard', compact(
            'user',
            'stats',
            'announcements',
            'quickAccessCards',
            'financeSnapshot',
        ));
    }

    private function schoolMetrics(): object
    {
        return DB::selectOne(<<<'SQL'
            SELECT
                (SELECT COUNT(*) FROM students) AS student_count,
                (SELECT COUNT(*) FROM staff_profiles) AS staff_count,
                (SELECT COUNT(*) FROM payments) AS payment_count,
                (SELECT COUNT(*) FROM school_classes) AS class_count,
                fee_summary.active_invoice_count,
                fee_summary.total_billed,
                fee_summary.total_collected,
                fee_summary.outstanding,
                fee_summary.debtor_students
            FROM (
                SELECT
                    COUNT(CASE WHEN status <> ? THEN 1 END) AS active_invoice_count,
                    COALESCE(SUM(amount_due), 0) AS total_billed,
                    COALESCE(SUM(amount_paid), 0) AS total_collected,
                    COALESCE(SUM(balance), 0) AS outstanding,
                    COUNT(DISTINCT CASE WHEN balance > 0 THEN student_id END) AS debtor_students
                FROM fee_invoices
            ) AS fee_summary
        SQL, ['paid']);
    }

    private function financeMetrics(): object
    {
        return DB::selectOne(<<<'SQL'
            SELECT
                (SELECT COUNT(*) FROM students) AS student_count,
                (SELECT COUNT(*) FROM school_classes) AS class_count,
                (SELECT COUNT(*) FROM payments) AS payment_count,
                fee_summary.active_invoice_count,
                fee_summary.total_billed,
                fee_summary.total_collected,
                fee_summary.outstanding,
                fee_summary.debtor_students
            FROM (
                SELECT
                    COUNT(CASE WHEN status <> ? THEN 1 END) AS active_invoice_count,
                    COALESCE(SUM(amount_due), 0) AS total_billed,
                    COALESCE(SUM(amount_paid), 0) AS total_collected,
                    COALESCE(SUM(balance), 0) AS outstanding,
                    COUNT(DISTINCT CASE WHEN balance > 0 THEN student_id END) AS debtor_students
                FROM fee_invoices
            ) AS fee_summary
        SQL, ['paid']);
    }

    private function financeSnapshot(object $metrics, int $studentCount): array
    {
        $totalBilled = (float) ($metrics->total_billed ?? 0);
        $totalCollected = (float) ($metrics->total_collected ?? 0);

        return [
            'students' => $studentCount,
            'classes' => (int) ($metrics->class_count ?? 0),
            'outstanding' => (float) ($metrics->outstanding ?? 0),
            'debtorStudents' => (int) ($metrics->debtor_students ?? 0),
            'totalBilled' => $totalBilled,
            'totalCollected' => $totalCollected,
            'collectionRate' => $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 0,
        ];
    }

    private function compactMoney(float $amount): string
    {
        $sign = $amount < 0 ? '-' : '';
        $absolute = abs($amount);

        return match (true) {
            $absolute >= 1000000000 => $sign.'₦'.number_format($absolute / 1000000000, 2).'B',
            $absolute >= 1000000 => $sign.'₦'.number_format($absolute / 1000000, 2).'M',
            $absolute >= 1000 => $sign.'₦'.number_format($absolute / 1000, 1).'K',
            default => $sign.'₦'.number_format($absolute, 0),
        };
    }
}
