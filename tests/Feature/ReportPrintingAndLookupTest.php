<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPrintingAndLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_index_offers_instant_lookup_and_direct_report_access(): void
    {
        [$admin, $student, $term] = $this->seedReportStudent();

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'term_id' => $term->id,
            'search' => $student->student_id_no,
        ]));

        $response->assertOk();
        $response->assertSee('Find a student report instantly');
        $response->assertSee('data-report-search-input', false);
        $response->assertSee('report-student-options', false);
        $response->assertSee($student->student_id_no);
        $response->assertSee('Open Report');
        $response->assertSee(route('admin.reports.show', [
            'student' => $student,
            'section' => 'overview',
            'term_id' => $term->id,
            'search' => $student->student_id_no,
        ]));
    }

    public function test_modern_and_classic_print_views_fill_single_a4_portrait_pages(): void
    {
        [$admin, $student, $term] = $this->seedReportStudent();

        $modern = $this->actingAs($admin)->get(route('admin.reports.print', [$student, $term]));

        $modern->assertOk();
        $modern->assertSee('report-print-modern', false);
        $modern->assertSee('report-print-modern-preview-match.css', false);
        $modern->assertSee('modern-report-sheet', false);
        $modern->assertSee('modern-report-scores', false);
        $modern->assertSee('modern-report-development', false);
        $modern->assertSee('report-density-normal', false);

        $modernPrintCss = file_get_contents(public_path('report-print-modern-preview-match.css'));
        $this->assertIsString($modernPrintCss);
        $this->assertStringContainsString('size: A4 portrait', $modernPrintCss);
        $this->assertStringContainsString('width: 1088px !important', $modernPrintCss);
        $this->assertStringContainsString('zoom: 0.66', $modernPrintCss);
        $this->assertStringContainsString('grid-template-columns: repeat(4, minmax(0, 1fr))', $modernPrintCss);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr))', $modernPrintCss);
        $this->assertStringContainsString('grid-template-columns: repeat(3, minmax(0, 1fr))', $modernPrintCss);
        $this->assertStringNotContainsString('size: A4 landscape', $modernPrintCss);
        $this->assertStringNotContainsString('page-break-after: always', $modernPrintCss);
        $this->assertStringNotContainsString('page-break-before: always', $modernPrintCss);

        $classic = $this->actingAs($admin)->get(route('admin.reports.print', [
            'student' => $student,
            'term' => $term,
            'layout' => 'classic',
        ]));

        $classic->assertOk();
        $classic->assertSee('report-print-classic', false);
        $classic->assertSee('report-print-classic.css', false);

        $classicPrintCss = file_get_contents(public_path('report-print-classic.css'));
        $this->assertIsString($classicPrintCss);
        $this->assertStringContainsString('size: A4 portrait', $classicPrintCss);
        $this->assertStringContainsString('width: 200mm', $classicPrintCss);
        $this->assertStringContainsString('height: 287mm', $classicPrintCss);
    }

    protected function seedReportStudent(): array
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $session = AcademicSession::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Second Term',
            'slug' => 'second-term',
            'start_date' => '2026-01-10',
            'end_date' => '2026-04-10',
            'is_current' => true,
        ]);

        $class = SchoolClass::create([
            'name' => 'JSS 2',
            'slug' => 'jss-2-general',
            'section' => 'General',
        ]);

        $studentUser = User::factory()->create([
            'role' => UserRole::Student,
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'name' => 'Amina Yusuf',
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-2026-001',
            'student_id_no' => 'STD-2026-001',
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
        ]);

        return [$admin, $student, $term];
    }
}
