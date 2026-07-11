<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentManagementController extends Controller
{
    public function index(Request $request, ?string $classSlug = null): View
    {
        $classSlug = $classSlug ?: trim((string) $request->string('classSlug'));
        $classSlug = $classSlug !== '' ? $classSlug : null;
        $search = trim((string) $request->string('search'));
        $statusFilter = trim((string) $request->string('status'));
        $billingStatusFilter = trim((string) $request->string('billing_status'));
        $allowedViews = collect(['directory', 'new-students', 'inactive', 'siblings', 'debtors', 'class-bills']);
        $activeStudentView = $allowedViews->contains($request->string('view')->toString())
            ? $request->string('view')->toString()
            : 'directory';

        $classes = SchoolClass::query()
            ->withCount('students')
            ->orderBy('name')
            ->orderBy('section')
            ->get();

        $activeClass = null;
        if ($classSlug && $classSlug !== 'all' && $classSlug !== 'unassigned') {
            $activeClass = $classes->firstWhere('slug', $classSlug);
            abort_unless($activeClass, 404);
        }

        $currentSessionId = AcademicSession::query()->where('is_current', true)->value('id');
        $recentCutoff = now()->subDays(90);

        $studentWorkspaceStats = [
            'total' => Student::query()->count(),
            'active' => Student::query()->where('status', 'active')->count(),
            'new' => $this->newStudentQuery($currentSessionId, $recentCutoff)->count(),
            'inactive' => Student::query()->where('status', 'inactive')->count(),
            'sibling_families' => Student::query()
                ->whereNotNull('parent_user_id')
                ->select('parent_user_id')
                ->groupBy('parent_user_id')
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->count(),
            'debtors' => Student::query()
                ->whereHas('feeInvoices', fn (Builder $query) => $query->where('balance', '>', 0))
                ->count(),
        ];

        $filterState = array_filter([
            'view' => $activeStudentView,
            'search' => $search,
            'status' => $statusFilter,
            'billing_status' => $billingStatusFilter,
        ], fn ($value) => $value !== null && $value !== '');

        $classDirectory = $classes
            ->map(fn (SchoolClass $class) => [
                'key' => $class->slug,
                'name' => $class->display_name,
                'count' => (int) $class->students_count,
                'href' => route('admin.students.index', ['classSlug' => $class->slug, 'view' => 'directory']),
            ])
            ->values();

        $unassignedCount = Student::query()->whereNull('school_class_id')->count();
        if ($unassignedCount > 0) {
            $classDirectory->push([
                'key' => 'unassigned',
                'name' => 'Unassigned',
                'count' => $unassignedCount,
                'href' => route('admin.students.index', ['classSlug' => 'unassigned', 'view' => 'directory']),
            ]);
        }

        $classNavItems = collect([
            ['key' => 'all', 'label' => 'All Students', 'href' => route('admin.students.index', $filterState)],
            ...$classes->map(fn (SchoolClass $class) => [
                'key' => $class->slug,
                'label' => $class->display_name,
                'href' => route('admin.students.index', ['classSlug' => $class->slug] + $filterState),
            ])->all(),
        ]);

        if ($unassignedCount > 0) {
            $classNavItems->push([
                'key' => 'unassigned',
                'label' => 'Unassigned',
                'href' => route('admin.students.index', ['classSlug' => 'unassigned'] + $filterState),
            ]);
        }

        $studentContext = array_filter([
            'classSlug' => $classSlug,
            'search' => $search,
            'status' => $statusFilter,
            'billing_status' => $billingStatusFilter,
        ], fn ($value) => $value !== null && $value !== '');

        $studentOfficeNavItems = [
            ['key' => 'directory', 'label' => 'Directory', 'href' => route('admin.students.index', $studentContext + ['view' => 'directory'])],
            ['key' => 'new-students', 'label' => 'New Students', 'href' => route('admin.students.index', $studentContext + ['view' => 'new-students'])],
            ['key' => 'inactive', 'label' => 'Inactive', 'href' => route('admin.students.index', $studentContext + ['view' => 'inactive'])],
            ['key' => 'siblings', 'label' => 'Siblings', 'href' => route('admin.students.index', $studentContext + ['view' => 'siblings'])],
            ['key' => 'debtors', 'label' => 'Debtors', 'href' => route('admin.students.index', $studentContext + ['view' => 'debtors'])],
            ['key' => 'class-bills', 'label' => 'Class Bills', 'href' => route('admin.students.index', $studentContext + ['view' => 'class-bills'])],
        ];

        $students = collect();
        $newStudents = collect();
        $inactiveStudents = collect();
        $siblingRows = collect();
        $studentDebtorRows = collect();
        $studentClassBillingRows = collect();

        $studentTotalCount = 0;
        $studentPerPage = 10;
        $studentPageCount = 1;
        $studentPage = 1;
        $studentShowingFrom = 0;
        $studentShowingTo = 0;

        if ($activeStudentView === 'directory') {
            $query = Student::query()
                ->with([
                    'user',
                    'schoolClass',
                    'parent',
                    'feeInvoices:id,student_id,amount_due,amount_paid,balance',
                ]);

            $this->applyClassFilter($query, $activeClass, $classSlug);
            $this->applyStatusFilter($query, $statusFilter);
            $this->applyBillingFilter($query, $billingStatusFilter);
            $this->applySearch($query, $search);

            $studentTotalCount = (clone $query)->count();
            $studentPageCount = max(1, (int) ceil($studentTotalCount / $studentPerPage));
            $studentPage = min(max(1, (int) $request->integer('page', 1)), $studentPageCount);
            $studentShowingFrom = $studentTotalCount > 0 ? (($studentPage - 1) * $studentPerPage) + 1 : 0;
            $studentShowingTo = min($studentTotalCount, $studentPage * $studentPerPage);

            $students = $query
                ->orderBy('id')
                ->forPage($studentPage, $studentPerPage)
                ->get();
        } elseif ($activeStudentView === 'new-students') {
            $query = $this->newStudentQuery($currentSessionId, $recentCutoff)
                ->with(['user', 'schoolClass', 'parent']);
            $this->applyClassFilter($query, $activeClass, $classSlug);
            $this->applyStatusFilter($query, $statusFilter);
            $this->applyBillingFilter($query, $billingStatusFilter);
            $this->applySearch($query, $search);

            $newStudents = $query
                ->orderByDesc(DB::raw('COALESCE(enrolled_at, created_at)'))
                ->limit(100)
                ->get();
        } elseif ($activeStudentView === 'inactive') {
            $query = Student::query()
                ->with(['user', 'schoolClass', 'parent'])
                ->where('status', 'inactive');
            $this->applyClassFilter($query, $activeClass, $classSlug);
            $this->applySearch($query, $search);

            $inactiveStudents = $query->orderByDesc('updated_at')->limit(100)->get();
        } elseif ($activeStudentView === 'siblings') {
            $query = Student::query()->with(['user', 'schoolClass', 'parent'])->whereNotNull('parent_user_id');
            $this->applyClassFilter($query, $activeClass, $classSlug);
            $this->applySearch($query, $search);
            $siblingRows = $this->buildSiblingRows($query->get());
        } elseif ($activeStudentView === 'debtors') {
            $query = Student::query()
                ->with([
                    'user',
                    'schoolClass',
                    'parent',
                    'feeInvoices' => fn ($invoiceQuery) => $invoiceQuery
                        ->with('feeItem')
                        ->where('balance', '>', 0)
                        ->orderByDesc('balance'),
                ])
                ->whereHas('feeInvoices', fn (Builder $invoiceQuery) => $invoiceQuery->where('balance', '>', 0));
            $this->applyClassFilter($query, $activeClass, $classSlug);
            $this->applySearch($query, $search);
            $studentDebtorRows = $this->buildDebtorRows($query->get());
        } elseif ($activeStudentView === 'class-bills') {
            $studentClassBillingRows = $this->buildClassBillingRows($classes);
        }

        $studentGroups = $students->groupBy(fn (Student $student) => $student->schoolClass->display_name ?? 'Unassigned');
        $activeStudentClassPage = $activeClass?->slug ?? ($classSlug === 'unassigned' ? 'unassigned' : 'all');
        $pageTitle = match ($activeStudentView) {
            'new-students' => 'Newly Registered Students',
            'inactive' => 'Inactive Students',
            'siblings' => 'Sibling Groups',
            'debtors' => 'Student Debtors',
            'class-bills' => 'Student Class Billing',
            default => $activeClass?->display_name ?? ($classSlug === 'unassigned' ? 'Unassigned Students' : 'All Students'),
        };

        return view('admin.students', compact(
            'students',
            'classes',
            'search',
            'statusFilter',
            'billingStatusFilter',
            'studentTotalCount',
            'studentPerPage',
            'studentPage',
            'studentPageCount',
            'studentShowingFrom',
            'studentShowingTo',
            'studentGroups',
            'classDirectory',
            'classNavItems',
            'activeStudentClassPage',
            'pageTitle',
            'activeStudentView',
            'studentOfficeNavItems',
            'studentWorkspaceStats',
            'newStudents',
            'inactiveStudents',
            'siblingRows',
            'studentDebtorRows',
            'studentClassBillingRows',
        ));
    }

    protected function newStudentQuery(?int $currentSessionId, $recentCutoff): Builder
    {
        return Student::query()->where(function (Builder $query) use ($currentSessionId, $recentCutoff): void {
            if ($currentSessionId !== null) {
                $query->where('academic_session_id', $currentSessionId)
                    ->orWhere('enrolled_at', '>=', $recentCutoff)
                    ->orWhere(function (Builder $createdQuery) use ($recentCutoff): void {
                        $createdQuery->whereNull('enrolled_at')->where('created_at', '>=', $recentCutoff);
                    });

                return;
            }

            $query->where('enrolled_at', '>=', $recentCutoff)
                ->orWhere(function (Builder $createdQuery) use ($recentCutoff): void {
                    $createdQuery->whereNull('enrolled_at')->where('created_at', '>=', $recentCutoff);
                });
        });
    }

    protected function applyClassFilter(Builder $query, ?SchoolClass $activeClass, ?string $classSlug): void
    {
        if ($activeClass) {
            $query->where('school_class_id', $activeClass->id);
        } elseif ($classSlug === 'unassigned') {
            $query->whereNull('school_class_id');
        }
    }

    protected function applyStatusFilter(Builder $query, string $status): void
    {
        if ($status === '') {
            return;
        }

        $query->where(function (Builder $statusQuery) use ($status): void {
            $statusQuery->where('status', $status)
                ->orWhere(function (Builder $fallbackQuery) use ($status): void {
                    $fallbackQuery->whereNull('status')
                        ->whereHas('user', fn (Builder $userQuery) => $userQuery->where('status', $status));
                });
        });
    }

    protected function applyBillingFilter(Builder $query, string $billingStatus): void
    {
        match ($billingStatus) {
            'debtors' => $query->whereHas('feeInvoices', fn (Builder $invoiceQuery) => $invoiceQuery->where('balance', '>', 0)),
            'clear' => $query->whereDoesntHave('feeInvoices', fn (Builder $invoiceQuery) => $invoiceQuery->where('balance', '>', 0)),
            'overpaid' => $query->whereHas('feeInvoices', fn (Builder $invoiceQuery) => $invoiceQuery->whereColumn('amount_paid', '>', 'amount_due')),
            default => null,
        };
    }

    protected function applySearch(Builder $query, string $search): void
    {
        $words = collect(preg_split('/\s+/', mb_strtolower(trim($search))) ?: [])->filter()->values();
        if ($words->isEmpty()) {
            return;
        }

        foreach ($words as $word) {
            $like = '%'.$word.'%';
            $query->where(function (Builder $wordQuery) use ($like): void {
                $wordQuery
                    ->whereRaw('LOWER(admission_no) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(student_id_no) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(guardian_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(guardian_phone) LIKE ?', [$like])
                    ->orWhereHas('user', function (Builder $userQuery) use ($like): void {
                        $userQuery->where(function (Builder $nameQuery) use ($like): void {
                            $nameQuery->whereRaw('LOWER(name) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(first_name) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(middle_name) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(email) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(phone) LIKE ?', [$like]);
                        });
                    })
                    ->orWhereHas('parent', function (Builder $parentQuery) use ($like): void {
                        $parentQuery->where(function (Builder $nameQuery) use ($like): void {
                            $nameQuery->whereRaw('LOWER(name) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(first_name) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(middle_name) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(email) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(phone) LIKE ?', [$like]);
                        });
                    })
                    ->orWhereHas('schoolClass', function (Builder $classQuery) use ($like): void {
                        $classQuery->whereRaw('LOWER(name) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(section) LIKE ?', [$like]);
                    });
            });
        }
    }

    protected function buildSiblingRows(Collection $students): Collection
    {
        return $students
            ->groupBy('parent_user_id')
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->map(function (Collection $group): array {
                /** @var Student $primary */
                $primary = $group->first();

                return [
                    'parent' => $primary->parent,
                    'students' => $group->sortBy(fn (Student $student) => $student->user->fullName())->values(),
                    'family_size' => $group->count(),
                    'class_names' => $group
                        ->map(fn (Student $student) => $student->schoolClass->display_name ?? 'Unassigned')
                        ->unique()
                        ->values(),
                ];
            })
            ->values();
    }

    protected function buildDebtorRows(Collection $students): Collection
    {
        return $students
            ->map(function (Student $student): array {
                $items = $student->feeInvoices->where('balance', '>', 0)->values();

                return [
                    'student' => $student,
                    'invoice_count' => $items->count(),
                    'outstanding_total' => (float) $items->sum('balance'),
                    'paid_total' => (float) $items->sum('amount_paid'),
                    'overpayment_total' => 0.0,
                    'items' => $items,
                ];
            })
            ->filter(fn (array $row) => $row['outstanding_total'] > 0)
            ->sortByDesc('outstanding_total')
            ->values();
    }

    protected function buildClassBillingRows(Collection $classes): Collection
    {
        $aggregates = DB::table('fee_invoices')
            ->join('students', 'students.id', '=', 'fee_invoices.student_id')
            ->selectRaw('students.school_class_id as class_id')
            ->selectRaw('COUNT(fee_invoices.id) as invoice_count')
            ->selectRaw('COUNT(DISTINCT CASE WHEN fee_invoices.balance > 0 THEN fee_invoices.student_id END) as students_with_debt')
            ->selectRaw('COALESCE(SUM(fee_invoices.amount_due), 0) as expected_total')
            ->selectRaw('COALESCE(SUM(fee_invoices.amount_paid), 0) as collected_total')
            ->selectRaw('COALESCE(SUM(fee_invoices.balance), 0) as outstanding_total')
            ->groupBy('students.school_class_id')
            ->get()
            ->keyBy('class_id');

        return $classes
            ->map(function (SchoolClass $class) use ($aggregates): array {
                $row = $aggregates->get($class->id);
                $expectedTotal = (float) ($row->expected_total ?? 0);
                $collectedTotal = (float) ($row->collected_total ?? 0);

                return [
                    'class' => $class,
                    'student_count' => (int) $class->students_count,
                    'invoice_count' => (int) ($row->invoice_count ?? 0),
                    'students_with_debt' => (int) ($row->students_with_debt ?? 0),
                    'expected_total' => $expectedTotal,
                    'collected_total' => $collectedTotal,
                    'outstanding_total' => (float) ($row->outstanding_total ?? 0),
                    'collection_rate' => $expectedTotal > 0 ? round(($collectedTotal / $expectedTotal) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('outstanding_total')
            ->values();
    }
}
