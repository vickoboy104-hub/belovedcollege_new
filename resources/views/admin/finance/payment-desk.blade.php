<x-portal-layout>
    <x-slot name="header">
        <x-page-header title="Payments and collections" description="Record offline collections, monitor transactions, and keep invoice balances synchronized." eyebrow="Finance desk" />
    </x-slot>

    <div class="space-y-7">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-800">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Payments Today" value="NGN {{ number_format((float) $metrics['paymentsToday'], 2) }}" icon="cash" accent="green" />
            <x-stat-card label="Successful Transactions" :value="$metrics['successfulTransactions']" icon="check-circle" accent="green" />
            <x-stat-card label="Pending Payments" :value="$metrics['pendingPayments']" icon="clock" accent="orange" />
            <x-stat-card label="Outstanding Balance" value="NGN {{ number_format((float) $metrics['outstandingBalance'], 2) }}" icon="wallet" accent="red" />
        </div>

        <div class="grid gap-7 xl:grid-cols-[1fr,0.8fr]">
            <x-form-card title="Record offline payment" description="Cash, bank transfer, and POS payments use the same ledger as online transactions." action="{{ route('admin.manual-payments.store') }}" method="POST">
                <div class="space-y-5">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-700">Student Invoice</label>
                        <select name="fee_invoice_id" required class="theme-input w-full rounded-xl border-[#c8d6ea]">
                            <option value="">Select invoice</option>
                            @foreach ($invoices as $invoice)
                                <option value="{{ $invoice->id }}">{{ $invoice->student->user->fullName() }} • {{ $invoice->invoice_no }} • Balance NGN {{ number_format((float) $invoice->balance, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-700">Amount</label><input name="amount" type="number" min="1" step="0.01" required class="theme-input w-full rounded-xl border-[#c8d6ea]" placeholder="0.00"></div>
                        <div><label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-700">Payment Method</label><select name="payment_method" required class="theme-input w-full rounded-xl border-[#c8d6ea]"><option value="cash">Cash</option><option value="bank-transfer">Bank Transfer</option><option value="pos">POS</option></select></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-700">Reference</label><input name="reference" class="theme-input w-full rounded-xl border-[#c8d6ea]" placeholder="Teller, POS or bank reference"></div>
                        <div><label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-700">Payment Date</label><input name="paid_at" type="datetime-local" class="theme-input w-full rounded-xl border-[#c8d6ea]"></div>
                    </div>
                    <div><label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-700">Received By</label><div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">{{ auth()->user()->fullName() }}</div></div>
                    <div><label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-700">Notes</label><textarea name="note" rows="3" class="theme-input w-full rounded-xl border-[#c8d6ea]" placeholder="Optional bursary note"></textarea></div>
                </div>
                <x-slot name="actions"><x-action-button type="submit" variant="primary">Record Payment</x-action-button></x-slot>
            </x-form-card>

            <div class="space-y-5">
                <x-dashboard-card title="Verification queue" subtitle="Student-submitted bank transfers awaiting bursary confirmation." icon="bank" accent="orange">
                    <a href="{{ route('admin.bank-transfers.index') }}" class="inline-flex w-full items-center justify-between rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-black text-amber-900"><span>Review bank transfers</span><span>→</span></a>
                </x-dashboard-card>
                <x-dashboard-card title="Quick finance actions" icon="finance" accent="blue">
                    <div class="grid gap-2">
                        <a href="{{ route('admin.finance', ['section' => 'generate-invoice']) }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Generate student invoice</a>
                        <a href="{{ route('admin.finance', ['section' => 'recent-invoices']) }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Open recent invoices</a>
                        <a href="{{ route('admin.finance.records', ['section' => 'recent-payments']) }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Full payment records</a>
                    </div>
                </x-dashboard-card>
            </div>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4"><h3 class="text-sm font-black text-[#071833]">Recent payments</h3><p class="mt-1 text-xs font-semibold text-slate-500">Latest online and offline ledger activity.</p></div>
            <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Student</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Method</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Date</th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse ($payments as $payment)
                    <tr><td class="px-4 py-4"><div class="font-bold text-slate-900">{{ $payment->student->user->fullName() }}</div><div class="text-[10px] font-semibold text-slate-500">{{ $payment->student->admission_no }}</div></td><td class="px-4 py-4 font-black">NGN {{ number_format((float) $payment->amount, 2) }}</td><td class="px-4 py-4 font-semibold">{{ $payment->channel ? str($payment->channel)->replace('-', ' ')->title() : $payment->provider->label() }}</td><td class="px-4 py-4"><x-status-badge :status="$payment->status->value" /></td><td class="px-4 py-4 text-xs font-semibold text-slate-500">{{ ($payment->paid_at ?? $payment->created_at)?->format('d M Y, h:i A') }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm font-semibold text-slate-500">No payments recorded yet.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>
    </div>
</x-portal-layout>
