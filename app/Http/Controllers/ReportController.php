<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentTermReport;
use App\Models\Term;
use App\Services\StudentReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ReportController extends Controller
{
    protected const ADMIN_REPORT_SECTIONS = [
        'overview',
        'scores',
        'remarks',
        'publication',
    ];

    public function __construct(
        protected StudentReportService $reportService,
    ) {
    }

    public function adminIndex(Request $request, ?string $classSlug = null): View
    {
        $terms = Term::query()
            ->with('academicSession')
            ->latest('start_date')
            ->get();
        $classes = SchoolClass::query()
            ->orderBy('name')
            ->orderBy('section')
            ->get();
        $selectedTerm = $terms->firstWhere('id', $request->integer('term_id'))
            ?? $terms->firstWhere('is_current', true)
            ?? $terms->first();
        $search = trim((string) $request->string('search'));
        $activeClass = null;

        if ($classSlug && $classSlug !== 'all' && $classSlug !== 'unassigned') {
            $activeClass = $classes->firstWhere('slug', $classSlug);
            abort_unless($activeClass, 404);
        }

        $students = Student::query()
            ->with('user', 'schoolClass', 'parent')
            ->when($activeClass, fn ($query) => $query->where('school_class_id', $activeClass->id))
            ->when($classSlug === 'unassigned', fn ($query) => $query->whereNull('school_class_id'))
            ->orderBy('admission_no')
            ->get();

        if ($search !== '') {
            $needle = strtolower($search);
            $students = $students->filter(function (Student $student) use ($needle) {
                $haystack = strtolower(implode(' ', array_filter([
                    $student->user->fullName(),
                    $student->admission_no,
                    $student->student_id_no,
                    $student->schoolClass->display_name ?? null,
                ])));

                return str_contains($haystack, $needle);
            })->values();
        }

        $classDirectory = $classes->map(function (SchoolClass $class) use ($selectedTerm, $search) {
            return [
                'key' => $class->slug,
                'name' => $class->display_name,
                'count' => Student::query()->where('school_class_id', $class->id)->count(),
                'href' => route('admin.reports.index', $this->filterRouteParameters([
                    'classSlug' => $class->slug,
                    'term_id' => $selectedTerm?->id,
                    'search' => $search,
                ])),
            ];
        })->values();

        if (Student::query()->whereNull('school_class_id')->exists()) {
            $classDirectory->push([
                'key' => 'unassigned',
                'name' => 'Unassigned',
                'count' => Student::query()->whereNull('school_class_id')->count(),
                'href' => route('admin.reports.index', $this->filterRouteParameters([
                    'classSlug' => 'unassigned',
                    'term_id' => $selectedTerm?->id,
                    'search' => $search,
                ])),
            ]);
        }

        $classNavItems = collect([
            [
                'key' => 'all',
                'label' => 'All Classes',
                'href' => route('admin.reports.index', $this->filterRouteParameters([
                    'term_id' => $selectedTerm?->id,
                    'search' => $search,
                ])),
            ],
            ...$classes->map(fn (SchoolClass $class) => [
                'key' => $class->slug,
                'label' => $class->display_name,
                'href' => route('admin.reports.index', $this->filterRouteParameters([
                    'classSlug' => $class->slug,
                    'term_id' => $selectedTerm?->id,
                    'search' => $search,
                ])),
            ])->all(),
        ]);

        if ($classDirectory->firstWhere('key', 'unassigned')) {
            $classNavItems->push([
                'key' => 'unassigned',
                'label' => 'Unassigned',
                'href' => route('admin.reports.index', $this->filterRouteParameters([
                    'classSlug' => 'unassigned',
                    'term_id' => $selectedTerm?->id,
                    'search' => $search,
                ])),
            ]);
        }

        $activeReportClassPage = $activeClass?->slug ?? ($classSlug === 'unassigned' ? 'unassigned' : 'all');
        $pageTitle = $activeClass?->display_name ?? ($classSlug === 'unassigned' ? 'Unassigned Students' : ($search !== '' ? 'Search Results' : 'Choose a Student Category'));

        return view('admin.reports.index', [
            'terms' => $terms,
            'classes' => $classes,
            'students' => $students,
            'selectedTerm' => $selectedTerm,
            'search' => $search,
            'classDirectory' => $classDirectory,
            'classNavItems' => $classNavItems,
            'activeReportClassPage' => $activeReportClassPage,
            'pageTitle' => $pageTitle,
            'activeClass' => $activeClass,
        ]);
    }

    public function adminShow(Request $request, Student $student, ?string $section = null): View
    {
        $activeReportSection = $this->normalizeAdminReportSection($section);
        $terms = Term::query()
            ->with('academicSession')
            ->latest('start_date')
            ->get();
        $selectedTerm = $terms->firstWhere('id', $request->integer('term_id'))
            ?? $terms->firstWhere('is_current', true)
            ?? $terms->first();

        $student->loadMissing('user', 'schoolClass', 'parent', 'academicSession');

        $report = $selectedTerm
            ? $this->reportService->getOrCreateReport($student, $selectedTerm)
            : null;
        $subjectRows = $report
            ? $this->reportService->buildSubjectRows($student, $selectedTerm)
            : collect();

        return view('admin.reports.manage', [
            'terms' => $terms,
            'selectedTerm' => $selectedTerm,
            'selectedStudent' => $student,
            'report' => $report,
            'subjectRows' => $subjectRows,
            'search' => trim((string) $request->string('search')),
            'classSlug' => trim((string) $request->string('classSlug')) ?: null,
            'characterTraits' => StudentReportService::CHARACTER_TRAITS,
            'practicalSkills' => StudentReportService::PRACTICAL_SKILLS,
            'skillGrades' => ['A', 'B', 'C', 'D', 'E'],
            'activeReportSection' => $activeReportSection,
        ]);
    }

    public function update(Request $request, Student $student, Term $term): RedirectResponse
    {
        $validated = $request->validate([
            'days_school_open' => ['nullable', 'integer', 'min:0', 'max:365'],
            'days_present' => ['nullable', 'integer', 'min:0', 'max:365'],
            'days_absent' => ['nullable', 'integer', 'min:0', 'max:365'],
            'next_term_begins_on' => ['nullable', 'date'],
            'class_teacher_remark' => ['nullable', 'string', 'max:255'],
            'guidance_remark' => ['nullable', 'string', 'max:255'],
            'principal_remark' => ['nullable', 'string', 'max:255'],
            'house_master_remark' => ['nullable', 'string', 'max:255'],
            'character_traits' => ['nullable', 'array'],
            'practical_skills' => ['nullable', 'array'],
        ]);

        $report = $this->reportService->getOrCreateReport($student, $term);
        $daysSchoolOpen = $validated['days_school_open'] ?? $report->days_school_open;
        $daysPresent = $validated['days_present'] ?? $report->days_present;
        $daysAbsent = $validated['days_absent']
            ?? ($daysSchoolOpen !== null && $daysPresent !== null ? max($daysSchoolOpen - $daysPresent, 0) : $report->days_absent);

        $report->update([
            'days_school_open' => $daysSchoolOpen,
            'days_present' => $daysPresent,
            'days_absent' => $daysAbsent,
            'next_term_begins_on' => $validated['next_term_begins_on'] ?? $report->next_term_begins_on,
            'class_teacher_remark' => $validated['class_teacher_remark'] ?? null,
            'guidance_remark' => $validated['guidance_remark'] ?? null,
            'principal_remark' => $validated['principal_remark'] ?? null,
            'house_master_remark' => $validated['house_master_remark'] ?? null,
            'character_traits' => $this->normalizeRatings($validated['character_traits'] ?? [], StudentReportService::CHARACTER_TRAITS),
            'practical_skills' => $this->normalizeRatings($validated['practical_skills'] ?? [], StudentReportService::PRACTICAL_SKILLS),
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        $this->reportService->refreshReport($report);

        return redirect()->route('admin.reports.show', $this->adminReportRouteParameters(
            $request,
            $student,
            $term,
            'remarks',
        ))->with('status', 'Student term report updated successfully.');
    }

    public function publish(Request $request, Student $student, Term $term): RedirectResponse
    {
        $validated = $request->validate([
            'portal_enabled' => ['nullable', 'boolean'],
            'checker_enabled' => ['nullable', 'boolean'],
            'checker_pin' => ['nullable', 'digits_between:4,12'],
        ]);

        $report = $this->reportService->getOrCreateReport($student, $term);

        if ($report->subject_count === 0) {
            return back()->withErrors([
                'report' => 'This report cannot be published yet because no subject scores have been compiled for the selected term.',
            ]);
        }

        $portalEnabled = $request->boolean('portal_enabled');
        $checkerEnabled = $request->boolean('checker_enabled');
        $checkerPin = $validated['checker_pin'] ?? null;

        if ($checkerEnabled && ! $checkerPin && ! $report->checker_pin_hash) {
            return back()->withErrors([
                'checker_pin' => 'Enter a checker PIN the first time you enable result checker access for this report.',
            ]);
        }

        $report->update([
            'portal_enabled' => $portalEnabled,
            'checker_enabled' => $checkerEnabled,
            'checker_pin_hash' => $checkerEnabled
                ? ($checkerPin ? Hash::make($checkerPin) : $report->checker_pin_hash)
                : null,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'published_by' => ($portalEnabled || $checkerEnabled) ? $request->user()->id : null,
            'published_at' => ($portalEnabled || $checkerEnabled) ? now() : null,
        ]);

        $message = $portalEnabled || $checkerEnabled
            ? 'Result access updated and published successfully.'
            : 'Report approval saved. Portal and checker access remain disabled.';

        return redirect()->route('admin.reports.show', $this->adminReportRouteParameters(
            $request,
            $student,
            $term,
            'publication',
        ))->with('status', $message);
    }

    public function adminPrint(Request $request, Student $student, Term $term): View
    {
        $report = $this->reportService->getOrCreateReport($student, $term);

        return $this->renderReportCard(
            $request,
            $report,
            $this->reportService->buildSubjectRows($student, $term),
            route('admin.reports.show', $this->adminReportRouteParameters(
                $request,
                $student,
                $term,
                'overview',
            )),
            'Back to result center',
        );
    }

    public function adminRecord(Student $student): View
    {
        $student->loadMissing('user', 'parent', 'schoolClass', 'academicSession');

        return view('reports.student-record', [
            'student' => $student,
            'record' => $this->reportService->buildStudentRecordSummary($student),
            'backUrl' => route('admin.students.show', $student),
            'backLabel' => 'Back to student profile',
        ]);
    }

    public function portalPrint(Request $request, Term $term): View
    {
        $student = $this->resolvePortalStudent($request);
        $report = $this->reportService->getOrCreateReport($student, $term);

        abort_unless($report->portal_enabled && $report->published_at !== null, 403);

        return $this->renderReportCard(
            $request,
            $report,
            $this->reportService->buildSubjectRows($student, $term),
            route('portal.index', $request->user()->hasAnyRole(UserRole::Parent) ? ['student' => $student->id] : []),
            'Back to portal',
        );
    }

    public function portalRecord(Request $request): View
    {
        $student = $this->resolvePortalStudent($request);

        return view('reports.student-record', [
            'student' => $student,
            'record' => $this->reportService->buildStudentRecordSummary($student),
            'backUrl' => route('portal.index', $request->user()->hasAnyRole(UserRole::Parent) ? ['student' => $student->id] : []),
            'backLabel' => 'Back to portal',
        ]);
    }

    public function checker(): View
    {
        return view('reports.checker', [
            'terms' => Term::query()->with('academicSession')->latest('start_date')->get(),
            'report' => null,
            'subjectRows' => collect(),
        ]);
    }

    public function checkerLookup(Request $request): View
    {
        $validated = $request->validate([
            'admission_no' => ['required', 'string', 'max:255'],
            'term_id' => ['required', 'exists:terms,id'],
            'pin' => ['required', 'string', 'max:50'],
        ]);

        $student = Student::query()
            ->with('user', 'schoolClass')
            ->where('admission_no', $validated['admission_no'])
            ->first();
        $term = Term::query()->with('academicSession')->findOrFail($validated['term_id']);
        $report = $student
            ? StudentTermReport::query()
                ->with('student.user', 'student.schoolClass', 'term.academicSession', 'approver', 'publisher')
                ->where('student_id', $student->id)
                ->where('term_id', $term->id)
                ->first()
            : null;

        if (! $student || ! $report || ! $report->checker_enabled || ! $report->checker_pin_hash || ! Hash::check($validated['pin'], $report->checker_pin_hash)) {
            return view('reports.checker', [
                'terms' => Term::query()->with('academicSession')->latest('start_date')->get(),
                'report' => null,
                'subjectRows' => collect(),
            ])->withErrors([
                'pin' => 'The admission number, term, or checker PIN is not valid for any published result.',
            ])->withInput();
        }

        $report = $this->reportService->refreshReport($report);

        return view('reports.checker', [
            'terms' => Term::query()->with('academicSession')->latest('start_date')->get(),
            'report' => $report,
            'subjectRows' => $this->reportService->buildSubjectRows($student, $term),
        ]);
    }

    protected function renderReportCard(Request $request, StudentTermReport $report, Collection $subjectRows, string $backUrl, string $backLabel): View
    {
        $layout = $request->string('layout')->toString() === 'classic' ? 'classic' : 'modern';

        return view($layout === 'classic' ? 'reports.print-classic' : 'reports.print', [
            'report' => $report,
            'subjectRows' => $subjectRows,
            'characterTraits' => StudentReportService::CHARACTER_TRAITS,
            'practicalSkills' => StudentReportService::PRACTICAL_SKILLS,
            'backUrl' => $backUrl,
            'backLabel' => $backLabel,
            'classicRows' => $this->buildClassicRows($subjectRows),
            'layout' => $layout,
        ]);
    }

    protected function resolvePortalStudent(Request $request): Student
    {
        $user = $request->user();
        $student = $user->studentProfile()
            ->with('user', 'schoolClass', 'academicSession')
            ->first();

        if ($user->hasAnyRole(UserRole::Parent)) {
            $children = Student::query()
                ->with('user', 'schoolClass', 'academicSession')
                ->where('parent_user_id', $user->id)
                ->get();

            $student = $children->firstWhere('id', $request->integer('student')) ?? $children->first();
        }

        abort_unless($student, 404);

        return $student;
    }

    protected function normalizeRatings(array $ratings, array $definitions): array
    {
        return collect($definitions)->mapWithKeys(function ($label, $key) use ($ratings) {
            $value = $ratings[$key] ?? null;

            return [$key => in_array($value, ['A', 'B', 'C', 'D', 'E'], true) ? $value : null];
        })->all();
    }

    protected function adminReportRouteParameters(Request $request, Student $student, Term $term, string $defaultSection = 'overview'): array
    {
        return $this->filterRouteParameters([
            'section' => $this->normalizeAdminReportSection((string) $request->input('section'), $defaultSection),
            'student' => $student,
            'term_id' => $term->id,
            'classSlug' => $request->input('classSlug'),
            'search' => $request->input('search'),
        ]);
    }

    protected function normalizeAdminReportSection(?string $section, string $fallback = 'overview'): string
    {
        return in_array($section, self::ADMIN_REPORT_SECTIONS, true) ? $section : $fallback;
    }

    protected function filterRouteParameters(array $parameters): array
    {
        return array_filter($parameters, fn (mixed $value) => $value !== null && $value !== '');
    }

    protected function buildClassicRows(Collection $subjectRows): Collection
    {
        $rows = $subjectRows
            ->map(function (array $row) {
                return [
                    'subject_name' => $row['subject_name'],
                    'a' => $this->formatClassicCell($row['quiz_score']),
                    'b' => $this->formatClassicCell($row['test_score']),
                    'c' => $this->formatClassicCell($row['project_score']),
                    'd' => $this->formatClassicCell($row['exam_score']),
                    'e' => $this->formatClassicCell($row['percentage']),
                    'f' => $row['grade'] ?: '',
                    'g' => '',
                    'h' => $row['remark'] ?: '',
                    'teacher_remark' => $row['teachers'] ?: '',
                ];
            })
            ->values();

        $minimumRows = 17;

        while ($rows->count() < $minimumRows) {
            $rows->push([
                'subject_name' => '',
                'a' => '',
                'b' => '',
                'c' => '',
                'd' => '',
                'e' => '',
                'f' => '',
                'g' => '',
                'h' => '',
                'teacher_remark' => '',
            ]);
        }

        return $rows;
    }

    protected function formatClassicCell(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $number = round((float) $value, 2);

        return floor($number) == $number ? (string) (int) $number : number_format($number, 2);
    }
}
