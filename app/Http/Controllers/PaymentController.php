<?php

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Services\Payments\PalmPayGateway;
use App\Services\Payments\PaystackGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        protected PaystackGateway $paystackGateway,
        protected PalmPayGateway $palmPayGateway,
    ) {
    }

    public function checkout(Request $request, FeeInvoice $invoice, string $provider): RedirectResponse
    {
        $this->authorizeInvoiceAccess($request->user(), $invoice->load('student.user'));

        $providerEnum = PaymentProvider::from($provider);

        if ((float) $invoice->balance <= 0) {
            return back()->with('status', 'This invoice has already been settled.');
        }

        $payment = Payment::create([
            'fee_invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'provider' => $providerEnum,
            'reference' => Str::upper($providerEnum->value).'-'.Str::upper(Str::random(12)),
            'amount' => $invoice->balance,
            'currency' => 'NGN',
            'status' => PaymentStatus::Initialized,
            'payload' => ['source' => 'single_invoice_checkout', 'invoice_ids' => [$invoice->id]],
        ]);

        try {
            $response = match ($providerEnum) {
                PaymentProvider::Paystack => $this->paystackGateway->initialize($invoice, $payment),
                PaymentProvider::PalmPay => $this->palmPayGateway->initialize($invoice, $payment),
            };

            $payment->update([
                'status' => PaymentStatus::Pending,
                'payload' => $response,
            ]);

            return redirect()->away((string) data_get($response, 'data.authorization_url'));
        } catch (Throwable $exception) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'payload' => ['message' => $exception->getMessage()],
            ]);

            return back()->withErrors(['payment' => $exception->getMessage()]);
        }
    }

    public function checkoutSelection(Request $request, string $provider): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['integer', 'exists:fee_invoices,id'],
        ]);

        $providerEnum = PaymentProvider::from($provider);
        $invoices = FeeInvoice::query()
            ->with('student.user', 'feeItem')
            ->whereIn('id', $validated['invoice_ids'])
            ->get()
            ->filter(fn (FeeInvoice $invoice) => (float) $invoice->balance > 0)
            ->values();

        abort_if($invoices->isEmpty(), 422, 'Select at least one unpaid fee item.');

        $studentId = $invoices->pluck('student_id')->unique();
        abort_if($studentId->count() !== 1, 422, 'Selected fee items must belong to the same student.');

        foreach ($invoices as $invoice) {
            $this->authorizeInvoiceAccess($request->user(), $invoice);
        }

        $primaryInvoice = $invoices->first();
        $payment = Payment::create([
            'student_id' => $primaryInvoice->student_id,
            'provider' => $providerEnum,
            'reference' => Str::upper($providerEnum->value).'-'.Str::upper(Str::random(12)),
            'amount' => $invoices->sum(fn (FeeInvoice $invoice) => (float) $invoice->balance),
            'currency' => 'NGN',
            'status' => PaymentStatus::Initialized,
            'payload' => [
                'source' => 'bundle_checkout',
                'invoice_ids' => $invoices->pluck('id')->values()->all(),
                'bundle_label' => 'Selected fee items',
            ],
        ]);

        $bundleSubject = (object) [
            'id' => null,
            'invoice_no' => 'BUNDLE-'.$primaryInvoice->student->admission_no,
            'student_id' => $primaryInvoice->student_id,
            'student' => $primaryInvoice->student,
        ];

        try {
            $response = match ($providerEnum) {
                PaymentProvider::Paystack => $this->paystackGateway->initialize($bundleSubject, $payment),
                PaymentProvider::PalmPay => $this->palmPayGateway->initialize($bundleSubject, $payment),
            };

            $payment->update([
                'status' => PaymentStatus::Pending,
                'payload' => [...($payment->payload ?? []), 'gateway' => $response],
            ]);

            return redirect()->away((string) data_get($response, 'data.authorization_url'));
        } catch (Throwable $exception) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'payload' => [...($payment->payload ?? []), 'message' => $exception->getMessage()],
            ]);

            return back()->withErrors(['payment' => $exception->getMessage()]);
        }
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $providerEnum = PaymentProvider::from($provider);
        $reference = $request->string('reference')->toString() ?: $request->string('trxref')->toString();
        $payment = Payment::with('feeInvoice')->where('reference', $reference)->firstOrFail();

        try {
            $payload = match ($providerEnum) {
                PaymentProvider::Paystack => $this->paystackGateway->verify($reference),
                PaymentProvider::PalmPay => [
                    'status' => true,
                    'data' => [
                        'reference' => $reference,
                        'status' => $request->string('status')->toString(),
                        'gateway_reference' => $request->string('gateway_reference')->toString(),
                    ],
                ],
            };

            $isSuccessful = match ($providerEnum) {
                PaymentProvider::Paystack => data_get($payload, 'data.status') === 'success',
                PaymentProvider::PalmPay => in_array(strtolower((string) data_get($payload, 'data.status')), ['success', 'paid', 'completed'], true),
            };

            if ($isSuccessful) {
                $payment = $this->markPaymentSuccessful($payment, [
                    'gateway_reference' => data_get($payload, 'data.reference') ?: data_get($payload, 'data.gateway_reference'),
                    'channel' => data_get($payload, 'data.channel'),
                    'payload' => $payload,
                ]);

                return redirect()->route('payments.receipt', $payment)->with('status', 'Payment confirmed successfully.');
            }

            $payment->update([
                'status' => PaymentStatus::Failed,
                'payload' => $payload,
            ]);

            return redirect()->route('dashboard')->withErrors(['payment' => 'Payment could not be confirmed.']);
        } catch (Throwable $exception) {
            return redirect()->route('dashboard')->withErrors(['payment' => $exception->getMessage()]);
        }
    }

    public function receipt(Request $request, Payment $payment): View
    {
        $payment->load('student.user', 'student.schoolClass', 'feeInvoice.feeItem', 'recorder');
        $this->authorizePaymentAccess($request->user(), $payment);

        return view('admin.receipt', compact('payment'));
    }

    protected function authorizeInvoiceAccess($user, FeeInvoice $invoice): void
    {
        if ($user->hasAnyRole(UserRole::Admin, UserRole::Principal, UserRole::Accountant)) {
            return;
        }

        if ($user->hasAnyRole(UserRole::Student) && $invoice->student->user_id === $user->id) {
            return;
        }

        if ($user->hasAnyRole(UserRole::Parent) && $invoice->student->parent_user_id === $user->id) {
            return;
        }

        abort(403);
    }

    protected function authorizePaymentAccess($user, Payment $payment): void
    {
        if ($user->hasAnyRole(UserRole::Admin, UserRole::Principal, UserRole::Accountant)) {
            return;
        }

        if ($user->hasAnyRole(UserRole::Student) && $payment->student->user_id === $user->id) {
            return;
        }

        if ($user->hasAnyRole(UserRole::Parent) && $payment->student->parent_user_id === $user->id) {
            return;
        }

        abort(403);
    }

    protected function markPaymentSuccessful(Payment $payment, array $attributes = []): Payment
    {
        $payment->update([
            'gateway_reference' => $attributes['gateway_reference'] ?? $payment->gateway_reference,
            'status' => PaymentStatus::Paid,
            'channel' => $attributes['channel'] ?? $payment->channel,
            'paid_at' => $attributes['paid_at'] ?? now(),
            'receipt_no' => $payment->receipt_no ?: 'RCP-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'payload' => array_merge($payment->payload ?? [], $attributes['payload'] ?? []),
        ]);

        $payment->refresh();

        if ($payment->feeInvoice) {
            $payment->feeInvoice->syncBalance();
        } else {
            $payment->allocateBundleInvoices();
        }

        return $payment->refresh();
    }
}
