<?php

namespace Tests\Feature;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BundlePaymentAllocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bundle_payment_allocation_is_idempotent(): void
    {
        $studentUser = User::factory()->create(['role' => UserRole::Student]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'ADM-BUNDLE-001',
        ]);

        $first = FeeInvoice::create([
            'invoice_no' => 'INV-BUNDLE-001',
            'student_id' => $student->id,
            'amount_due' => 3000,
            'amount_paid' => 0,
            'balance' => 3000,
            'status' => 'unpaid',
            'issued_at' => now(),
        ]);
        $second = FeeInvoice::create([
            'invoice_no' => 'INV-BUNDLE-002',
            'student_id' => $student->id,
            'amount_due' => 2000,
            'amount_paid' => 0,
            'balance' => 2000,
            'status' => 'unpaid',
            'issued_at' => now(),
        ]);

        $payment = Payment::create([
            'student_id' => $student->id,
            'provider' => PaymentProvider::Paystack,
            'reference' => 'PAYSTACK-BUNDLE-TEST',
            'receipt_no' => 'RCP-BUNDLE-TEST',
            'amount' => 5000,
            'currency' => 'NGN',
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'payload' => [
                'source' => 'bundle_checkout',
                'invoice_ids' => [$first->id, $second->id],
            ],
        ]);

        $payment->allocateBundleInvoices();
        $payment->allocateBundleInvoices();

        $allocations = Payment::query()
            ->where('student_id', $student->id)
            ->get()
            ->filter(fn (Payment $row) => data_get($row->payload, 'source') === 'bundle_allocation');

        $this->assertCount(2, $allocations);
        $this->assertSame(0.0, (float) $first->fresh()->balance);
        $this->assertSame(0.0, (float) $second->fresh()->balance);
        $this->assertTrue((bool) data_get($payment->fresh()->payload, 'bundle_allocated'));
    }
}
