@props(['payment'])

@php
    $allocatedInvoices = collect(data_get($payment->payload, 'allocated_invoices', []));
    $receiptLines = $allocatedInvoices->isNotEmpty()
        ? $allocatedInvoices
        : collect([
            [
                'fee_item' => $payment->feeInvoice->feeItem->name ?? 'School fee payment',
                'amount_due' => (float) ($payment->feeInvoice->amount_due ?? 0),
                'amount_paid_now' => (float) $payment->amount,
                'amount_paid_total' => (float) ($payment->feeInvoice->amount_paid ?? $payment->amount),
                'balance' => (float) ($payment->feeInvoice->balance ?? 0),
            ],
        ]);
@endphp

<div id="receipt-{{ $payment->id }}" class="print-card rounded-[24px] border border-[#c8d6ea] bg-white p-8 shadow-md relative overflow-hidden text-left" style="width: 100%; box-sizing: border-box; font-family: system-ui, -apple-system, sans-serif;">
    <!-- Sleek Top Accent Banner -->
    <div class="absolute top-0 left-0 right-0 h-2.5 bg-gradient-to-r from-[#071833] via-[#1d4ed8] to-[#fbbf24]"></div>

    <div class="flex flex-col gap-6 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between mt-2">
        <div class="flex items-center gap-4">
            <!-- School Logo -->
            @if(isset($schoolSettings['logo_path']) && $schoolSettings['logo_path'])
                <img src="{{ asset($schoolSettings['logo_path']) }}" class="h-16 w-16 object-contain" alt="School Logo" />
            @else
                <div class="h-16 w-16 bg-[#071833] text-white flex items-center justify-center rounded-xl font-bold text-xl">B</div>
            @endif
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-950">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</h1>
                <div class="mt-1 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $schoolSettings['school_address'] ?? 'Lagos, Nigeria' }}</div>
                <div class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-wide">
                    Phone: {{ $schoolSettings['school_phone'] ?? '+234 000 000 0000' }} <span class="mx-1.5">|</span> Email: {{ $schoolSettings['school_email'] ?? 'info@school.test' }}
                </div>
            </div>
        </div>
        <div class="text-xs text-slate-600 bg-slate-50 border border-slate-150 rounded-2xl p-4 min-w-[220px] shadow-sm">
            <div class="font-black text-blue-600 uppercase tracking-wider text-[10px] mb-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Official Payment Receipt
            </div>
            <div class="space-y-1 font-semibold">
                <div>Receipt No: <span class="text-slate-900 font-bold">{{ $payment->receipt_no ?: 'Pending' }}</span></div>
                <div class="truncate">Reference: <span class="text-slate-700 font-mono text-[10px]">{{ $payment->reference }}</span></div>
                <div>Date: <span class="text-slate-700">{{ optional($payment->paid_at)->format('d M Y, h:i A') }}</span></div>
            </div>
        </div>
    </div>

    <!-- Student & Payment info cards -->
    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4">
            <div class="text-[9px] font-extrabold uppercase tracking-[0.2em] text-slate-500">Student payee</div>
            <div class="mt-2 text-base font-bold text-slate-950">{{ $payment->student->user->fullName() }}</div>
            <div class="mt-2 space-y-1 text-xs font-semibold text-slate-600">
                <div>Admission No: <span class="text-slate-900 font-bold">{{ $payment->student->admission_no }}</span></div>
                <div>Class: <span class="text-slate-900">{{ $payment->student->schoolClass->display_name ?? 'Not assigned' }}</span></div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4">
            <div class="text-[9px] font-extrabold uppercase tracking-[0.2em] text-slate-500">Transaction details</div>
            <div class="mt-2 space-y-1 text-xs font-semibold text-slate-600">
                <div>Payment Provider: <span class="text-slate-900 font-bold uppercase">{{ $payment->provider->label() }}</span></div>
                <div>Status: <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 text-emerald-800">{{ $payment->status->label() }}</span></div>
                <div>Channel: <span class="text-slate-900 font-semibold">{{ $payment->channel ?: 'Direct/Internal' }}</span></div>
                <div>Recorded By: <span class="text-slate-900">{{ $payment->recorder?->fullName() ?? 'System Auto' }}</span></div>
            </div>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-[#c8d6ea] shadow-sm">
        <div class="overflow-x-auto w-full">
            <table class="min-w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-extrabold tracking-wider border-b border-[#c8d6ea]">
                    <tr>
                        <th class="px-5 py-3">Fee Item</th>
                        <th class="px-5 py-3 text-right">Amount Due</th>
                        <th class="px-5 py-3 text-right">Paid Now</th>
                        <th class="px-5 py-3 text-right">Paid Total</th>
                        <th class="px-5 py-3 text-right">Remaining Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-150 text-slate-700 text-xs">
                    @foreach ($receiptLines as $line)
                        <tr class="hover:bg-slate-50/30 transition">
                            <td class="px-5 py-3 text-[#071833] font-bold text-sm">
                                {{ $line['fee_item'] ?? 'School fee payment' }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-600">NGN {{ number_format((float) ($line['amount_due'] ?? 0), 2) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-emerald-600">NGN {{ number_format((float) ($line['amount_paid_now'] ?? 0), 2) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-600">NGN {{ number_format((float) ($line['amount_paid_total'] ?? 0), 2) }}</td>
                            <td class="px-5 py-3 text-right font-black text-slate-950 text-sm">
                                NGN {{ number_format((float) ($line['balance'] ?? 0), 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50/80 border-t border-[#c8d6ea]">
                    <tr class="font-black text-slate-900">
                        <td colspan="4" class="px-5 py-3 text-right text-xs uppercase tracking-[0.2em] text-slate-500">Amount Paid In Full</td>
                        <td class="px-5 py-3 text-right text-base text-emerald-700 font-black">
                            NGN {{ number_format((float) $payment->amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if ($payment->note)
        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50/40 p-4 text-xs leading-relaxed text-slate-600">
            <div class="font-bold text-slate-900 uppercase tracking-wider text-[9px] mb-1">Bursary Notes / Remarks</div>
            <p>{{ $payment->note }}</p>
        </div>
    @endif

    <!-- Sign Off Area for Official Feel -->
    <div class="mt-8 pt-6 border-t border-dashed border-slate-200 grid grid-cols-2 gap-8 text-xs font-semibold text-slate-500">
        <div>
            <div class="h-8 border-b border-slate-200 w-40"></div>
            <div class="mt-1.5">Received By (Bursary / Accountant)</div>
        </div>
        <div class="text-right flex flex-col items-end">
            <div class="h-8 border-b border-slate-200 w-40"></div>
            <div class="mt-1.5">Official Stamp & Signature</div>
        </div>
    </div>
</div>
