<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentEmailOptionalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_register_student_without_email_and_student_can_login_by_admission_number(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'password' => 'student-password',
        ]);

        $response->assertRedirect(route('admin.students.index'));

        $student = Student::query()->with('user')->latest('id')->firstOrFail();
        $this->assertNull($student->user->email);
        $this->assertNotEmpty($student->admission_no);

        auth()->logout();

        $loginResponse = $this->post(route('student.login.store'), [
            'login' => $student->admission_no,
            'password' => 'student-password',
            'audience' => 'student',
        ]);

        $loginResponse->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($student->user);
    }
}
