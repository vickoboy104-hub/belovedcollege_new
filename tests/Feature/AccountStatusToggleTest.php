<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\StaffProfile;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountStatusToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_deactivate_and_reactivate_a_student_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $studentUser = User::factory()->create([
            'role' => UserRole::Student,
            'status' => 'active',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-TOGGLE-001',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.students.deactivate', $student))
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('users', ['id' => $studentUser->id, 'status' => 'inactive']);

        $this->actingAs($admin)
            ->patch(route('admin.students.deactivate', $student->fresh()))
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'active']);
        $this->assertDatabaseHas('users', ['id' => $studentUser->id, 'status' => 'active']);
    }

    public function test_admin_can_deactivate_and_reactivate_a_staff_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $staffUser = User::factory()->create([
            'role' => UserRole::Teacher,
            'status' => 'active',
        ]);
        $profile = StaffProfile::create([
            'user_id' => $staffUser->id,
            'employee_no' => 'STF-TOGGLE-001',
            'department' => 'Sciences',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.staff.deactivate', $profile))
            ->assertRedirect(route('admin.staff.index'));

        $this->assertDatabaseHas('staff_profiles', ['id' => $profile->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('users', ['id' => $staffUser->id, 'status' => 'inactive']);

        $this->actingAs($admin)
            ->patch(route('admin.staff.deactivate', $profile->fresh()))
            ->assertRedirect(route('admin.staff.index'));

        $this->assertDatabaseHas('staff_profiles', ['id' => $profile->id, 'status' => 'active']);
        $this->assertDatabaseHas('users', ['id' => $staffUser->id, 'status' => 'active']);
    }
}
