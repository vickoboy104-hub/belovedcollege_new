<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentGatewaySettingsController extends Controller
{
    public function index(PaymentGatewayManager $gateways): View
    {
        return view('admin.payment-gateways', [
            'settings' => Setting::forAdminForm(),
            'gateways' => $gateways->catalog(),
            'enabledValues' => $gateways->enabledValues(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled_payment_gateways' => ['nullable', 'array'],
            'enabled_payment_gateways.*' => [Rule::in(['paystack', 'palmpay', 'flutterwave', 'monnify'])],

            'paystack_public_key' => ['nullable', 'string', 'max:255'],
            'paystack_secret_key' => ['nullable', 'string', 'max:255'],
            'paystack_webhook_secret' => ['nullable', 'string', 'max:255'],

            'palmpay_merchant_id' => ['nullable', 'string', 'max:255'],
            'palmpay_app_id' => ['nullable', 'string', 'max:255'],
            'palmpay_public_key' => ['nullable', 'string', 'max:5000'],
            'palmpay_private_key' => ['nullable', 'string', 'max:10000'],
            'palmpay_webhook_secret' => ['nullable', 'string', 'max:255'],
            'palmpay_checkout_url' => ['nullable', 'url', 'max:1000'],

            'flutterwave_public_key' => ['nullable', 'string', 'max:255'],
            'flutterwave_secret_key' => ['nullable', 'string', 'max:255'],
            'flutterwave_encryption_key' => ['nullable', 'string', 'max:255'],
            'flutterwave_secret_hash' => ['nullable', 'string', 'max:255'],
            'flutterwave_client_id' => ['nullable', 'string', 'max:255'],
            'flutterwave_client_secret' => ['nullable', 'string', 'max:255'],
            'flutterwave_payment_options' => ['nullable', 'string', 'max:500'],

            'monnify_api_key' => ['nullable', 'string', 'max:255'],
            'monnify_secret_key' => ['nullable', 'string', 'max:255'],
            'monnify_contract_code' => ['nullable', 'string', 'max:255'],
            'monnify_environment' => ['nullable', Rule::in(['sandbox', 'live'])],
            'monnify_payment_methods' => ['nullable', 'string', 'max:500'],
        ]);

        $enabled = collect($validated['enabled_payment_gateways'] ?? [])
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->unique()
            ->values()
            ->implode(',');

        unset($validated['enabled_payment_gateways']);
        Setting::setMany([
            'enabled_payment_gateways' => $enabled,
            ...$validated,
        ], 'payments');

        return back()->with('status', 'Payment gateway configuration updated securely.');
    }
}
