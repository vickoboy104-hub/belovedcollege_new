<?php

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\FeeInvoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BankTransferController extends Controller
{
    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['integer', 'exists:fee_invoices,id'],
            'bank_reference' => ['required', 'string', 'max:255'],
        ]);

        $invoices = FeeInvoice::query()
            ->with('student.user')
            ->whereIn('id', $validated['invoice_ids'])
            ->get()
            ->filter(fn (FeeInvoice $invoice) => (float) $invoice->balance > 0)
            ->values();

        abort_if($invoices->isEmpty(), 422, 'Select at least one unpaid fee item.');
        abort_if($invoices->pluck('student_id')->unique()->count() !== 1, 422, 'Selected fee items must belong to one student.');

        foreach ($invoices as $invoice) {
            $this->authorizeInvoiceAccess($request, $invoice);
        }

        $existingClaim = Payment::query()
            ->where('student_id', $invoices->first()->student_id)
            ->where('provider', PaymentProvider::Manual)
            ->where('status', PaymentStatus::Pending)
            ->where('channel', 'bank-transfer')
            ->where('payload->bank_reference', trim($validated['bank_reference']))
            ->exists();

        if ($existingClaim) {
            return back()->withErrors(['payment' => 'This bank transfer reference has already been submitted for verification.']);
        }

        Payment::create([
            'student_id' => $invoices->first()->student_id,
            'provider' => PaymentProvider::Manual,
            'reference' => 'TRF-'.Str::upper(Str::random(12)),
            'amount' => $invoices->sum(fn (FeeInvoice $invoice) => (float) $invoice->balance),
            'currency' => 'NGN',
            'status' => PaymentStatus::Pending,
            'channel' => 'bank-transfer',
            'payload' => [
                'source' => 'bank_transfer_claim',
                'bank_reference' => trim($validated['bank_reference']),
                'invoice_ids' => $invoices->pluck('id')->values()->all(),
                'submitted_at' => now()->toIso8601String(),
            ],
        ]);

        return back()->with('status', 'Bank transfer submitted for bursary verification. Your invoice will update after confirmation.');
    }

    public function index(): View
    {
        $transfers = Payment::query()
            ->with('student.user', 'student.schoolClass')
            ->where('provider', PaymentProvider::Manual)
            ->where('channel', 'bank-transfer')
            ->where('status', PaymentStatus::Pending)
            ->where('payload->source', 'bank_transfer_claim')
            ->latest()
            ->get();

        return view('admin.finance.bank-transfers', compact('transfers'));
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless(
            $payment->provider === PaymentProvider::Manual
            && $payment->status === PaymentStatus::Pending
            && $payment->channel === 'bank-transfer'
            && data_get($payment->payload, 'source') === 'bank_transfer_claim',
            422,
            'This payment is not a pending bank transfer claim.'
        );

        $payment->update([
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'recorded_by' => $request->user()->id,
            'receipt_no' => $payment->receipt_no ?: 'RCP-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'payload' => array_merge($payment->payload ?? [], [
                'verified_at' => now()->toIso8601String(),
                'verified_by' => $request->user()->id,
            ]),
        ]);

        if ($payment->feeInvoice) {
            $payment->feeInvoice->syncBalance();
        } else {
            $payment->allocateBundleInvoices();
        }

        return back()->with('status', 'Bank transfer verified and student balance updated.');
    }

    protected function authorizeInvoiceAccess(Request $request, FeeInvoice $invoice): void
    {
        $user = $request->user();

        if ($user->hasAnyRole(UserRole::Student) && $invoice->student->user_id === $user->id) {
            return;
        }

        if ($user->hasAnyRole(UserRole::Parent) && $invoice->student->parent_user_id === $user->id) {
            return;
        }

        abort(403);
    }
}
