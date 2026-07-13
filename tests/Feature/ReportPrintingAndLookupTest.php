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

    public function test_modern_and_classic_print_views_receive_dedicated_a4_styles(): void
    {
        [$admin, $student, $term] = $this->seedReportStudent();

        $modern = $this->actingAs($admin)->get(route('admin.reports.print', [$student, $term]));

        $modern->assertOk();
        $modern->assertSee('report-print-modern', false);
        $modern->assertSee('report-print-modern.css', false);

        $classic = $this->actingAs($admin)->get(route('admin.reports.print', [
            'student' => $student,
            'term' => $term,
            'layout' => 'classic',
        ]));

        $classic->assertOk();
        $classic->assertSee('report-print-classic', false);
        $classic->assertSee('report-print-classic.css', false);
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
