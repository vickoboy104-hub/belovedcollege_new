<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_password_is_not_retained_and_user_must_change_it(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Student,
            'password' => 'TemporaryPass123!',
            'temp_password_plaintext' => 'TemporaryPass123!',
            'temp_password_generated_at' => now(),
        ]);

        $this->assertTrue($user->fresh()->must_change_password);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'temp_password_plaintext' => null,
            'temp_password_generated_at' => null,
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('profile.edit'));
    }

    public function test_replacing_temporary_password_unlocks_the_application(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
            'password' => Hash::make('OldPassword123!'),
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword456!',
                'password_confirmation' => 'NewPassword456!',
            ])
            ->assertSessionHasNoErrors();

        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('NewPassword456!', $user->fresh()->password));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'route' => 'password.update',
            'method' => 'PUT',
        ]);
    }

    public function test_passport_uploads_are_moved_out_of_public_storage(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => UserRole::Student]);
        $publicDirectory = public_path('uploads/settings');
        File::ensureDirectoryExists($publicDirectory);
        $publicFile = $publicDirectory.'/student-passport-security-test.jpg';
        File::put($publicFile, 'private-image-content');

        $user->update([
            'avatar_url' => 'uploads/settings/student-passport-security-test.jpg',
        ]);

        $user->refresh();
        $this->assertFileDoesNotExist($publicFile);
        $this->assertNotNull($user->avatar_path);
        Storage::disk('local')->assertExists($user->avatar_path);
        $this->assertSame('/private-media/users/'.$user->id.'/avatar', $user->avatar_url);

        $response = $this->actingAs($user)
            ->get(route('private-media.avatar', $user));

        $response->assertOk()->assertHeader('Cache-Control');
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $otherUser = User::factory()->create(['role' => UserRole::Student]);
        $this->actingAs($otherUser)
            ->get(route('private-media.avatar', $user))
            ->assertForbidden();
    }

    public function test_authenticated_pages_receive_security_headers(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
