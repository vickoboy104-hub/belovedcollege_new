<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_directory_and_new_intake_views_render_with_sticky_actions(): void
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

        $class = SchoolClass::create([
            'name' => 'JSS 1',
            'slug' => 'jss-1-general',
            'section' => 'General',
        ]);

        $studentUser = User::factory()->create([
            'name' => 'Daniel Adeyemi',
            'first_name' => 'Daniel',
            'last_name' => 'Adeyemi',
            'email' => 'daniel.student@example.test',
            'role' => UserRole::Student,
        ]);

        Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'BVS-JSS1-GEN-001',
            'student_id_no' => 'STD-001',
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'guardian_name' => 'Parent Adeyemi',
            'status' => 'active',
            'enrolled_at' => now()->toDateString(),
        ]);

        $directoryResponse = $this->actingAs($admin)->get(route('admin.students.index', [
            'view' => 'directory',
        ]));

        $directoryResponse->assertOk();
        $directoryResponse->assertSee('Daniel Adeyemi');
        $directoryResponse->assertSee('BVS-JSS1-GEN-001');
        $directoryResponse->assertSee('has-sticky-edge-columns', false);
        $directoryResponse->assertSee('View');

        $newStudentsResponse = $this->actingAs($admin)->get(route('admin.students.index', [
            'view' => 'new-students',
        ]));

        $newStudentsResponse->assertOk();
        $newStudentsResponse->assertSee('New Student Intake');
        $newStudentsResponse->assertSee('Daniel Adeyemi');
        $newStudentsResponse->assertSee('has-sticky-edge-columns', false);
        $newStudentsResponse->assertSee('View');
    }
}
