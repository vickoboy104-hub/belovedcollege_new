<?php

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(protected PaymentGatewayManager $gateways)
    {
    }

    public function checkout(Request $request, FeeInvoice $invoice, string $provider): RedirectResponse
    {
        $this->authorizeInvoiceAccess($request->user(), $invoice->load('student.user'));

        $providerEnum = PaymentProvider::tryFrom($provider);
        abort_unless($providerEnum?->isOnline(), 404);

        if (! $this->gateways->isAvailable($providerEnum)) {
            return back()->withErrors([
                'payment' => $providerEnum->label().' is disabled or not completely configured by the school.',
            ]);
        }

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

        return $this->initializePayment($invoice, $payment, $providerEnum);
    }

    public function checkoutSelection(Request $request, string $provider): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['integer', 'exists:fee_invoices,id'],
        ]);

        $providerEnum = PaymentProvider::tryFrom($provider);
        abort_unless($providerEnum?->isOnline(), 404);

        if (! $this->gateways->isAvailable($providerEnum)) {
            return back()->withErrors([
                'payment' => $providerEnum->label().' is disabled or not completely configured by the school.',
            ]);
        }

        $invoices = FeeInvoice::query()
            ->with('student.user', 'feeItem')
            ->whereIn('id', $validated['invoice_ids'])
            ->get()
            ->filter(fn (FeeInvoice $invoice) => (float) $invoice->balance > 0)
            ->values();

        abort_if($invoices->isEmpty(), 422, 'Select at least one unpaid fee item.');

        $studentIds = $invoices->pluck('student_id')->unique();
        abort_if($studentIds->count() !== 1, 422, 'Selected fee items must belong to the same student.');

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

        return $this->initializePayment($bundleSubject, $payment, $providerEnum);
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $providerEnum = PaymentProvider::tryFrom($provider);
        abort_unless($providerEnum?->isOnline(), 404);

        $reference = collect([
            $request->string('reference')->toString(),
            $request->string('trxref')->toString(),
            $request->string('tx_ref')->toString(),
            $request->string('paymentReference')->toString(),
            $request->string('payment_reference')->toString(),
        ])->first(fn (string $value) => $value !== '');
        abort_if(blank($reference), 422, 'A payment reference is required.');

        $payment = Payment::with('feeInvoice')->where('reference', $reference)->firstOrFail();
        abort_unless($payment->provider === $providerEnum, 404);

        try {
            $payload = $this->gateways->gateway($providerEnum)->verify($reference, [
                'transaction_id' => $request->input('transaction_id') ?: $request->input('id'),
                'status' => $request->input('status'),
                'transaction_reference' => $request->input('transactionReference'),
            ]);

            if ($this->verifiedPaymentMatches($providerEnum, $payment, $payload)) {
                $payment = $this->markPaymentSuccessful($payment, [
                    'gateway_reference' => data_get($payload, 'data.gateway_reference') ?: data_get($payload, 'data.reference'),
                    'channel' => data_get($payload, 'data.channel'),
                    'paid_at' => data_get($payload, 'data.paid_at') ?: now(),
                    'payload' => ['verification' => $payload],
                ]);

                return redirect()->route('payments.receipt', $payment)->with('status', 'Payment confirmed successfully.');
            }

            $payment->update([
                'status' => PaymentStatus::Failed,
                'payload' => array_merge($payment->payload ?? [], [
                    'verification_message' => 'Gateway verification did not match the expected payment.',
                ]),
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

    protected function initializePayment(object $invoice, Payment $payment, PaymentProvider $provider): RedirectResponse
    {
        try {
            $response = $this->gateways->gateway($provider)->initialize($invoice, $payment);
            $authorizationUrl = (string) data_get($response, 'data.authorization_url');
            abort_if($authorizationUrl === '', 502, 'The payment provider did not return a checkout URL.');

            $payment->update([
                'status' => PaymentStatus::Pending,
                'gateway_reference' => data_get($response, 'data.gateway_reference'),
                'payload' => array_merge($payment->payload ?? [], ['gateway_initialization' => $response]),
            ]);

            return redirect()->away($authorizationUrl);
        } catch (Throwable $exception) {
            report($exception);

            $payment->update([
                'status' => PaymentStatus::Failed,
                'payload' => array_merge($payment->payload ?? [], ['message' => 'Payment initialization failed.']),
            ]);

            return back()->withErrors([
                'payment' => $provider->label().' could not start the payment. Check the gateway configuration or try another enabled method.',
            ]);
        }
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
        $gatewayAmount = data_get($payload, 'data.amount');

        if ($provider === PaymentProvider::Paystack) {
            return $status === 'success'
                && hash_equals($payment->reference, $reference)
                && (int) $gatewayAmount === (int) round(((float) $payment->amount) * 100)
                && $currency === strtoupper((string) $payment->currency);
        }

        if ($provider === PaymentProvider::Flutterwave) {
            return in_array($status, ['successful', 'success'], true)
                && hash_equals($payment->reference, $reference)
                && abs((float) $gatewayAmount - (float) $payment->amount) < 0.01
                && $currency === strtoupper((string) $payment->currency);
        }

        if ($provider === PaymentProvider::Monnify) {
            return in_array($status, ['paid', 'overpaid'], true)
                && hash_equals($payment->reference, $reference)
                && (float) $gatewayAmount + 0.00001 >= (float) $payment->amount
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
