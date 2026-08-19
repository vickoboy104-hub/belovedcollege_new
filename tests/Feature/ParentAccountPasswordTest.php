<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ParentAccountPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_one_time_parent_password_without_storing_plaintext(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $parent = User::factory()->create([
            'name' => 'Mrs Yusuf',
            'email' => 'parent@example.test',
            'role' => UserRole::Parent,
            'password' => 'old-password',
            'must_change_password' => false,
        ]);
        $studentUser = User::factory()->create(['role' => UserRole::Student]);
        Student::create([
            'user_id' => $studentUser->id,
            'parent_user_id' => $parent->id,
            'admission_no' => 'ADM-PARENT-001',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.parents.password.reset', $parent), [
            'redirect_search' => 'parent@example.test',
        ]);

        $response->assertRedirect(route('admin.parents.index', ['search' => 'parent@example.test']));
        $response->assertSessionHas('generated_parent_credentials');

        $credentials = session('generated_parent_credentials');
        $this->assertSame('parent@example.test', $credentials['email']);
        $this->assertNotEmpty($credentials['password']);

        $parent->refresh();
        $this->assertTrue($parent->must_change_password);
        $this->assertTrue(Hash::check($credentials['password'], $parent->password));
        $this->assertNull($parent->temp_password_plaintext);
        $this->assertNull($parent->temp_password_generated_at);
    }

    public function test_non_parent_account_cannot_use_parent_password_reset_route(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'email' => 'teacher@example.test',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.parents.password.reset', $teacher))
            ->assertNotFound();
    }

    public function test_parent_without_email_cannot_receive_portal_credentials(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $parent = User::factory()->create([
            'role' => UserRole::Parent,
            'email' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.parents.password.reset', $parent))
            ->assertStatus(422);
    }
}
