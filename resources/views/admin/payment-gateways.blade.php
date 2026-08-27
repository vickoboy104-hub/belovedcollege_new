<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Payment Gateways"
            eyebrow="Finance Configuration"
            description="Choose exactly which online payment methods students and parents can use, then enter the credentials supplied by each provider."
        >
            <x-slot name="actions">
                <x-action-button variant="secondary" :href="route('admin.finance')" icon="finance">Open Finance</x-action-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <form method="POST" action="{{ route('admin.payment-gateways.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-950 shadow-sm">
            <div class="font-extrabold">Recommended for your first payment test: Paystack Test Mode</div>
            <p class="mt-1 leading-6 text-blue-900">Create a Paystack account, copy the <strong>pk_test_...</strong> and <strong>sk_test_...</strong> keys here, enable Paystack, and test checkout without using real money. Switch to live keys only when you are ready for real school payments.</p>
        </div>

        <x-dashboard-card title="Student checkout methods" subtitle="Tick the gateways you want students and parents to see. A gateway appears only when it is both enabled here and fully configured below." icon="finance" accent="blue">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                @foreach ($gateways as $gateway)
                    <label class="relative flex cursor-pointer flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-400 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-950 text-xs font-black text-white">{{ $gateway['initials'] ?? strtoupper(substr($gateway['label'], 0, 2)) }}</span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <div class="font-extrabold text-slate-900">{{ $gateway['label'] }}</div>
                                        @if (! empty($gateway['recommended']))
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[9px] font-black uppercase tracking-wide text-emerald-800">Recommended</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $gateway['mode'] ?? 'Configured' }}</div>
                                </div>
                            </div>
                            <input type="checkbox" name="enabled_payment_gateways[]" value="{{ $gateway['value'] }}" class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500" @checked(in_array($gateway['value'], old('enabled_payment_gateways', $enabledValues), true))>
                        </div>
                        <p class="text-[11px] leading-5 text-slate-600">{{ $gateway['description'] ?? 'Online payment gateway.' }}</p>
                        <div class="mt-auto text-xs font-semibold {{ $gateway['configured'] ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $gateway['configured'] ? 'Ready for checkout' : 'Setup incomplete' }}
                        </div>
                    </label>
                @endforeach
            </div>
        </x-dashboard-card>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="form-section">
                <div class="form-section-header"><h3 class="section-title">Paystack</h3><p class="section-description">Recommended first test gateway. Hosted checkout with server-side transaction verification.</p></div>
                <div class="form-stack space-y-4">
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold leading-5 text-emerald-900">Use <strong>pk_test_...</strong> and <strong>sk_test_...</strong> while testing. Paystack decides test/live mode from the keys you enter.</div>
                    <div><label class="text-xs font-bold text-slate-700" for="paystack-public-key">Public key</label><input id="paystack-public-key" name="paystack_public_key" value="{{ old('paystack_public_key', $settings['paystack_public_key'] ?? '') }}" class="theme-input mt-1 w-full" autocomplete="off" placeholder="pk_test_... or pk_live_..."></div>
                    <div><label class="text-xs font-bold text-slate-700" for="paystack-secret-key">Secret key</label><x-password-input id="paystack-secret-key" name="paystack_secret_key" autocomplete="new-password" wrapper-class="mt-1" input-class="theme-input w-full" placeholder="Leave blank to keep the existing encrypted key" /></div>
                    <div><label class="text-xs font-bold text-slate-700" for="paystack-webhook-secret">Webhook signing secret</label><x-password-input id="paystack-webhook-secret" name="paystack_webhook_secret" autocomplete="new-password" wrapper-class="mt-1" input-class="theme-input w-full" placeholder="Optional: defaults to the Paystack secret key" /></div>
                    @php($paystack = $gateways->firstWhere('value', 'paystack'))
                    <x-gateway-endpoints :callback="$paystack['callback_url']" :webhook="$paystack['webhook_url']" />
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-header"><h3 class="section-title">Flutterwave</h3><p class="section-description">Hosted Flutterwave checkout with test credentials, server verification and signed webhooks.</p></div>
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
                <div class="form-section-header"><h3 class="section-title">Monnify</h3><p class="section-description">Hosted checkout for cards, account transfers and USSD with a dedicated sandbox environment.</p></div>
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

            <section class="form-section">
                <div class="form-section-header"><h3 class="section-title">OPay</h3><p class="section-description">OPay Cashier hosted checkout with sandbox and live environments.</p></div>
                <div class="form-stack">
                    <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs font-semibold leading-5 text-blue-900">Use OPay sandbox credentials first. Only switch the environment to Live after OPay has approved the school merchant account.</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="text-xs font-bold text-slate-700" for="opay-merchant-id">Merchant ID</label><input id="opay-merchant-id" name="opay_merchant_id" value="{{ old('opay_merchant_id', $settings['opay_merchant_id'] ?? '') }}" class="theme-input mt-1 w-full" autocomplete="off"></div>
                        <div><label class="text-xs font-bold text-slate-700" for="opay-environment">Environment</label><select id="opay-environment" name="opay_environment" class="theme-input mt-1 w-full"><option value="sandbox" @selected(old('opay_environment', $settings['opay_environment'] ?? 'sandbox') === 'sandbox')>Sandbox / Testing</option><option value="live" @selected(old('opay_environment', $settings['opay_environment'] ?? 'sandbox') === 'live')>Live / Production</option></select></div>
                    </div>
                    <div class="mt-4 space-y-4">
                        <div><label class="text-xs font-bold text-slate-700" for="opay-public-key">Public key</label><textarea id="opay-public-key" name="opay_public_key" class="theme-input mt-1 min-h-24 w-full" autocomplete="off" placeholder="Public key supplied by OPay">{{ old('opay_public_key', $settings['opay_public_key'] ?? '') }}</textarea></div>
                        <div><label class="text-xs font-bold text-slate-700" for="opay-secret-key">Secret key</label><x-password-input id="opay-secret-key" name="opay_secret_key" autocomplete="new-password" wrapper-class="mt-1" input-class="theme-input w-full" placeholder="Leave blank to keep the existing encrypted secret" /></div>
                        <div><label class="text-xs font-bold text-slate-700" for="opay-pay-method">Preferred pay method <span class="font-medium text-slate-500">(optional)</span></label><input id="opay-pay-method" name="opay_pay_method" value="{{ old('opay_pay_method', $settings['opay_pay_method'] ?? '') }}" class="theme-input mt-1 w-full" autocomplete="off" placeholder="Leave blank to let OPay show available methods"></div>
                        @php($opay = $gateways->firstWhere('value', 'opay'))
                        <x-gateway-endpoints :callback="$opay['callback_url']" :webhook="$opay['webhook_url']" />
                    </div>
                </div>
            </section>

            <section class="form-section xl:col-span-2">
                <div class="form-section-header"><h3 class="section-title">PalmPay</h3><p class="section-description">Template retained for schools that receive a direct PalmPay merchant integration contract.</p></div>
                <div class="form-stack">
                    <div class="mb-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-xs font-semibold leading-5 text-amber-900">PalmPay merchant APIs are onboarding-contract specific. The template is ready for credentials, but checkout intentionally remains unavailable until the school's official PalmPay verification contract is implemented. Do not use it for live fees yet.</div>
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
        </div>

        <div class="sticky bottom-4 z-20 flex justify-end rounded-xl border border-slate-200 bg-white/95 p-4 shadow-xl backdrop-blur">
            <x-action-button type="submit" variant="primary" icon="save">Save Payment Configuration</x-action-button>
        </div>
    </form>
</x-app-layout>
