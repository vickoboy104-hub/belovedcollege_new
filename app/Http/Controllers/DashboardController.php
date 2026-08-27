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
        $dashboardDescription = 'Open the workspace assigned to your account.';

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
                ['title' => 'Register Student', 'description' => 'Open the student intake drawer and create login details.', 'route' => route('admin.students.index', ['register' => 1]), 'tone' => 'student', 'icon' => 'student'],
                ['title' => 'Add Parent', 'description' => 'Find or link a guardian record to a child profile.', 'route' => route('admin.parents.index'), 'tone' => 'parent', 'icon' => 'parents'],
                ['title' => 'Teacher Access', 'description' => 'Grant or remove exact subject and class permissions for teachers.', 'route' => route('admin.teacher-access.index'), 'tone' => 'school', 'icon' => 'learning'],
                ['title' => 'Payment Gateways', 'description' => 'Enable checkout providers and securely configure merchant credentials.', 'route' => route('admin.payment-gateways.index'), 'tone' => 'finance', 'icon' => 'finance'],
                ['title' => 'Record Payment', 'description' => 'Post a confirmed school fee payment.', 'route' => route('admin.finance', ['section' => 'record-payment']), 'tone' => 'finance', 'icon' => 'bills'],
                ['title' => 'Create Invoice', 'description' => 'Generate a student or class billing record.', 'route' => route('admin.finance', ['section' => 'generate-invoice']), 'tone' => 'finance', 'icon' => 'finance-records'],
                ['title' => 'View Debtors', 'description' => 'Review students with outstanding balances.', 'route' => route('admin.students.index', ['view' => 'debtors']), 'tone' => 'report', 'icon' => 'reports'],
                ['title' => 'Publish Announcement', 'description' => 'Post an update for students, parents, or staff.', 'route' => route('admin.academics', ['section' => 'announcement']), 'tone' => 'school', 'icon' => 'announcement'],
                ['title' => 'Edit Homepage', 'description' => 'Update public homepage slides and content.', 'route' => route('admin.settings', ['section' => 'landing-builder']), 'tone' => 'school', 'icon' => 'settings'],
                ['title' => 'Generate Report', 'description' => 'Open the report card workspace.', 'route' => route('admin.reports.index'), 'tone' => 'report', 'icon' => 'reports'],
            ]);

            $financeSnapshot = $this->financeSnapshot($metrics, $studentCount);
            $dashboardDescription = 'School-wide operational summary for authorised leadership accounts.';
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
                ['title' => 'Bills & Payment', 'description' => 'Create invoices and record collections.', 'route' => route('admin.finance'), 'tone' => 'finance', 'icon' => 'bills'],
                ['title' => 'Finance Records', 'description' => 'Balances, printable fee lists, and receipts.', 'route' => route('admin.finance.records'), 'tone' => 'report', 'icon' => 'finance-records'],
            ]);

            $financeSnapshot = $this->financeSnapshot($metrics, (int) ($metrics->student_count ?? 0));
            $dashboardDescription = 'Finance activity and collection information relevant to your accounts role.';
        } elseif ($user->hasAnyRole(UserRole::Teacher)) {
            $stats = [
                ['label' => 'Lessons Published', 'value' => $user->lessons()->count()],
                ['label' => 'Assignments Created', 'value' => $user->assignments()->count()],
                ['label' => 'Assessments Created', 'value' => $user->assessments()->count()],
                ['label' => 'Managed Classes', 'value' => $user->managedClasses()->count()],
            ];

            $quickAccessCards = collect([
                ['title' => 'Publish Lesson Note', 'description' => 'Share a lesson note with a class group.', 'route' => route('teacher.learning', ['section' => 'publish-lesson']), 'tone' => 'school', 'icon' => 'learning'],
                ['title' => 'Create Assignment', 'description' => 'Assign classwork and collect submissions.', 'route' => route('teacher.learning', ['section' => 'create-assignment']), 'tone' => 'student', 'icon' => 'assignments'],
                ['title' => 'Create Assessment', 'description' => 'Set up a test, quiz, or exam score sheet.', 'route' => route('teacher.learning', ['section' => 'assessment']), 'tone' => 'report', 'icon' => 'reports'],
                ['title' => 'Submit Attendance', 'description' => 'Log daily student attendance quickly.', 'route' => route('teacher.learning', ['section' => 'attendance']), 'tone' => 'finance', 'icon' => 'clock'],
                ['title' => 'Review Submissions', 'description' => 'Open submitted assignments and scores.', 'route' => route('teacher.learning', ['section' => 'submissions']), 'tone' => 'parent', 'icon' => 'eye'],
                ['title' => 'Open CBT Library', 'description' => 'Manage CBT assessments and attempts.', 'route' => route('teacher.learning', ['section' => 'cbt-list']), 'tone' => 'school', 'icon' => 'portal'],
            ]);

            $dashboardDescription = 'Your teaching workload, published learning content, and assigned class responsibilities.';
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
                ['title' => 'Student Portal', 'description' => 'Results, lessons, attendance, and fees for your account.', 'route' => route('portal.index'), 'tone' => 'student', 'icon' => 'portal'],
            ]);

            $dashboardDescription = 'Your personal academic account summary: lessons, assignments, published reports, and fee balance.';
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
                ['title' => 'Student Portal', 'description' => 'Open the academic and fee records for your linked child accounts.', 'route' => route('portal.index'), 'tone' => 'student', 'icon' => 'portal'],
            ]);

            $dashboardDescription = 'A private summary of the student accounts linked to you as a parent or guardian.';
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
            'dashboardDescription',
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
