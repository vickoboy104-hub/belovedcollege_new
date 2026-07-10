<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\FeeInvoice;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\StaffProfile;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $student = $user->studentProfile()->with('schoolClass', 'feeInvoices')->first();
        $children = collect();

        if ($user->hasAnyRole(UserRole::Parent)) {
            $children = Student::query()
                ->with('user', 'schoolClass', 'feeInvoices')
                ->where('parent_user_id', $user->id)
                ->get();

            $student = $children->first();
        }

        $stats = [
            [
                'label' => 'Students',
                'value' => Student::count(),
                'accent' => 'bg-sky-500/15 text-sky-900',
            ],
            [
                'label' => 'Staff',
                'value' => StaffProfile::count(),
                'accent' => 'bg-emerald-500/15 text-emerald-900',
            ],
            [
                'label' => 'Active Invoices',
                'value' => FeeInvoice::whereNot('status', 'paid')->count(),
                'accent' => 'bg-amber-500/15 text-amber-900',
            ],
            [
                'label' => 'Payments Logged',
                'value' => Payment::count(),
                'accent' => 'bg-rose-500/15 text-rose-900',
            ],
        ];

        $announcements = Announcement::query()
            ->where('is_published', true)
            ->latest('published_at')
            ->take(4)
            ->get();

        $roleBlocks = [
            'teacherAssignments' => Assignment::query()->where('teacher_id', $user->id)->latest()->take(4)->get(),
            'teacherLessons' => Lesson::query()->where('teacher_id', $user->id)->latest()->take(4)->get(),
            'studentInvoices' => $student?->feeInvoices()->latest()->take(4)->get() ?? collect(),
            'studentLessons' => $student
                ? Lesson::query()->where('school_class_id', $student->school_class_id)->latest()->take(4)->get()
                : collect(),
            'children' => $children,
        ];

        $quickAccessCards = collect();
        $financeSnapshot = null;

        if ($user->hasAnyRole([UserRole::Admin, UserRole::Principal])) {
            $quickAccessCards = collect([
                ['title' => 'Register Student', 'description' => 'Open the student intake drawer and create login details.', 'route' => route('admin.students.index', ['register' => 1]), 'tone' => 'student', 'icon' => 'student'],
                ['title' => 'Add Parent', 'description' => 'Find or link a guardian record to a child profile.', 'route' => route('admin.parents.index'), 'tone' => 'parent', 'icon' => 'parents'],
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
            $totalBilled = (float) FeeInvoice::sum('amount_due');
            $totalCollected = (float) FeeInvoice::sum('amount_paid');
            $financeSnapshot = [
                'students' => Student::count(),
                'classes' => SchoolClass::count(),
                'outstanding' => (float) FeeInvoice::sum('balance'),
                'debtorStudents' => FeeInvoice::query()->where('balance', '>', 0)->pluck('student_id')->unique()->count(),
                'totalBilled' => $totalBilled,
                'totalCollected' => $totalCollected,
                'collectionRate' => $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 0,
            ];
        }

        return view('dashboard', compact('user', 'student', 'stats', 'announcements', 'roleBlocks', 'quickAccessCards', 'financeSnapshot'));
    }
}
