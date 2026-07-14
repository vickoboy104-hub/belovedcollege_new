<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Payment Gateways"
            eyebrow="Finance Configuration"
            description="Choose which online payment methods students and parents can use, then enter the merchant credentials supplied by each provider."
        >
            <x-slot name="actions">
                <x-action-button variant="secondary" :href="route('admin.finance')" icon="finance">Open Finance</x-action-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <form method="POST" action="{{ route('admin.payment-gateways.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-dashboard-card title="Available checkout methods" subtitle="Only gateways that are enabled and fully configured appear in the student payment portal." icon="finance" accent="blue">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($gateways as $gateway)
                    <label class="relative flex cursor-pointer flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-400">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-extrabold text-slate-900">{{ $gateway['label'] }}</div>
                                <div class="mt-1 text-xs font-semibold {{ $gateway['configured'] ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $gateway['configured'] ? 'Credentials configured' : 'Setup incomplete' }}
                                </div>
                            </div>
                            <input type="checkbox" name="enabled_payment_gateways[]" value="{{ $gateway['value'] }}" class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500" @checked(in_array($gateway['value'], old('enabled_payment_gateways', $enabledValues), true))>
                        </div>
                        <div class="text-[11px] leading-5 text-slate-500">Enabling controls visibility. Checkout remains unavailable until the required credentials below are saved.</div>
                    </label>
                @endforeach
            </div>
        </x-dashboard-card>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="form-section">
                <div class="form-section-header"><h3 class="section-title">Paystack</h3><p class="section-description">Hosted checkout with server-side transaction verification.</p></div>
                <div class="form-stack space-y-4">
                    <div><label class="text-xs font-bold text-slate-700" for="paystack-public-key">Public key</label><input id="paystack-public-key" name="paystack_public_key" value="{{ old('paystack_public_key', $settings['paystack_public_key'] ?? '') }}" class="theme-input mt-1 w-full" autocomplete="off" placeholder="pk_test_... or pk_live_..."></div>
                    <div><label class="text-xs font-bold text-slate-700" for="paystack-secret-key">Secret key</label><x-password-input id="paystack-secret-key" name="paystack_secret_key" autocomplete="new-password" wrapper-class="mt-1" input-class="theme-input w-full" placeholder="Leave blank to keep the existing encrypted key" /></div>
                    <div><label class="text-xs font-bold text-slate-700" for="paystack-webhook-secret">Webhook signing secret</label><x-password-input id="paystack-webhook-secret" name="paystack_webhook_secret" autocomplete="new-password" wrapper-class="mt-1" input-class="theme-input w-full" placeholder="Optional: defaults to the Paystack secret key" /></div>
                    @php($paystack = $gateways->firstWhere('value', 'paystack'))
                    <x-gateway-endpoints :callback="$paystack['callback_url']" :webhook="$paystack['webhook_url']" />
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-header"><h3 class="section-title">PalmPay</h3><p class="section-description">Merchant onboarding details supplied directly by PalmPay.</p></div>
                <div class="form-stack">
                    <div class="mb-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-xs font-semibold leading-5 text-amber-900">PalmPay uses merchant-specific onboarding contracts. Keep this gateway disabled until PalmPay gives the school its official checkout URL, signing rules, and server verification documentation.</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="text-xs font-bold text-slate-700" for="palmpay-merchant-id">Merchant ID</label><input id="palmpay-merchant-id" name="palmpay_merchant_id" value="{{ old('palmpay_merchant_id', $settings['palmpay_merchant_id'] ?? '') }}" class="theme-input mt-1 w-full" autocomplete="off"></div>
                        <div><label class="text-xs font-bold text-slate-700" for="palmpay-app-id">App ID</label><input id="palmpay-app-id" name="palmpay_app_id" value="{{ old('palmpay_app_id', $settings['palmpay_app_id'] ?? '') }}" class="theme-input mt-1 w-full" autocomplete="off"></div>
                    </div>
                    <div class="mt-4 space-y-4">
                        <div><label class="text-xs font-bold text-slate-700" for="palmpay-checkout-url">Official checkout URL</label><input id="palmpay-checkout-url" name="palmpay_checkout_url" value="{{ old('palmpay_checkout_url', $settings['palmpay_checkout_url'] ?? '') }}" class="theme-input mt-1 w-full" type="url" autocomplete="off"></div>
                        <div><label class="text-xs font-bold text-slate-700" for="palmpay-public-key">Public key</label><textarea id="palmpay-public-key" name="palmpay_public_key" class="theme-input mt-1 min-h-24 w-full" autocomplete="off">{{ old('palmpay_public_key', $settings['palmpay_public_key'] ?? '') }}</textarea></div>
                        <div><label class="text-xs font-bold text-slate-700" for="palmpay-private-key">Private key</label><textarea id="palmpay-private-key" name="palmpay_private_key" class="theme-input mt-1 min-h-24 w-full" autocomplete="new-password" placeholder="Leave blank to keep the existing encrypted key"></textarea></div>
                        <div><label class="text-xs font-bold text-slate-700" for="palmpay-webhook-secret">Webhook secret</label><x-password-input id="palmpay-webhook-secret" name="palmpay_webhook_secret" autocomplete="new-password" wrapper-class="mt-1" input-class="theme-input w-full" placeholder="Leave blank to keep the existing encrypted secret" /></div>
                        @php($palmpay = $gateways->firstWhere('value', 'palmpay'))
                        <x-gateway-endpoints :callback="$palmpay['callback_url']" :webhook="$palmpay['webhook_url']" />
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-header"><h3 class="section-title">Flutterwave</h3><p class="section-description">Hosted Flutterwave Standard checkout with server verification and signed webhooks.</p></div>
                <div class="form-stack">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="text-xs font-bold text-slate-700" for="flutterwave-public-key">Public key</label><input id="flutterwave-public-key" name="flutterwave_public_key" value="{{ old('flutterwave_public_key', $settings['flutterwave_public_key'] ?? '') }}" class="theme-input mt-1 w-full" autocomplete="off"></div>
                        <div><label class="text-xs font-bold text-slate-700" for="flutterwave-secret-key">Secret key</label><x-password-input id="flutterwave-secret-key" name="flutterwave_secret_key" autocomplete="new-password" wrapper-class="mt-1" input-class="theme-input w-full" placeholder="Leave blank to keep the existing encrypted key" /></div>
                        <div><label class="text-xs font-bold text-slate-700" for="flutterwave-encryption-key">Encryption key</label><x-password-input id="flutterwave-encryption-key" name="flutterwave_encryption_key" autocomplete="new-password" wrapper-class="mt-1" input-class="theme-input w-full" placeholder="Leave blank to keep existing" /></div>
                        <div><label class="text-xs font-bold text-slate-700" for="flutterwave-secret-hash">Webhook secret hash</label><x-password-input id="flutterwave-secret-hash" name="flutterwave_secret_hash" autocomplete="new-password" wrapper-class="mt-1" input-class="theme-input w-full" placeholder="The secret hash configured in Flutterwave" /></div>
                        <div><label class="text-xs font-bold text-slate-700" for="flutterwave-client-id">OAuth client ID</label><input id="flutterwave-client-id" name="flutterwave_client_id" value="{{ old('flutterwave_client_id', $settings['flutterwave_client_id'] ?? '') }}" class="theme-input mt-1 w-full" autocomplete="off"></div>
                        <div><label class="text-xs font-bold text-slate-700" for="flutterwave-client-secret">OAuth client secret</label><x-password-input id="flutterwave-client-secret" name="flutterwave_client_secret" autocomplete="new-password" wrapper-class="mt-1" input-class="theme-input w-full" placeholder="Leave blank to keep existing" /></div>
                    </div>
                    <div class="mt-4"><label class="text-xs font-bold text-slate-700" for="flutterwave-options">Checkout options</label><input id="flutterwave-options" name="flutterwave_payment_options" value="{{ old('flutterwave_payment_options', $settings['flutterwave_payment_options'] ?? 'card,banktransfer,ussd,opay') }}" class="theme-input mt-1 w-full"></div>
                    @php($flutterwave = $gateways->firstWhere('value', 'flutterwave'))
                    <div class="mt-4"><x-gateway-endpoints :callback="$flutterwave['callback_url']" :webhook="$flutterwave['webhook_url']" /></div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-header"><h3 class="section-title">Monnify</h3><p class="section-description">Hosted checkout for cards, account transfers, and USSD with server-side verification.</p></div>
                <div class="form-stack">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="text-xs font-bold text-slate-700" for="monnify-api-key">API key</label><input id="monnify-api-key" name="monnify_api_key" value="{{ old('monnify_api_key', $settings['monnify_api_key'] ?? '') }}" class="theme-input mt-1 w-full" autocomplete="off"></div>
                        <div><label class="text-xs font-bold text-slate-700" for="monnify-secret-key">Secret key</label><x-password-input id="monnify-secret-key" name="monnify_secret_key" autocomplete="new-password" wrapper-class="mt-1" input-class="theme-input w-full" placeholder="Leave blank to keep the existing encrypted key" /></div>
                        <div><label class="text-xs font-bold text-slate-700" for="monnify-contract-code">Contract code</label><input id="monnify-contract-code" name="monnify_contract_code" value="{{ old('monnify_contract_code', $settings['monnify_contract_code'] ?? '') }}" class="theme-input mt-1 w-full" autocomplete="off"></div>
                        <div><label class="text-xs font-bold text-slate-700" for="monnify-environment">Environment</label><select id="monnify-environment" name="monnify_environment" class="theme-input mt-1 w-full"><option value="sandbox" @selected(old('monnify_environment', $settings['monnify_environment'] ?? 'sandbox') === 'sandbox')>Sandbox / Testing</option><option value="live" @selected(old('monnify_environment', $settings['monnify_environment'] ?? 'sandbox') === 'live')>Live / Production</option></select></div>
                    </div>
                    <div class="mt-4"><label class="text-xs font-bold text-slate-700" for="monnify-methods">Payment methods</label><input id="monnify-methods" name="monnify_payment_methods" value="{{ old('monnify_payment_methods', $settings['monnify_payment_methods'] ?? 'CARD,ACCOUNT_TRANSFER,USSD') }}" class="theme-input mt-1 w-full"></div>
                    @php($monnify = $gateways->firstWhere('value', 'monnify'))
                    <div class="mt-4"><x-gateway-endpoints :callback="$monnify['callback_url']" :webhook="$monnify['webhook_url']" /></div>
                </div>
            </section>
        </div>

        <div class="sticky bottom-4 z-20 flex justify-end rounded-xl border border-slate-200 bg-white/95 p-4 shadow-xl backdrop-blur">
            <x-action-button type="submit" variant="primary" icon="save">Save Payment Configuration</x-action-button>
        </div>
    </form>
</x-app-layout>
