<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\StaffProfile;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeopleManagementInputSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_parent_portal_account_cannot_be_repurposed_as_a_parent(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $teacher = User::factory()->create([
            'email' => 'teacher@example.test',
            'role' => UserRole::Teacher,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'email' => 'amina@example.test',
            'parent_name' => 'Linked Parent',
            'parent_email' => 'teacher@example.test',
        ]);

        $response->assertSessionHasErrors('parent_email');
        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'email' => 'teacher@example.test',
            'role' => UserRole::Teacher->value,
        ]);
        $this->assertDatabaseCount('students', 0);
    }

    public function test_parent_email_must_be_different_from_student_email(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'email' => 'family@example.test',
            'parent_name' => 'Mrs Yusuf',
            'parent_email' => 'family@example.test',
        ]);

        $response->assertSessionHasErrors('parent_email');
        $this->assertDatabaseMissing('users', ['email' => 'family@example.test']);
        $this->assertDatabaseCount('students', 0);
    }

    public function test_new_student_parent_details_require_a_parent_email(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'parent_name' => 'Mrs Yusuf',
            'parent_phone' => '08030000000',
        ]);

        $response->assertSessionHasErrors('parent_email');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_new_parent_account_requires_a_parent_name(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'parent_email' => 'parent@example.test',
        ]);

        $response->assertSessionHasErrors('parent_name');
        $this->assertDatabaseMissing('users', ['email' => 'parent@example.test']);
        $this->assertDatabaseCount('students', 0);
    }

    public function test_existing_parent_account_can_be_safely_linked_to_a_student(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $parent = User::factory()->create([
            'name' => 'Mrs Yusuf',
            'email' => 'parent@example.test',
            'role' => UserRole::Parent,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'email' => 'amina@example.test',
            'parent_name' => 'Mrs Yusuf',
            'parent_email' => 'parent@example.test',
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $student = Student::query()->whereHas('user', fn ($query) => $query->where('email', 'amina@example.test'))->firstOrFail();

        $this->assertSame($parent->id, $student->parent_user_id);
        $this->assertDatabaseHas('users', [
            'id' => $parent->id,
            'role' => UserRole::Parent->value,
        ]);
    }

    public function test_linking_child_does_not_reactivate_an_inactive_parent_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $parent = User::factory()->create([
            'name' => 'Mrs Yusuf',
            'email' => 'inactive-parent@example.test',
            'role' => UserRole::Parent,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'email' => 'amina@example.test',
            'parent_name' => 'Mrs Yusuf',
            'parent_email' => 'inactive-parent@example.test',
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $student = Student::query()->whereHas('user', fn ($query) => $query->where('email', 'amina@example.test'))->firstOrFail();

        $this->assertSame($parent->id, $student->parent_user_id);
        $this->assertDatabaseHas('users', [
            'id' => $parent->id,
            'role' => UserRole::Parent->value,
            'status' => 'inactive',
        ]);
    }

    public function test_student_update_rejects_unknown_account_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $studentUser = User::factory()->create([
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'name' => 'Amina Yusuf',
            'role' => UserRole::Student,
            'status' => 'active',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-100',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.students.update', $student), [
            'status' => 'suspended',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('users', ['id' => $studentUser->id, 'status' => 'active']);
        $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'active']);
    }

    public function test_student_update_without_status_preserves_inactive_state(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $studentUser = User::factory()->create([
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'name' => 'Amina Yusuf',
            'email' => 'amina@example.test',
            'role' => UserRole::Student,
            'status' => 'inactive',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-101',
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.students.update', $student), [
            'first_name' => 'Amina',
            'last_name' => 'Bello',
            'email' => 'amina@example.test',
            'admission_no' => 'ADM-101',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $studentUser->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'inactive']);
    }

    public function test_staff_update_rejects_unknown_account_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $staffUser = User::factory()->create([
            'role' => UserRole::Teacher,
            'status' => 'active',
        ]);
        $staffProfile = StaffProfile::create([
            'user_id' => $staffUser->id,
            'employee_no' => 'STF-100',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.staff.update', $staffProfile), [
            'status' => 'disabled',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('users', ['id' => $staffUser->id, 'status' => 'active']);
        $this->assertDatabaseHas('staff_profiles', ['id' => $staffProfile->id, 'status' => 'active']);
    }

    public function test_staff_update_without_status_preserves_inactive_state(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $staffUser = User::factory()->create([
            'first_name' => 'Daniel',
            'last_name' => 'Adeyemi',
            'name' => 'Daniel Adeyemi',
            'email' => 'daniel@example.test',
            'role' => UserRole::Teacher,
            'status' => 'inactive',
        ]);
        $staffProfile = StaffProfile::create([
            'user_id' => $staffUser->id,
            'employee_no' => 'STF-101',
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.staff.update', $staffProfile), [
            'first_name' => 'Daniel',
            'last_name' => 'Adebayo',
            'email' => 'daniel@example.test',
            'role' => 'teacher',
            'employee_no' => 'STF-101',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $staffUser->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('staff_profiles', ['id' => $staffProfile->id, 'status' => 'inactive']);
    }
}
