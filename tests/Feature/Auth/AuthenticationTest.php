<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_student_without_email_authenticates_as_the_exact_admission_number_owner(): void
    {
        $otherUser = User::factory()->create([
            'email' => null,
            'role' => UserRole::Student,
            'password' => 'shared-password',
            'status' => 'active',
        ]);
        Student::create([
            'user_id' => $otherUser->id,
            'admission_no' => 'ADM-OTHER',
        ]);

        $targetUser = User::factory()->create([
            'email' => null,
            'role' => UserRole::Student,
            'password' => 'shared-password',
            'status' => 'active',
        ]);
        Student::create([
            'user_id' => $targetUser->id,
            'admission_no' => 'ADM-TARGET',
        ]);

        $response = $this->post(route('student.login.store'), [
            'login' => 'ADM-TARGET',
            'password' => 'shared-password',
            'audience' => 'student',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($targetUser);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
