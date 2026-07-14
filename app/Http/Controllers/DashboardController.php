<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $metrics = DB::selectOne(<<<'SQL'
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

        $studentCount = (int) ($metrics->student_count ?? 0);
        $staffCount = (int) ($metrics->staff_count ?? 0);
        $activeInvoiceCount = (int) ($metrics->active_invoice_count ?? 0);
        $paymentCount = (int) ($metrics->payment_count ?? 0);

        $stats = [
            ['label' => 'Students', 'value' => $studentCount, 'accent' => 'bg-sky-500/15 text-sky-900'],
            ['label' => 'Staff', 'value' => $staffCount, 'accent' => 'bg-emerald-500/15 text-emerald-900'],
            ['label' => 'Active Invoices', 'value' => $activeInvoiceCount, 'accent' => 'bg-amber-500/15 text-amber-900'],
            ['label' => 'Payments Logged', 'value' => $paymentCount, 'accent' => 'bg-rose-500/15 text-rose-900'],
        ];

        $announcements = Announcement::query()
            ->select(['id', 'title', 'body', 'excerpt', 'category', 'published_at'])
            ->where('is_published', true)
            ->latest('published_at')
            ->take(4)
            ->get();

        $quickAccessCards = collect();
        $financeSnapshot = null;

        if ($user->hasAnyRole([UserRole::Admin, UserRole::Principal])) {
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
        } elseif ($user->hasAnyRole([UserRole::Accountant])) {
            $quickAccessCards = collect([
                ['title' => 'Bills & Payment', 'description' => 'Create invoices and record collections.', 'route' => route('admin.finance'), 'tone' => 'finance', 'icon' => 'bills'],
                ['title' => 'Finance Records', 'description' => 'Balances, printable fee lists, and receipts.', 'route' => route('admin.finance.records'), 'tone' => 'report', 'icon' => 'finance-records'],
            ]);
        } elseif ($user->hasAnyRole([UserRole::Teacher])) {
            $quickAccessCards = collect([
                ['title' => 'Publish Lesson Note', 'description' => 'Share a lesson note with a class group.', 'route' => route('teacher.learning', ['section' => 'publish-lesson']), 'tone' => 'school', 'icon' => 'learning'],
                ['title' => 'Create Assignment', 'description' => 'Assign classwork and collect submissions.', 'route' => route('teacher.learning', ['section' => 'create-assignment']), 'tone' => 'student', 'icon' => 'assignments'],
                ['title' => 'Create Assessment', 'description' => 'Set up a test, quiz, or exam score sheet.', 'route' => route('teacher.learning', ['section' => 'assessment']), 'tone' => 'report', 'icon' => 'reports'],
                ['title' => 'Submit Attendance', 'description' => 'Log daily student attendance quickly.', 'route' => route('teacher.learning', ['section' => 'attendance']), 'tone' => 'finance', 'icon' => 'clock'],
                ['title' => 'Review Submissions', 'description' => 'Open submitted assignments and scores.', 'route' => route('teacher.learning', ['section' => 'submissions']), 'tone' => 'parent', 'icon' => 'eye'],
                ['title' => 'Open CBT Library', 'description' => 'Manage CBT assessments and attempts.', 'route' => route('teacher.learning', ['section' => 'cbt-list']), 'tone' => 'school', 'icon' => 'portal'],
            ]);
        } elseif ($user->hasAnyRole([UserRole::Student, UserRole::Parent])) {
            $quickAccessCards = collect([
                ['title' => 'Student Portal', 'description' => 'Results, lessons, attendance, and fees.', 'route' => route('portal.index'), 'tone' => 'student', 'icon' => 'portal'],
            ]);
        }

        if ($user->hasAnyRole([UserRole::Admin, UserRole::Principal, UserRole::Accountant])) {
            $totalBilled = (float) ($metrics->total_billed ?? 0);
            $totalCollected = (float) ($metrics->total_collected ?? 0);

            $financeSnapshot = [
                'students' => $studentCount,
                'classes' => (int) ($metrics->class_count ?? 0),
                'outstanding' => (float) ($metrics->outstanding ?? 0),
                'debtorStudents' => (int) ($metrics->debtor_students ?? 0),
                'totalBilled' => $totalBilled,
                'totalCollected' => $totalCollected,
                'collectionRate' => $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 0,
            ];
        }

        return view('dashboard', compact('user', 'stats', 'announcements', 'quickAccessCards', 'financeSnapshot'));
    }
}
