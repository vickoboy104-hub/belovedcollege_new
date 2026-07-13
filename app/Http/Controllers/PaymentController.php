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
use Illuminate\Support\Str;
use Illuminate\View\View;
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

        $providerEnum = PaymentProvider::tryFrom($provider);
        abort_unless($providerEnum, 404);

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

            $authorizationUrl = (string) data_get($response, 'data.authorization_url');
            abort_if($authorizationUrl === '', 502, 'The payment provider did not return a checkout URL.');

            return redirect()->away($authorizationUrl);
        } catch (Throwable $exception) {
            report($exception);

            $payment->update([
                'status' => PaymentStatus::Failed,
                'payload' => ['message' => 'Payment initialization failed.'],
            ]);

            return back()->withErrors(['payment' => 'The payment provider could not be reached. Please try again later.']);
        }
    }

    public function checkoutSelection(Request $request, string $provider): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['integer', 'exists:fee_invoices,id'],
        ]);

        $providerEnum = PaymentProvider::tryFrom($provider);
        abort_unless($providerEnum, 404);

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

            $authorizationUrl = (string) data_get($response, 'data.authorization_url');
            abort_if($authorizationUrl === '', 502, 'The payment provider did not return a checkout URL.');

            return redirect()->away($authorizationUrl);
        } catch (Throwable $exception) {
            report($exception);

            $payment->update([
                'status' => PaymentStatus::Failed,
                'payload' => [...($payment->payload ?? []), 'message' => 'Payment initialization failed.'],
            ]);

            return back()->withErrors(['payment' => 'The payment provider could not be reached. Please try again later.']);
        }
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $providerEnum = PaymentProvider::tryFrom($provider);
        abort_unless($providerEnum, 404);

        $reference = $request->string('reference')->toString() ?: $request->string('trxref')->toString();
        abort_if($reference === '', 422, 'A payment reference is required.');

        $payment = Payment::with('feeInvoice')->where('reference', $reference)->firstOrFail();
        abort_unless($payment->provider === $providerEnum, 404);

        try {
            $payload = match ($providerEnum) {
                PaymentProvider::Paystack => $this->paystackGateway->verify($reference),
                PaymentProvider::PalmPay => $this->palmPayGateway->verify($reference),
            };

            if ($this->verifiedPaymentMatches($providerEnum, $payment, $payload)) {
                $payment = $this->markPaymentSuccessful($payment, [
                    'gateway_reference' => data_get($payload, 'data.reference') ?: data_get($payload, 'data.gateway_reference'),
                    'channel' => data_get($payload, 'data.channel'),
                    'payload' => $payload,
                ]);

                return redirect()->route('payments.receipt', $payment)->with('status', 'Payment confirmed successfully.');
            }

            $payment->update([
                'status' => PaymentStatus::Failed,
                'payload' => ['message' => 'Gateway verification did not match the expected payment.'],
            ]);

            return redirect()->route('dashboard')->withErrors(['payment' => 'Payment could not be verified.']);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('dashboard')->withErrors([
                'payment' => 'Payment confirmation is still pending. No invoice has been marked as paid.',
            ]);
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

    protected function verifiedPaymentMatches(PaymentProvider $provider, Payment $payment, array $payload): bool
    {
        $status = strtolower((string) data_get($payload, 'data.status'));
        $reference = (string) data_get($payload, 'data.reference');
        $currency = strtoupper((string) data_get($payload, 'data.currency'));

        if ($provider === PaymentProvider::Paystack) {
            $gatewayAmount = (int) data_get($payload, 'data.amount', -1);
            $expectedAmount = (int) round(((float) $payment->amount) * 100);

            return $status === 'success'
                && hash_equals($payment->reference, $reference)
                && $gatewayAmount === $expectedAmount
                && $currency === strtoupper((string) $payment->currency);
        }

        return false;
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
