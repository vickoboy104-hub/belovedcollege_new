<?php

namespace Tests\Feature;

use App\Enums\PaymentProvider;
use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_five_online_gateway_templates_and_testing_guidance(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get(route('admin.payment-gateways.index'));

        $response->assertOk();
        $response->assertSee('Paystack');
        $response->assertSee('Flutterwave');
        $response->assertSee('Monnify');
        $response->assertSee('OPay');
        $response->assertSee('PalmPay');
        $response->assertSee('Recommended for your first payment test');
        $response->assertSee('pk_test_', false);
        $response->assertSee('Sandbox / Testing');
    }

    public function test_opay_secret_is_a_sensitive_encrypted_setting(): void
    {
        $this->assertTrue(Setting::isSensitive('opay_secret_key'));
    }

    public function test_unconfigured_enabled_gateways_are_not_exposed_to_student_checkout(): void
    {
        Setting::setMany([
            'enabled_payment_gateways' => 'paystack,opay',
        ], 'payments');

        /** @var PaymentGatewayManager $manager */
        $manager = app(PaymentGatewayManager::class);
        $available = $manager->catalog(onlyAvailable: true)->pluck('value')->all();

        $this->assertNotContains(PaymentProvider::Paystack->value, $available);
        $this->assertNotContains(PaymentProvider::OPay->value, $available);
    }

    public function test_opay_callback_and_webhook_routes_are_registered(): void
    {
        $this->assertSame(
            url('/payments/callback/opay'),
            route('payments.callback', PaymentProvider::OPay->value),
        );
        $this->assertSame(url('/webhooks/opay'), route('webhooks.opay'));
    }
}
