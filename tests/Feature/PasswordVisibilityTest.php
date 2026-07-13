<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_login_variant_has_a_password_visibility_control(): void
    {
        foreach (['login', 'student.login', 'staff.login'] as $routeName) {
            $response = $this->get(route($routeName));

            $response->assertOk();
            $response->assertSee('data-password-input', false);
            $response->assertSee('data-password-toggle', false);
            $this->assertSame(1, substr_count($response->getContent(), 'data-password-toggle'));
        }
    }

    public function test_reset_registration_and_confirmation_forms_have_visibility_controls(): void
    {
        $reset = $this->get(route('password.reset', [
            'token' => 'test-token',
            'email' => 'student@example.test',
        ]));

        $reset->assertOk();
        $this->assertSame(2, substr_count($reset->getContent(), 'data-password-toggle'));

        $registration = $this->get(route('register'));
        $registration->assertOk();
        $this->assertSame(2, substr_count($registration->getContent(), 'data-password-toggle'));

        $user = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
        ]);

        $confirmation = $this->actingAs($user)->get(route('password.confirm'));
        $confirmation->assertOk();
        $this->assertSame(1, substr_count($confirmation->getContent(), 'data-password-toggle'));
    }

    public function test_profile_password_update_has_three_independent_visibility_controls(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('update_password_current_password', false);
        $response->assertSee('update_password_password', false);
        $response->assertSee('update_password_password_confirmation', false);
        $this->assertSame(3, substr_count($response->getContent(), 'data-password-toggle'));
    }
}
