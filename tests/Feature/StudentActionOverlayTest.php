<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentActionOverlayTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_directory_loads_the_row_connected_action_overlay(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
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
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'first_name' => 'Daniel',
            'last_name' => 'Adeyemi',
            'name' => 'Daniel Adeyemi',
        ]);

        Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'BVS-JSS1-GEN-001',
            'student_id_no' => 'STD-0001',
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.students.index'));

        $response->assertOk();
        $response->assertSee('student-actions-overlay.css', false);
        $response->assertSee('route-admin-students-index', false);
        $response->assertSee('table-view-btn', false);

        $css = file_get_contents(public_path('student-actions-overlay.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('position: sticky !important', $css);
        $this->assertStringContainsString('right: 0 !important', $css);
        $this->assertStringContainsString('tbody td:last-child', $css);
        $this->assertStringContainsString('z-index: 20 !important', $css);
    }
}
