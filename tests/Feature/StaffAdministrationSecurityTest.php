<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAdministrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_principal_cannot_create_an_administrator_account(): void
    {
        $principal = User::factory()->create(['role' => UserRole::Principal]);

        $response = $this->actingAs($principal)->post(route('admin.staff.store'), [
            'first_name' => 'Protected',
            'last_name' => 'Admin',
            'email' => 'protected.admin@example.test',
            'password' => 'SecurePass123!',
            'role' => 'admin',
            'employee_no' => 'STF-ADMIN-001',
            'department' => 'Operations',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'protected.admin@example.test']);
    }

    public function test_principal_cannot_promote_staff_to_administrator(): void
    {
        $principal = User::factory()->create(['role' => UserRole::Principal]);
        $teacher = User::factory()->create([
            'first_name' => 'Daniel',
            'last_name' => 'Adeyemi',
            'name' => 'Daniel Adeyemi',
            'email' => 'daniel@example.test',
            'role' => UserRole::Teacher,
        ]);
        $profile = StaffProfile::create([
            'user_id' => $teacher->id,
            'employee_no' => 'STF-001',
            'department' => 'Sciences',
            'status' => 'active',
        ]);

        $response = $this->actingAs($principal)->patch(route('admin.staff.update', $profile), [
            'first_name' => 'Daniel',
            'middle_name' => null,
            'last_name' => 'Adeyemi',
            'email' => 'daniel@example.test',
            'phone' => null,
            'role' => 'admin',
            'employee_no' => 'STF-001',
            'department' => 'Sciences',
            'designation' => 'Teacher',
            'qualification' => null,
            'hire_date' => null,
            'salary' => null,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseHas('users', ['id' => $teacher->id, 'role' => UserRole::Teacher->value]);
    }

    public function test_principal_cannot_reset_deactivate_or_delete_an_administrator_account(): void
    {
        $principal = User::factory()->create(['role' => UserRole::Principal]);
        $administrator = User::factory()->create([
            'email' => 'administrator@example.test',
            'role' => UserRole::Admin,
            'status' => 'active',
        ]);
        $profile = StaffProfile::create([
            'user_id' => $administrator->id,
            'employee_no' => 'STF-ADMIN-002',
            'department' => 'Operations',
            'status' => 'active',
        ]);

        $this->actingAs($principal)
            ->post(route('admin.staff.password.reset', $profile))
            ->assertForbidden();

        $this->actingAs($principal)
            ->patch(route('admin.staff.deactivate', $profile))
            ->assertForbidden();

        $this->actingAs($principal)
            ->delete(route('admin.staff.destroy', $profile))
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
            'role' => UserRole::Admin->value,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('staff_profiles', ['id' => $profile->id, 'status' => 'active']);
    }

    public function test_staff_manager_cannot_deactivate_delete_or_demote_their_own_account(): void
    {
        $administrator = User::factory()->create([
            'first_name' => 'Platform',
            'last_name' => 'Administrator',
            'name' => 'Platform Administrator',
            'email' => 'platform@example.test',
            'role' => UserRole::Admin,
            'status' => 'active',
        ]);
        $profile = StaffProfile::create([
            'user_id' => $administrator->id,
            'employee_no' => 'STF-SELF-001',
            'department' => 'Operations',
            'status' => 'active',
        ]);

        $this->actingAs($administrator)
            ->patch(route('admin.staff.deactivate', $profile))
            ->assertForbidden();

        $this->actingAs($administrator)
            ->delete(route('admin.staff.destroy', $profile))
            ->assertForbidden();

        $response = $this->actingAs($administrator)->patch(route('admin.staff.update', $profile), [
            'first_name' => 'Platform',
            'middle_name' => null,
            'last_name' => 'Administrator',
            'email' => 'platform@example.test',
            'phone' => null,
            'role' => 'teacher',
            'employee_no' => 'STF-SELF-001',
            'department' => 'Operations',
            'designation' => 'Platform Administrator',
            'qualification' => null,
            'hire_date' => null,
            'salary' => null,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
            'role' => UserRole::Admin->value,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('staff_profiles', ['id' => $profile->id, 'status' => 'active']);
    }
}
