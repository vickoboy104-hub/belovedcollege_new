<x-portal-layout>
    <x-slot name="header">
        <x-page-header title="Student invoices" description="Print, save as PDF, and move directly into payment recording." eyebrow="Finance desk" />
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h2 class="text-lg font-black text-[#071833]">Invoice register</h2><p class="mt-1 text-xs font-semibold text-slate-500">{{ $invoices->count() }} invoice{{ $invoices->count() === 1 ? '' : 's' }} in the current register.</p></div>
            <a href="{{ route('admin.finance', ['section' => 'generate-invoice']) }}" class="rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-black text-white">Generate Invoice</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Student</th><th class="px-4 py-3">Invoice</th><th class="px-4 py-3 text-right">Due</th><th class="px-4 py-3 text-right">Paid</th><th class="px-4 py-3 text-right">Balance</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td class="px-4 py-4"><div class="font-bold text-slate-900">{{ $invoice->student->user->fullName() }}</div><div class="text-[10px] font-semibold text-slate-500">{{ $invoice->student->admission_no }} • {{ $invoice->student->schoolClass->display_name ?? 'No class' }}</div></td>
                                <td class="px-4 py-4"><div class="font-mono text-xs font-bold text-slate-800">{{ $invoice->invoice_no }}</div><div class="text-[10px] font-semibold text-slate-500">{{ $invoice->feeItem->name ?? 'Direct invoice' }}</div></td>
                                <td class="px-4 py-4 text-right font-bold">NGN {{ number_format((float) $invoice->amount_due, 2) }}</td>
                                <td class="px-4 py-4 text-right font-bold text-emerald-700">NGN {{ number_format((float) $invoice->amount_paid, 2) }}</td>
                                <td class="px-4 py-4 text-right font-black text-rose-700">NGN {{ number_format((float) $invoice->balance, 2) }}</td>
                                <td class="px-4 py-4"><x-status-badge :status="$invoice->status" /></td>
                                <td class="px-4 py-4"><div class="flex justify-end gap-2"><a href="{{ route('admin.invoices.print', $invoice) }}" target="_blank" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-[10px] font-black text-blue-700">Print / PDF</a>@if((float) $invoice->balance > 0)<a href="{{ route('admin.finance.payment-desk') }}" class="rounded-lg bg-slate-900 px-3 py-2 text-[10px] font-black text-white">Record Payment</a>@endif</div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-12 text-center text-sm font-semibold text-slate-500">No invoices have been generated.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-portal-layout>
