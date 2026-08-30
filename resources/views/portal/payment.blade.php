<x-app-layout>
    @php
        $bankAccounts = collect(range(1, 3))
            ->map(fn (int $index) => [
                'bank' => $schoolSettings["bank_name_{$index}"] ?? null,
                'account_name' => $schoolSettings["account_name_{$index}"] ?? null,
                'account_number' => $schoolSettings["account_number_{$index}"] ?? null,
            ])
            ->filter(fn (array $account) => filled($account['bank']) || filled($account['account_name']) || filled($account['account_number']))
            ->values();
        $outstandingInvoices = $invoices->filter(fn ($invoice) => (float) $invoice->balance > 0)->values();
    @endphp

    <x-slot name="header">
        <x-page-header title="Make Payment" :description="'Hi '.$student->user->first_name.' — choose what you want to pay.'" eyebrow="Student Billing">
            <x-slot name="actions">
                <a href="{{ route('portal.index', $children->isNotEmpty() ? ['student' => $student->id] : []) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-black text-slate-700">Back to portal</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div
        class="mx-auto max-w-5xl space-y-5"
        x-data="{
            selectedInvoices: [],
            totals: @js($outstandingInvoices->mapWithKeys(fn ($invoice) => [$invoice->id => (float) $invoice->balance])),
            step: 1,
            method: '',
            get selectedTotal() {
                return this.selectedInvoices.reduce((sum, id) => sum + Number(this.totals[id] || 0), 0);
            }
        }"
    >
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-800">{{ $errors->first() }}</div>
        @endif

        <section class="overflow-hidden rounded-[22px] border border-[#d8e2ef] bg-white shadow-sm">
            <div class="finance-contrast-banner grid gap-3 bg-[#071833] px-5 py-4 text-white sm:grid-cols-[1fr,auto] sm:items-center sm:px-6">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-amber-300">Financial account</p>
                    <h2 class="finance-banner-title mt-1 text-lg font-black">Choose what you want to pay</h2>
                    <p class="finance-banner-summary mt-1 text-xs font-semibold">One fee or several fees can be settled in one checkout.</p>
                </div>
                <div class="rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 sm:text-right">
                    <p class="finance-banner-label text-[10px] font-black uppercase tracking-wider">Outstanding Balance</p>
                    <p class="finance-banner-value mt-0.5 text-xl font-black">NGN {{ number_format((float) $outstandingInvoices->sum('balance'), 2) }}</p>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <div class="mb-4 flex items-center gap-3" aria-label="Payment progress">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black" :class="step === 1 ? 'bg-blue-600 text-white' : 'bg-emerald-600 text-white'">1</span>
                        <span class="text-xs font-black text-[#071833]">Select Fees</span>
                    </div>
                    <span class="h-px flex-1 bg-slate-200"></span>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black" :class="step === 2 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500'">2</span>
                        <span class="text-xs font-black" :class="step === 2 ? 'text-[#071833]' : 'text-slate-500'">Payment Method</span>
                    </div>
                </div>

                <div x-show="step === 1" x-transition.opacity>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-black text-[#071833]">Select fees</h3>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Tap the items you want to settle now.</p>
                        </div>
                        <button type="button" x-show="Object.keys(totals).length > 0" class="text-xs font-black text-blue-700" @click="selectedInvoices = selectedInvoices.length === Object.keys(totals).length ? [] : Object.keys(totals)">
                            <span x-text="selectedInvoices.length === Object.keys(totals).length ? 'Clear all' : 'Select all'"></span>
                        </button>
                    </div>

                    <div class="hidden overflow-hidden rounded-xl border border-slate-200 md:block">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3"></th><th class="px-4 py-3">Fee</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3 text-right">Status</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($outstandingInvoices as $invoice)
                                    @php $cleared = (float) $invoice->balance <= 0; @endphp
                                    <tr class="{{ $cleared ? 'bg-slate-50 opacity-60' : 'bg-white' }}">
                                        <td class="px-4 py-3"><input type="checkbox" value="{{ $invoice->id }}" x-model="selectedInvoices" @disabled($cleared) class="h-5 w-5 rounded border-slate-300 text-blue-600"></td>
                                        <td class="px-4 py-3"><div class="font-black text-[#071833]">{{ $invoice->feeItem->name ?? 'School fee' }}</div><div class="text-[10px] font-semibold text-slate-500">{{ $invoice->invoice_no }}</div></td>
                                        <td class="px-4 py-3 text-right font-black">NGN {{ number_format((float) $invoice->balance, 2) }}</td>
                                        <td class="px-4 py-3 text-right text-[10px] font-black {{ $cleared ? 'text-emerald-700' : 'text-amber-700' }}">{{ $cleared ? 'PAID' : 'OUTSTANDING' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-10 text-center text-sm font-semibold text-slate-500">No invoices available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-3 md:hidden">
                        @foreach ($outstandingInvoices as $invoice)
                            @php $cleared = (float) $invoice->balance <= 0; @endphp
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 {{ $cleared ? 'bg-slate-50 opacity-60' : 'bg-white' }}">
                                <input type="checkbox" value="{{ $invoice->id }}" x-model="selectedInvoices" @disabled($cleared) class="h-5 w-5 rounded border-slate-300 text-blue-600">
                                <span class="min-w-0 flex-1"><span class="block truncate text-sm font-black text-[#071833]">{{ $invoice->feeItem->name ?? 'School fee' }}</span><span class="text-[10px] font-semibold text-slate-500">{{ $invoice->invoice_no }}</span></span>
                                <span class="text-right"><span class="block text-sm font-black text-[#071833]">NGN {{ number_format((float) $invoice->balance, 2) }}</span><span class="text-[10px] font-black {{ $cleared ? 'text-emerald-700' : 'text-amber-700' }}">{{ $cleared ? 'PAID' : 'DUE' }}</span></span>
                            </label>
                        @endforeach
                    </div>

                    <div class="sticky bottom-3 mt-4 flex flex-col gap-3 rounded-xl border border-[#c8d6ea] bg-white p-3 shadow-lg sm:flex-row sm:items-center sm:justify-between">
                        <div><p class="text-[10px] font-black uppercase tracking-wider text-slate-500"><span x-text="selectedInvoices.length"></span> selected</p><p class="mt-1 text-xl font-black text-[#071833]" x-text="'NGN ' + selectedTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p></div>
                        <button type="button" @click="if (selectedInvoices.length) step = 2" :disabled="selectedInvoices.length === 0" class="min-h-11 rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white disabled:bg-slate-300">Continue</button>
                    </div>
                </div>

                <div x-show="step === 2" x-cloak x-transition.opacity>
                    <div class="mb-4"><h3 class="text-sm font-black text-[#071833]">Choose Payment Method</h3><p class="mt-1 text-xs font-semibold text-slate-500">Choose how you want to pay. The school selects the secure provider in the background.</p></div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($paymentMethods as $paymentMethod)
                            @php
                                $icon = match ($paymentMethod['value']) { 'card' => '💳', 'bank-transfer' => '🏦', 'ussd' => '📱', 'wallet' => '💰', default => '💳' };
                            @endphp
                            <button type="button" @disabled(!$paymentMethod['available']) @click="method = '{{ $paymentMethod['value'] }}'" class="rounded-2xl border-2 p-4 text-left transition disabled:cursor-not-allowed disabled:opacity-45" :class="method === '{{ $paymentMethod['value'] }}' ? 'border-blue-600 bg-blue-50' : 'border-slate-200 bg-white hover:border-slate-300'">
                                <span class="flex items-start gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-lg">{{ $icon }}</span><span><span class="block text-sm font-black text-[#071833]">{{ $paymentMethod['label'] }}</span><span class="mt-1 block text-xs font-semibold leading-relaxed text-slate-600">{{ $paymentMethod['description'] }}</span>@if(!$paymentMethod['available'])<span class="mt-1 block text-[10px] font-black text-rose-600">Currently unavailable</span>@endif</span></span>
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4"><div class="flex items-center justify-between gap-3"><span class="text-xs font-black text-slate-600">Selected amount</span><span class="text-xl font-black text-[#071833]" x-text="'NGN ' + selectedTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></div></div>

                    <div x-show="method === 'bank-transfer'" x-cloak class="mt-5 rounded-2xl border border-amber-200 bg-amber-50/60 p-4">
                        <h4 class="text-sm font-black text-[#071833]">Transfer to the official school account</h4>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach ($bankAccounts as $account)
                                <div class="rounded-xl border border-amber-200 bg-white p-3"><p class="text-[10px] font-black uppercase tracking-wider text-slate-500">{{ $account['bank'] ?: 'School bank account' }}</p><p class="mt-1 text-sm font-bold text-[#071833]">{{ $account['account_name'] ?: 'Account name pending' }}</p><p class="mt-1 font-mono text-lg font-black text-[#071833]">{{ $account['account_number'] ?: 'N/A' }}</p></div>
                            @endforeach
                        </div>
                        <form method="POST" action="{{ route('payments.bank-transfer.submit') }}" class="mt-4 space-y-3">
                            @csrf
                            <template x-for="invoiceId in selectedInvoices" :key="invoiceId"><input type="hidden" name="invoice_ids[]" :value="invoiceId"></template>
                            <div><label class="mb-1 block text-[10px] font-black uppercase tracking-wider text-slate-600">Bank transfer reference</label><input name="bank_reference" required maxlength="255" placeholder="Enter the bank transaction/reference number" class="theme-input w-full rounded-xl border-slate-300"></div>
                            <button type="submit" class="w-full rounded-xl bg-amber-600 px-5 py-3 text-sm font-black text-white hover:bg-amber-700">I have transferred — submit for verification</button>
                            <p class="text-[10px] font-semibold text-amber-900">Your invoice remains unchanged until the bursary confirms the transfer in the school account.</p>
                        </form>
                    </div>

                    <form x-show="['card','ussd','wallet'].includes(method)" x-cloak method="POST" class="mt-5">
                        @csrf
                        <template x-for="invoiceId in selectedInvoices" :key="invoiceId"><input type="hidden" name="invoice_ids[]" :value="invoiceId"></template>
                        <button x-show="method === 'card'" type="submit" formaction="{{ route('payments.method.checkout', 'card') }}" class="w-full rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white hover:bg-blue-700">Continue with Card Payment</button>
                        <button x-show="method === 'ussd'" type="submit" formaction="{{ route('payments.method.checkout', 'ussd') }}" class="w-full rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white hover:bg-blue-700">Continue with USSD</button>
                        <button x-show="method === 'wallet'" type="submit" formaction="{{ route('payments.method.checkout', 'wallet') }}" class="w-full rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white hover:bg-blue-700">Continue with Wallet</button>
                        <p class="mt-2 text-center text-[10px] font-semibold text-slate-500">Card, OTP, bank verification and other sensitive payment details are handled only by the secure hosted payment provider.</p>
                    </form>

                    <div class="mt-5 flex justify-between gap-3"><button type="button" @click="step = 1; method = ''" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-black text-slate-700">Back to fees</button></div>
                </div>
            </div>
        </section>

        <section class="rounded-[20px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4"><h3 class="text-sm font-black text-[#071833]">Recent receipts</h3><p class="mt-1 text-xs font-semibold text-slate-500">Confirmed transactions for this student only.</p></div>
            <div class="grid gap-3 sm:grid-cols-2">
                @forelse ($payments->filter(fn ($payment) => $payment->status->value === 'paid')->take(6) as $payment)
                    <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="rounded-xl border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/30"><div class="flex items-center justify-between gap-3"><span class="text-sm font-black text-[#071833]">NGN {{ number_format((float) $payment->amount, 2) }}</span><span class="text-xs font-black text-blue-700">Receipt →</span></div><p class="mt-1 text-[10px] font-semibold text-slate-500">{{ $payment->channel ? str($payment->channel)->replace('-', ' ')->title() : $payment->provider->label() }} • {{ $payment->paid_at?->format('d M Y') }}</p></a>
                @empty
                    <div class="sm:col-span-2 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-xs font-semibold text-slate-500">No confirmed receipts yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
