<?php

namespace Tests\Feature;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\Payments\FlutterwaveGateway;
use App\Services\Payments\MonnifyGateway;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentGatewayManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_enable_gateways_and_secrets_are_encrypted_and_not_rendered(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.payment-gateways.update'), [
                'enabled_payment_gateways' => ['paystack', 'flutterwave', 'monnify'],
                'paystack_public_key' => 'pk_test_school',
                'paystack_secret_key' => 'sk_test_school_secret',
                'flutterwave_public_key' => 'FLWPUBK_TEST-school',
                'flutterwave_secret_key' => 'FLWSECK_TEST-school-secret',
                'flutterwave_secret_hash' => 'flutterwave-webhook-secret',
                'monnify_api_key' => 'MK_TEST_school',
                'monnify_secret_key' => 'MONNIFY_SECRET_school',
                'monnify_contract_code' => '1234567890',
                'monnify_environment' => 'sandbox',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame('paystack,flutterwave,monnify', Setting::getValue('enabled_payment_gateways'));
        $this->assertSame('sk_test_school_secret', Setting::getValue('paystack_secret_key'));
        $this->assertStringStartsWith(
            'encrypted:',
            (string) DB::table('settings')->where('key', 'paystack_secret_key')->value('value'),
        );
        $this->assertStringStartsWith(
            'encrypted:',
            (string) DB::table('settings')->where('key', 'flutterwave_secret_key')->value('value'),
        );
        $this->assertStringStartsWith(
            'encrypted:',
            (string) DB::table('settings')->where('key', 'monnify_secret_key')->value('value'),
        );

        $page = $this->actingAs($admin)->get(route('admin.payment-gateways.index'));
        $page->assertOk();
        $page->assertSee('Payment Gateways');
        $page->assertDontSee('sk_test_school_secret');
        $page->assertDontSee('FLWSECK_TEST-school-secret');
        $page->assertDontSee('MONNIFY_SECRET_school');
        $page->assertSee(route('webhooks.paystack'));
        $page->assertSee(route('webhooks.flutterwave'));
        $page->assertSee(route('webhooks.monnify'));
    }

    public function test_gateway_catalog_defaults_to_paystack_and_palmpay_but_only_configured_methods_are_available(): void
    {
        Setting::setMany([
            'paystack_secret_key' => 'sk_test_available',
        ], 'payments');

        $manager = app(PaymentGatewayManager::class);
        $catalog = $manager->catalog()->keyBy('value');

        $this->assertTrue($catalog['paystack']['enabled']);
        $this->assertTrue($catalog['paystack']['configured']);
        $this->assertTrue($catalog['paystack']['available']);
        $this->assertTrue($catalog['palmpay']['enabled']);
        $this->assertFalse($catalog['palmpay']['configured']);
        $this->assertFalse($catalog['palmpay']['available']);
        $this->assertFalse($catalog['flutterwave']['enabled']);
        $this->assertFalse($catalog['monnify']['enabled']);
    }

    public function test_flutterwave_hosted_checkout_and_server_verification_are_normalized(): void
    {
        Setting::setMany([
            'flutterwave_secret_key' => 'FLWSECK_TEST_gateway',
            'enabled_payment_gateways' => 'flutterwave',
        ], 'payments');

        Http::fake([
            'https://api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'message' => 'Hosted link generated',
                'data' => ['link' => 'https://checkout.flutterwave.test/pay/abc123'],
            ]),
            'https://api.flutterwave.com/v3/transactions/998877/verify' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 998877,
                    'status' => 'successful',
                    'tx_ref' => 'FLUTTERWAVE-REF-001',
                    'flw_ref' => 'FLW-MOCK-REF',
                    'amount' => 35000,
                    'currency' => 'NGN',
                    'payment_type' => 'card',
                ],
            ]),
        ]);

        [$invoice, $payment] = $this->gatewaySubject(PaymentProvider::Flutterwave, 'FLUTTERWAVE-REF-001', 35000);
        $gateway = app(FlutterwaveGateway::class);

        $initialized = $gateway->initialize($invoice, $payment);
        $this->assertSame('https://checkout.flutterwave.test/pay/abc123', data_get($initialized, 'data.authorization_url'));

        $verified = $gateway->verify($payment->reference, ['transaction_id' => 998877]);
        $this->assertSame('successful', data_get($verified, 'data.status'));
        $this->assertSame($payment->reference, data_get($verified, 'data.reference'));
        $this->assertSame(35000, data_get($verified, 'data.amount'));
        $this->assertSame('NGN', data_get($verified, 'data.currency'));
    }

    public function test_monnify_authentication_checkout_and_verification_are_normalized(): void
    {
        Cache::flush();
        Setting::setMany([
            'monnify_api_key' => 'MK_TEST_gateway',
            'monnify_secret_key' => 'MONNIFY_SECRET_gateway',
            'monnify_contract_code' => '1234567890',
            'monnify_environment' => 'sandbox',
            'enabled_payment_gateways' => 'monnify',
        ], 'payments');

        Http::fake([
            'https://sandbox.monnify.com/api/v1/auth/login' => Http::response([
                'requestSuccessful' => true,
                'responseBody' => [
                    'accessToken' => 'MONNIFY_ACCESS_TOKEN',
                    'expiresIn' => 3600,
                ],
            ]),
            'https://sandbox.monnify.com/api/v1/merchant/transactions/init-transaction' => Http::response([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseBody' => [
                    'checkoutUrl' => 'https://sandbox.monnify.com/checkout/mock',
                    'transactionReference' => 'MNFY-MOCK-001',
                    'paymentReference' => 'MONNIFY-REF-001',
                ],
            ]),
            'https://sandbox.monnify.com/api/v2/transactions/MONNIFY-REF-001' => Http::response([
                'requestSuccessful' => true,
                'responseBody' => [
                    'paymentStatus' => 'PAID',
                    'paymentReference' => 'MONNIFY-REF-001',
                    'transactionReference' => 'MNFY-MOCK-001',
                    'amountPaid' => 42500,
                    'currencyCode' => 'NGN',
                    'paymentMethod' => 'ACCOUNT_TRANSFER',
                    'paidOn' => now()->toIso8601String(),
                ],
            ]),
        ]);

        [$invoice, $payment] = $this->gatewaySubject(PaymentProvider::Monnify, 'MONNIFY-REF-001', 42500);
        $gateway = app(MonnifyGateway::class);

        $initialized = $gateway->initialize($invoice, $payment);
        $this->assertSame('https://sandbox.monnify.com/checkout/mock', data_get($initialized, 'data.authorization_url'));

        $verified = $gateway->verify($payment->reference);
        $this->assertSame('PAID', data_get($verified, 'data.status'));
        $this->assertSame($payment->reference, data_get($verified, 'data.reference'));
        $this->assertEquals(42500, data_get($verified, 'data.amount'));
        $this->assertSame('NGN', data_get($verified, 'data.currency'));
    }

    public function test_student_cannot_submit_or_replace_an_assignment_after_its_deadline(): void
    {
        $teacher = User::factory()->create(['role' => UserRole::Teacher]);
        $studentUser = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $schoolClass = SchoolClass::create([
            'name' => 'JSS 2',
            'slug' => 'jss-2-general',
            'section' => 'General',
        ]);
        $subject = Subject::create([
            'name' => 'Basic Science',
            'code' => 'BSC',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'school_class_id' => $schoolClass->id,
            'admission_no' => 'LATE-001',
            'student_id_no' => 'LATE-STUDENT-001',
            'status' => 'active',
        ]);
        $assignment = Assignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'title' => 'Closed assignment',
            'instructions' => 'This work is closed.',
            'due_date' => now()->subMinute(),
            'total_score' => 20,
            'status' => 'published',
        ]);

        $this->actingAs($studentUser)
            ->post(route('portal.assignments.submit', $assignment), [
                'content' => 'A late answer that must be rejected.',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('content');

        $this->assertDatabaseMissing('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
        ]);
        $this->assertSame(0, AssignmentSubmission::query()->count());
    }

    protected function gatewaySubject(PaymentProvider $provider, string $reference, float $amount): array
    {
        $studentUser = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'status' => 'active',
            'name' => 'Gateway Test Student',
            'phone' => '08000000000',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'GATEWAY-'.random_int(1000, 9999),
            'student_id_no' => 'GATEWAY-STUDENT-'.random_int(1000, 9999),
            'status' => 'active',
        ]);
        $student->setRelation('user', $studentUser);
        $payment = Payment::create([
            'student_id' => $student->id,
            'provider' => $provider,
            'reference' => $reference,
            'amount' => $amount,
            'currency' => 'NGN',
            'status' => PaymentStatus::Initialized,
            'payload' => ['invoice_ids' => []],
        ]);
        $invoice = (object) [
            'id' => null,
            'invoice_no' => 'TEST-INVOICE',
            'student_id' => $student->id,
            'student' => $student,
        ];

        return [$invoice, $payment];
    }
}
