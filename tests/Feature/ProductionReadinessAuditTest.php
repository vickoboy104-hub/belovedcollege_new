<?php

namespace Tests\Feature;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\AcademicSession;
use App\Models\Assessment;
use App\Models\CbtAttempt;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductionReadinessAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_website_and_login_entry_points_render(): void
    {
        foreach (['home', 'about', 'admissions', 'contact', 'reports.checker', 'login', 'student.login', 'staff.login'] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_public_self_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Unapproved User',
            'email' => 'unapproved@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'unapproved@example.test']);
    }

    public function test_inactive_accounts_cannot_log_in_or_keep_an_existing_session(): void
    {
        $inactive = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'status' => 'inactive',
            'password' => 'Password123!',
        ]);

        $this->post(route('student.login.store'), [
            'login' => $inactive->email,
            'password' => 'Password123!',
            'audience' => 'student',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();

        $this->actingAs($inactive)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_role_boundaries_block_cross_workspace_access(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $accountant = User::factory()->create([
            'role' => UserRole::Accountant,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $studentUser = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $session = AcademicSession::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-07-31',
            'is_current' => true,
        ]);

        Student::create([
            'user_id' => $studentUser->id,
            'academic_session_id' => $session->id,
            'admission_no' => 'AUDIT-001',
            'student_id_no' => 'AUDIT-STD-001',
            'status' => 'active',
        ]);

        $this->actingAs($teacher)->get(route('admin.people'))->assertForbidden();
        $this->actingAs($accountant)->get(route('admin.people'))->assertForbidden();
        $this->actingAs($studentUser)->get(route('admin.finance'))->assertForbidden();
        $this->actingAs($studentUser)->get(route('portal.index'))->assertOk();
    }

    public function test_palmpay_query_parameters_cannot_mark_a_payment_as_paid(): void
    {
        $payment = $this->createPayment(PaymentProvider::PalmPay, 125000);

        $this->get(route('payments.callback', [
            'provider' => 'palmpay',
            'reference' => $payment->reference,
            'status' => 'success',
            'gateway_reference' => 'FORGED-TRANSACTION',
        ]))->assertRedirect(route('dashboard'));

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
    }

    public function test_callback_provider_must_match_the_original_payment_provider(): void
    {
        $payment = $this->createPayment(PaymentProvider::Paystack, 50000);

        $this->get(route('payments.callback', [
            'provider' => 'palmpay',
            'reference' => $payment->reference,
            'status' => 'success',
        ]))->assertNotFound();

        $this->assertSame(PaymentStatus::Pending, $payment->fresh()->status);
    }

    public function test_paystack_callback_requires_matching_reference_amount_and_currency(): void
    {
        Setting::setMany(['paystack_secret_key' => 'sk_test_audit'], 'payments');
        $payment = $this->createPayment(PaymentProvider::Paystack, 75000);

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => $payment->reference,
                    'amount' => 100,
                    'currency' => 'NGN',
                    'channel' => 'card',
                ],
            ]),
        ]);

        $this->get(route('payments.callback', [
            'provider' => 'paystack',
            'reference' => $payment->reference,
        ]))->assertRedirect(route('dashboard'));

        $this->assertSame(PaymentStatus::Failed, $payment->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
    }

    public function test_verified_paystack_payment_can_be_completed(): void
    {
        Setting::setMany(['paystack_secret_key' => 'sk_test_audit'], 'payments');
        $payment = $this->createPayment(PaymentProvider::Paystack, 75000);

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => $payment->reference,
                    'amount' => 7500000,
                    'currency' => 'NGN',
                    'channel' => 'card',
                ],
            ]),
        ]);

        $this->get(route('payments.callback', [
            'provider' => 'paystack',
            'reference' => $payment->reference,
        ]))->assertRedirect(route('payments.receipt', $payment));

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_expired_cbt_attempt_cannot_be_submitted(): void
    {
        $teacher = User::factory()->create([
            'role' => UserRole::Teacher,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $studentUser = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $class = SchoolClass::create([
            'name' => 'JSS 1',
            'slug' => 'jss-1-general',
            'section' => 'General',
        ]);
        $subject = Subject::create([
            'name' => 'Mathematics',
            'code' => 'MTH-AUDIT',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'school_class_id' => $class->id,
            'admission_no' => 'CBT-AUDIT-001',
            'student_id_no' => 'CBT-STD-001',
            'status' => 'active',
        ]);
        $assessment = Assessment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'title' => 'Expired CBT Audit',
            'type' => 'exam',
            'is_cbt' => true,
            'total_score' => 10,
            'cbt_duration_minutes' => 30,
            'cbt_is_active' => true,
            'cbt_starts_at' => now()->subHour(),
            'cbt_ends_at' => now()->addHour(),
        ]);
        $attempt = CbtAttempt::create([
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'status' => 'in_progress',
            'started_at' => now()->subHour(),
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($studentUser)
            ->post(route('portal.cbt.submit', $assessment), ['answers' => []])
            ->assertRedirect(route('portal.index'));

        $this->assertSame('expired', $attempt->fresh()->status);
        $this->assertNull($attempt->fresh()->submitted_at);
    }

    protected function createPayment(PaymentProvider $provider, float $amount): Payment
    {
        $studentUser = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'PAY-'.strtoupper($provider->value).'-'.random_int(1000, 9999),
            'student_id_no' => 'PAY-STU-'.random_int(1000, 9999),
            'status' => 'active',
        ]);

        return Payment::create([
            'student_id' => $student->id,
            'provider' => $provider,
            'reference' => strtoupper($provider->value).'-AUDIT-'.random_int(100000, 999999),
            'amount' => $amount,
            'currency' => 'NGN',
            'status' => PaymentStatus::Pending,
            'payload' => ['source' => 'audit-test'],
        ]);
    }
}
