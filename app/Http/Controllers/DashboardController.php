<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\FeeInvoice;
use App\Models\Lesson;
use App\Models\Payment;
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

        return view('dashboard', compact('user', 'student', 'stats', 'announcements', 'roleBlocks'));
    }
}
