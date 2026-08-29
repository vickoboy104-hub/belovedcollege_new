<?php

namespace Tests\Feature;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\FeeInvoice;
use App\Models\FeeItem;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\Payments\PaymentMethodResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancePaymentUpgradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_method_resolves_to_enabled_paystack_without_exposing_gateway_choice_to_student(): void
    {
        Setting::setMany([
            'enabled_payment_gateways' => 'paystack',
            'paystack_secret_key' => 'sk_test_finance_upgrade',
        ], 'payments');

        $resolver = app(PaymentMethodResolver::class);

        $this->assertSame(PaymentProvider::Paystack, $resolver->providerFor('card'));
        $this->assertSame(PaymentProvider::Paystack, $resolver->providerFor('ussd'));
    }

    public function test_student_billing_section_renders_customer_payment_methods(): void
    {
        [$user, $student, $invoice] = $this->studentWithInvoice(125000);

        Setting::setMany([
            'enabled_payment_gateways' => 'paystack',
            'paystack_secret_key' => 'sk_test_finance_upgrade',
            'bank_name_1' => 'Beloved Test Bank',
            'account_name_1' => 'Beloved Schools',
            'account_number_1' => '0123456789',
        ], 'payments');

        $response = $this->actingAs($user)->get(route('portal.index', ['section' => 'billing']));

        $response->assertOk();
        $response->assertSee('Choose Payment Method');
        $response->assertSee('Card Payment');
        $response->assertSee('Bank Transfer');
        $response->assertSee('USSD');
        $response->assertSee('Wallet');
        $response->assertSee($invoice->invoice_no);
        $response->assertDontSee('Continue securely with the school&#039;s Paystack checkout', false);
    }

    public function test_bank_transfer_claim_stays_pending_until_finance_verifies_it(): void
    {
        [$studentUser, $student, $invoice] = $this->studentWithInvoice(50000);

        $this->actingAs($studentUser)
            ->post(route('payments.bank-transfer.submit'), [
                'invoice_ids' => [$invoice->id],
                'bank_reference' => 'BANK-REF-001',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $claim = Payment::query()->where('student_id', $student->id)->firstOrFail();
        $this->assertSame(PaymentProvider::Manual, $claim->provider);
        $this->assertSame(PaymentStatus::Pending, $claim->status);
        $this->assertSame('bank-transfer', $claim->channel);
        $this->assertSame(50000.0, (float) $invoice->fresh()->balance);

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bank-transfers.verify', $claim))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(PaymentStatus::Paid, $claim->fresh()->status);
        $this->assertSame(0.0, (float) $invoice->fresh()->balance);
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_finance_staff_can_record_partial_cash_payment_in_same_ledger(): void
    {
        [$studentUser, $student, $invoice] = $this->studentWithInvoice(40000);
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.manual-payments.store'), [
                'fee_invoice_id' => $invoice->id,
                'amount' => 15000,
                'payment_method' => 'cash',
                'reference' => 'CASH-001',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $payment = Payment::query()->where('fee_invoice_id', $invoice->id)->firstOrFail();
        $this->assertSame(PaymentProvider::Manual, $payment->provider);
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame('cash', $payment->channel);
        $this->assertSame(25000.0, (float) $invoice->fresh()->balance);
        $this->assertSame('part-paid', $invoice->fresh()->status);
    }

    public function test_admin_can_open_a4_invoice_print_view(): void
    {
        [$studentUser, $student, $invoice] = $this->studentWithInvoice(30000);
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.invoices.print', $invoice))
            ->assertOk()
            ->assertSee('Student Invoice')
            ->assertSee($invoice->invoice_no)
            ->assertSee('Print / Save as PDF');
    }

    public function test_admin_record_payment_route_opens_upgraded_monitoring_desk(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get('/admin/finance/record-payment')
            ->assertOk()
            ->assertSee('Payments Today')
            ->assertSee('Successful Transactions')
            ->assertSee('Pending Payments')
            ->assertSee('Outstanding Balance')
            ->assertSee('Cash')
            ->assertSee('Bank Transfer')
            ->assertSee('POS');
    }

    protected function studentWithInvoice(float $amount): array
    {
        $class = SchoolClass::create([
            'name' => 'JSS 1',
            'slug' => 'jss-1-finance-test',
            'section' => 'A',
        ]);
        $user = User::factory()->create([
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'status' => 'active',
            'first_name' => 'Daniel',
            'last_name' => 'Adeyemi',
            'name' => 'Daniel Adeyemi',
        ]);
        $student = Student::create([
            'user_id' => $user->id,
            'admission_no' => 'BEL-FIN-001',
            'student_id_no' => 'BEL-FIN-STU-001',
            'school_class_id' => $class->id,
            'status' => 'active',
        ]);
        $feeItem = FeeItem::create([
            'name' => 'School Fees',
            'school_class_id' => $class->id,
            'amount' => $amount,
            'is_mandatory' => true,
        ]);
        $invoice = FeeInvoice::create([
            'invoice_no' => 'INV-FIN-001',
            'student_id' => $student->id,
            'fee_item_id' => $feeItem->id,
            'amount_due' => $amount,
            'amount_paid' => 0,
            'balance' => $amount,
            'status' => 'unpaid',
            'issued_at' => now(),
        ]);

        return [$user, $student, $invoice];
    }
}
