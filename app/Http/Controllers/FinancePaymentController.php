<?php

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\FeeInvoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FinancePaymentController extends Controller
{
    public function desk(): View
    {
        $invoices = FeeInvoice::query()
            ->with('student.user', 'student.schoolClass', 'feeItem')
            ->where('balance', '>', 0)
            ->latest('issued_at')
            ->get();

        $payments = Payment::query()
            ->with('student.user', 'student.schoolClass')
            ->latest()
            ->get()
            ->reject(fn (Payment $payment) => data_get($payment->payload, 'source') === 'bundle_allocation')
            ->values();

        $metrics = [
            'paymentsToday' => $payments
                ->filter(fn (Payment $payment) => $payment->status === PaymentStatus::Paid && $payment->paid_at?->isToday())
                ->sum(fn (Payment $payment) => (float) $payment->amount),
            'successfulTransactions' => $payments->filter(fn (Payment $payment) => $payment->status === PaymentStatus::Paid)->count(),
            'pendingPayments' => $payments->filter(fn (Payment $payment) => in_array($payment->status, [PaymentStatus::Initialized, PaymentStatus::Pending], true))->count(),
            'outstandingBalance' => (float) $invoices->sum('balance'),
        ];

        return view('admin.finance.payment-desk', [
            'invoices' => $invoices,
            'payments' => $payments->take(20),
            'metrics' => $metrics,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fee_invoice_id' => ['required', 'exists:fee_invoices,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['nullable', 'in:cash,bank-transfer,pos'],
            'provider' => ['nullable', 'in:manual,paystack,palmpay'],
            'channel' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice = FeeInvoice::with('student')->findOrFail($validated['fee_invoice_id']);

        if ((float) $invoice->balance <= 0) {
            return back()->withErrors([
                'fee_invoice_id' => 'This invoice has already been settled.',
            ])->withInput();
        }

        $paymentMethod = $validated['payment_method'] ?? $this->legacyPaymentMethod($validated['channel'] ?? null);
        $methodLabels = [
            'cash' => 'Cash',
            'bank-transfer' => 'Bank Transfer',
            'pos' => 'POS',
        ];

        $reference = filled($validated['reference'] ?? null)
            ? trim((string) $validated['reference'])
            : 'MAN-'.Str::upper(Str::random(10));

        Payment::create([
            'fee_invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'provider' => PaymentProvider::Manual,
            'reference' => $reference,
            'receipt_no' => 'RCP-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'amount' => $validated['amount'],
            'currency' => 'NGN',
            'status' => PaymentStatus::Paid,
            'channel' => $paymentMethod,
            'paid_at' => $validated['paid_at'] ?? now(),
            'recorded_by' => $request->user()->id,
            'note' => $validated['note'] ?? $methodLabels[$paymentMethod].' payment recorded at the finance desk.',
            'payload' => [
                'source' => 'manual_finance_entry',
                'payment_method' => $paymentMethod,
                'payment_method_label' => $methodLabels[$paymentMethod],
                'legacy_channel_note' => $validated['channel'] ?? null,
                'recorded_amount' => (float) $validated['amount'],
                'invoice_balance_before_payment' => (float) $invoice->balance,
                'overpayment_amount' => max((float) $validated['amount'] - (float) $invoice->balance, 0),
            ],
        ]);

        $invoice->refresh()->syncBalance();

        return back()->with('status', $methodLabels[$paymentMethod].' payment recorded successfully.');
    }

    public function printInvoice(FeeInvoice $invoice): View
    {
        $invoice->load([
            'student.user',
            'student.schoolClass',
            'student.academicSession',
            'feeItem.academicSession',
            'feeItem.term',
            'payments.recorder',
        ]);

        return view('admin.finance.invoice-print', compact('invoice'));
    }

    protected function legacyPaymentMethod(?string $channel): string
    {
        $channel = strtolower(trim((string) $channel));

        if (str_contains($channel, 'pos')) {
            return 'pos';
        }

        if (str_contains($channel, 'bank') || str_contains($channel, 'transfer') || str_contains($channel, 'teller')) {
            return 'bank-transfer';
        }

        return 'cash';
    }
}
