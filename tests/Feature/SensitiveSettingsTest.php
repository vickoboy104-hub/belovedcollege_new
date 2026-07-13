<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SensitiveSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_settings_are_encrypted_and_not_exposed_to_views(): void
    {
        $secret = 'sk_test_secret_that_must_not_render';
        Setting::setMany([
            'school_name' => 'Beloved Schools',
            'paystack_secret_key' => $secret,
            'mail_password' => 'mail-secret-value',
        ], 'payments');

        $stored = (string) DB::table('settings')
            ->where('key', 'paystack_secret_key')
            ->value('value');

        $this->assertNotSame($secret, $stored);
        $this->assertStringStartsWith('encrypted:', $stored);
        $this->assertSame($secret, Setting::getValue('paystack_secret_key'));
        $this->assertArrayNotHasKey('paystack_secret_key', Setting::publicSettings());
        $this->assertSame('', Setting::forAdminForm()['paystack_secret_key']);

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.settings', ['section' => 'payment-settings']))
            ->assertOk()
            ->assertDontSee($secret, false)
            ->assertDontSee('mail-secret-value', false);
    }

    public function test_blank_admin_submission_does_not_erase_an_existing_secret(): void
    {
        Setting::setMany(['paystack_secret_key' => 'existing-secret'], 'payments');
        $before = DB::table('settings')->where('key', 'paystack_secret_key')->value('value');

        Setting::setMany(['paystack_secret_key' => ''], 'payments');

        $after = DB::table('settings')->where('key', 'paystack_secret_key')->value('value');
        $this->assertSame($before, $after);
        $this->assertSame('existing-secret', Setting::getValue('paystack_secret_key'));
    }
}
