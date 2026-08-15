<?php

namespace Tests\Feature;

use App\Services\Payments\PaymentGatewayManager;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSafetyRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_login_exports_are_not_present_in_the_repository(): void
    {
        $this->assertFileDoesNotExist(base_path('storage/exports/staff-logins.csv'));
        $this->assertFileDoesNotExist(base_path('storage/exports/student-logins.csv'));
    }

    public function test_palmpay_remains_unavailable_without_authoritative_verification(): void
    {
        Setting::setMany([
            'enabled_payment_gateways' => 'palmpay',
            'palmpay_checkout_url' => 'https://merchant.example.test/checkout',
            'palmpay_merchant_id' => 'merchant-test',
            'palmpay_private_key' => 'private-test',
        ], 'payments');

        $this->assertFalse(app(PaymentGatewayManager::class)->isAvailable('palmpay'));
    }

    public function test_public_forms_and_slider_controls_have_accessible_names(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('aria-label="Slide previews"', false)
            ->assertSee('aria-label="Choose a slide"', false);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('for="contact-name"', false)
            ->assertSee('for="contact-email"', false)
            ->assertSee('for="contact-message"', false);
    }

    public function test_login_layout_uses_an_escaped_background_style(): void
    {
        $source = file_get_contents(resource_path('views/layouts/guest.blade.php'));

        $this->assertIsString($source);
        $this->assertStringNotContainsString('{!! $authShellStyle !!}', $source);
        $this->assertStringContainsString("{{ asset(\$authImage) }}", $source);
    }
}
