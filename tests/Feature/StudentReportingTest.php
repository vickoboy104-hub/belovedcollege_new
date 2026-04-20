<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicSession;
use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentTermReport;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentReportingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_compile_update_and_publish_student_term_report(): void
    {
        [$admin, $student, $term, $subject] = $this->seedReportData();

        $indexResponse = $this->actingAs($admin)->get(route('admin.reports.index', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Official report cards and release control');
        $indexResponse->assertSee($student->user->fullName());
        $indexResponse->assertSee($subject->name);

        $updateResponse = $this->actingAs($admin)->post(route('admin.reports.update', [$student, $term]), [
            'days_school_open' => 65,
            'days_present' => 60,
            'class_teacher_remark' => 'Excellent progress.',
            'principal_remark' => 'Keep it up.',
            'character_traits' => [
                'attentiveness' => 'A',
                'attendance' => 'B',
            ],
            'practical_skills' => [
                'music' => 'A',
            ],
        ]);

        $updateResponse->assertRedirect(route('admin.reports.index', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]));

        $publishResponse = $this->actingAs($admin)->post(route('admin.reports.publish', [$student, $term]), [
            'portal_enabled' => 1,
            'checker_enabled' => 1,
            'checker_pin' => '443322',
        ]);

        $publishResponse->assertRedirect(route('admin.reports.index', [
            'student_id' => $student->id,
            'term_id' => $term->id,
        ]));

        $report = StudentTermReport::query()->where('student_id', $student->id)->where('term_id', $term->id)->first();

        $this->assertNotNull($report);
        $this->assertTrue($report->portal_enabled);
        $this->assertTrue($report->checker_enabled);
        $this->assertNotNull($report->published_at);
        $this->assertSame('Excellent progress.', $report->class_teacher_remark);
        $this->assertSame('A', $report->character_traits['attentiveness']);
        $this->assertTrue(Hash::check('443322', $report->checker_pin_hash));

        $printResponse = $this->actingAs($admin)->get(route('admin.reports.print', [$student, $term]));

        $printResponse->assertOk();
        $printResponse->assertSee('Result Sheet');
        $printResponse->assertSee('Excellent progress.');
        $printResponse->assertSee($subject->name);
    }

    public function test_public_result_checker_can_open_published_report_with_valid_pin(): void
    {
        [$admin, $student, $term, $subject] = $this->seedReportData();

        $this->actingAs($admin)->post(route('admin.reports.publish', [$student, $term]), [
            'portal_enabled' => 1,
            'checker_enabled' => 1,
            'checker_pin' => '778899',
        ]);

        $response = $this->post(route('reports.checker.lookup'), [
            'admission_no' => $student->admission_no,
            'term_id' => $term->id,
            'pin' => '778899',
        ]);

        $response->assertOk();
        $response->assertSee($student->user->fullName());
        $response->assertSee($subject->name);
        $response->assertSee('Average');
    }

    protected function seedReportData(): array
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'name' => 'Admin User',
        ]);

        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'first_name' => 'Grace',
            'last_name' => 'Teacher',
            'name' => 'Grace Teacher',
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
            'class_teacher_id' => $teacher->id,
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
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
        ]);

        $subject = Subject::create([
            'name' => 'Mathematics',
            'code' => 'MTH-001',
        ]);

        $quiz = Assessment::create([
            'teacher_id' => $teacher->id,
            'term_id' => $term->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'Math Quiz',
            'type' => 'quiz',
            'total_score' => 10,
        ]);

        $test = Assessment::create([
            'teacher_id' => $teacher->id,
            'term_id' => $term->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'Math Test',
            'type' => 'test',
            'total_score' => 20,
        ]);

        $exam = Assessment::create([
            'teacher_id' => $teacher->id,
            'term_id' => $term->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'Math Exam',
            'type' => 'exam',
            'total_score' => 70,
        ]);

        AssessmentResult::create([
            'assessment_id' => $quiz->id,
            'student_id' => $student->id,
            'score' => 8,
        ]);

        AssessmentResult::create([
            'assessment_id' => $test->id,
            'student_id' => $student->id,
            'score' => 15,
        ]);

        AssessmentResult::create([
            'assessment_id' => $exam->id,
            'student_id' => $student->id,
            'score' => 54,
        ]);

        return [$admin, $student, $term, $subject];
    }
}
