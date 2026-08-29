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
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fee_invoice_id' => ['required', 'exists:fee_invoices,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:cash,bank-transfer,pos'],
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
            'channel' => $validated['payment_method'],
            'paid_at' => $validated['paid_at'] ?? now(),
            'recorded_by' => $request->user()->id,
            'note' => $validated['note'] ?? $methodLabels[$validated['payment_method']].' payment recorded at the finance desk.',
            'payload' => [
                'source' => 'manual_finance_entry',
                'payment_method' => $validated['payment_method'],
                'payment_method_label' => $methodLabels[$validated['payment_method']],
                'recorded_amount' => (float) $validated['amount'],
                'invoice_balance_before_payment' => (float) $invoice->balance,
                'overpayment_amount' => max((float) $validated['amount'] - (float) $invoice->balance, 0),
            ],
        ]);

        $invoice->refresh()->syncBalance();

        return back()->with('status', $methodLabels[$validated['payment_method']].' payment recorded successfully.');
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
}
