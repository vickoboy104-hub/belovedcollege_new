<x-portal-layout>
    <x-slot name="header">
        <x-page-header title="Student invoices" description="Print, save as PDF, and move directly into payment recording." eyebrow="Finance desk" />
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h2 class="text-lg font-black text-[#071833]">Invoice register</h2><p class="mt-1 text-xs font-semibold text-slate-500">{{ $invoices->total() }} invoice{{ $invoices->total() === 1 ? '' : 's' }} in the current register.</p></div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.finance.payment-desk') }}" class="theme-button-secondary">Payment desk</a>
                <a href="{{ route('admin.finance', ['section' => 'generate-invoice']) }}" class="rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-black text-white">Generate Invoice</a>
            </div>
        </div>

        <x-data-table :headers="['Student', 'Invoice', 'Due', 'Paid', 'Balance', 'Status', 'Actions']" minWidth="1050px" label="Invoice register">
            @forelse ($invoices as $invoice)
                <tr>
                    <td><div class="font-bold text-slate-900">{{ $invoice->student->user->fullName() }}</div><div class="text-xs font-semibold text-slate-500">{{ $invoice->student->admission_no }} • {{ $invoice->student->schoolClass->display_name ?? 'No class' }}</div></td>
                    <td><div class="font-mono text-xs font-bold text-slate-800">{{ $invoice->invoice_no }}</div><div class="text-xs font-semibold text-slate-500">{{ $invoice->feeItem->name ?? 'Direct invoice' }}</div></td>
                    <td class="text-right font-bold">NGN {{ number_format((float) $invoice->amount_due, 2) }}</td>
                    <td class="text-right font-bold text-emerald-700">NGN {{ number_format((float) $invoice->amount_paid, 2) }}</td>
                    <td class="text-right font-black text-rose-700">NGN {{ number_format((float) $invoice->balance, 2) }}</td>
                    <td><x-status-badge :status="$invoice->status" /></td>
                    <td><div class="table-action-group"><a href="{{ route('admin.invoices.print', $invoice) }}" target="_blank" class="table-view-btn">Print / PDF</a></div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center font-semibold text-slate-500">No invoices have been generated.</td></tr>
            @endforelse

            @if ($invoices->hasPages())
                <x-slot name="paginationSlot">
                    <div class="admin-table-footer">
                        <div class="admin-table-count">
                            Showing {{ $invoices->firstItem() }}-{{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
                        </div>
                        <div class="admin-pagination">
                            @foreach ($invoices->linkCollection() as $link)
                                @if ($link['url'] === null)
                                    <span class="admin-page-btn {{ $link['label'] === '&hellip;' ? '' : 'is-disabled' }}">{!! $link['label'] !!}</span>
                                @else
                                    <a class="admin-page-btn {{ $link['active'] ? 'is-active' : '' }}" href="{{ $link['url'] }}">{!! $link['label'] !!}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </x-slot>
            @endif
        </x-data-table>
    </div>
</x-portal-layout>
