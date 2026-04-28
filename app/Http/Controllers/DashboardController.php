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
                ['title' => 'School Management', 'description' => 'Sessions, terms, classes, and subjects.', 'route' => route('admin.academics'), 'tone' => 'school'],
                ['title' => 'Student Management', 'description' => 'Student records, classes, and profiles.', 'route' => route('admin.students.index'), 'tone' => 'student'],
                ['title' => 'Parents Management', 'description' => 'Guardian contacts and linked children.', 'route' => route('admin.parents.index'), 'tone' => 'parent'],
                ['title' => 'Bills & Payment', 'description' => 'Invoices, collections, and balances.', 'route' => route('admin.finance'), 'tone' => 'finance'],
                ['title' => 'Reports', 'description' => 'Scores, publications, and print views.', 'route' => route('admin.reports.index'), 'tone' => 'report'],
                ['title' => 'Staff Management', 'description' => 'Staff records and departments.', 'route' => route('admin.staff.index'), 'tone' => 'staff'],
            ]);
        } elseif ($user->hasAnyRole([UserRole::Accountant])) {
            $quickAccessCards = collect([
                ['title' => 'Bills & Payment', 'description' => 'Create invoices and record collections.', 'route' => route('admin.finance'), 'tone' => 'finance'],
                ['title' => 'Finance Records', 'description' => 'Balances, printable fee lists, and receipts.', 'route' => route('admin.finance.records'), 'tone' => 'report'],
            ]);
        } elseif ($user->hasAnyRole([UserRole::Teacher])) {
            $quickAccessCards = collect([
                ['title' => 'Teaching Workspace', 'description' => 'Lessons, assignments, attendance, and CBT.', 'route' => route('teacher.learning'), 'tone' => 'school'],
            ]);
        } elseif ($user->hasAnyRole([UserRole::Student, UserRole::Parent])) {
            $quickAccessCards = collect([
                ['title' => 'Student Portal', 'description' => 'Results, lessons, attendance, and fees.', 'route' => route('portal.index'), 'tone' => 'student'],
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
