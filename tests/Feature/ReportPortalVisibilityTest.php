<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicSession;
use App\Models\Student;
use App\Models\StudentTermReport;
use App\Models\Term;
use App\Models\User;
use App\Services\StudentReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPortalVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_record_hides_unpublished_reports_but_admin_record_keeps_full_history(): void
    {
        $studentUser = User::factory()->create(['role' => UserRole::Student]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-REPORT-001',
        ]);
        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);
        $firstTerm = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'slug' => 'first-term-report-visibility',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-15',
        ]);
        $secondTerm = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Second Term',
            'slug' => 'second-term-report-visibility',
            'start_date' => '2027-01-10',
            'end_date' => '2027-04-10',
        ]);

        StudentTermReport::create([
            'student_id' => $student->id,
            'term_id' => $firstTerm->id,
            'academic_session_id' => $session->id,
            'portal_enabled' => true,
            'published_at' => now(),
        ]);
        StudentTermReport::create([
            'student_id' => $student->id,
            'term_id' => $secondTerm->id,
            'academic_session_id' => $session->id,
            'portal_enabled' => false,
            'published_at' => null,
        ]);

        $service = app(StudentReportService::class);

        $this->actingAs($studentUser);
        $studentRecord = $service->buildStudentRecordSummary($student);
        $this->assertCount(1, $studentRecord['reports']);
        $this->assertSame($firstTerm->id, $studentRecord['reports']->first()->term_id);

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);
        $adminRecord = $service->buildStudentRecordSummary($student);
        $this->assertCount(2, $adminRecord['reports']);
    }
}
