<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt - {{ $payment->receipt_no ?: $payment->reference }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/print.css'])
    @include('partials.theme-overrides')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                color: #0f172a !important;
            }
            .print-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="antialiased bg-[#edf2f9] text-slate-900 min-h-screen">
    @php
        $viewer = auth()->user();
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

    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Print Header & Controls -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 no-print bg-white/80 backdrop-blur border border-[#c8d6ea] rounded-2xl px-6 py-4.5 shadow-sm">
            <div>
                <h2 class="font-bold text-slate-900 text-sm">Official Receipt Ready</h2>
                <p class="text-xs text-slate-500 mt-0.5">Click Print below to generate a hardcopy or download a PDF.</p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <button type="button" onclick="openPrintSettings('#main-print-card', { itemsPerPage: 1, duplicate: true })" class="ps-trigger-btn">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Settings</span>
                </button>
                <a href="{{ $viewer && $viewer->hasAnyRole(['admin', 'principal', 'accountant']) ? route('admin.finance') : route('portal.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-[#c8d6ea] bg-white hover:bg-slate-50 font-bold text-xs text-slate-700 uppercase tracking-wider transition">
                    {{ $viewer && $viewer->hasAnyRole(['admin', 'principal', 'accountant']) ? 'Back to finance' : 'Back to portal' }}
                </a>
            </div>
        </div>

        <!-- Official Printable Card Layout -->
        <section id="main-print-card" class="print-card rounded-[24px] border border-[#c8d6ea] bg-white p-8 shadow-xl shadow-slate-900/5 relative overflow-hidden">
            <!-- Sleek Top Accent Banner -->
            <div class="absolute top-0 left-0 right-0 h-2.5 bg-gradient-to-r from-[#071833] via-[#1d4ed8] to-[#fbbf24]"></div>

            <div class="flex flex-col gap-6 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between mt-2">
                <div class="flex items-center gap-4">
                    <x-application-logo class="h-16 w-16 text-[#071833]" />
                    <div>
                        <h1 class="display-font text-3xl font-black tracking-tight text-slate-950">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</h1>
                        <div class="mt-1.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $schoolSettings['school_address'] ?? 'Lagos, Nigeria' }}</div>
                        <div class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-wide">
                            Phone: {{ $schoolSettings['school_phone'] ?? '+234 000 000 0000' }} <span class="mx-1.5">|</span> Email: {{ $schoolSettings['school_email'] ?? 'info@school.test' }}
                        </div>
                    </div>
                </div>
                <div class="text-xs text-slate-600 bg-slate-50 border border-slate-150 rounded-2xl p-4 min-w-[220px] shadow-sm">
                    <div class="font-black text-blue-600 uppercase tracking-wider text-[10px] mb-2 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
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
            <div class="mt-8 grid gap-6 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                    <div class="text-[9px] font-extrabold uppercase tracking-[0.2em] text-slate-500">Student payee</div>
                    <div class="mt-2.5 text-base font-bold text-slate-950">{{ $payment->student->user->fullName() }}</div>
                    <div class="mt-2 space-y-1 text-xs font-semibold text-slate-600">
                        <div>Admission No: <span class="text-slate-900 font-bold">{{ $payment->student->admission_no }}</span></div>
                        <div>Class: <span class="text-slate-900">{{ $payment->student->schoolClass->display_name ?? 'Not assigned' }}</span></div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                    <div class="text-[9px] font-extrabold uppercase tracking-[0.2em] text-slate-500">Transaction details</div>
                    <div class="mt-2.5 space-y-1 text-xs font-semibold text-slate-600">
                        <div>Payment Provider: <span class="text-slate-900 font-bold uppercase">{{ $payment->provider->label() }}</span></div>
                        <div>Status: <x-status-badge :status="$payment->status->label()" class="scale-90 origin-left" /></div>
                        <div>Channel: <span class="text-slate-900 font-semibold">{{ $payment->channel ?: 'Direct/Internal' }}</span></div>
                        <div>Recorded By: <span class="text-slate-900">{{ $payment->recorder?->fullName() ?? 'System Auto' }}</span></div>
                    </div>
                </div>
            </div>

            <div class="mt-8 overflow-hidden rounded-2xl border border-[#c8d6ea] shadow-sm">
                <div class="overflow-x-auto w-full">
                    <table class="min-w-full text-left text-sm border-collapse" style="min-width: 640px;">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-extrabold tracking-wider border-b border-[#c8d6ea]">
                            <tr>
                                <th class="px-5 py-4">Fee Item</th>
                                <th class="px-5 py-4 text-right">Amount Due</th>
                                <th class="px-5 py-4 text-right">Paid Now</th>
                                <th class="px-5 py-4 text-right">Paid Total</th>
                                <th class="px-5 py-4 text-right">Remaining Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-150 text-slate-700 text-xs">
                            @foreach ($receiptLines as $line)
                                <tr class="hover:bg-slate-50/30 transition">
                                    <td class="px-5 py-4 text-[#071833] font-bold text-sm">
                                        {{ $line['fee_item'] ?? 'School fee payment' }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold text-slate-600">NGN {{ number_format((float) ($line['amount_due'] ?? 0), 2) }}</td>
                                    <td class="px-5 py-4 text-right font-bold text-emerald-600">NGN {{ number_format((float) ($line['amount_paid_now'] ?? 0), 2) }}</td>
                                    <td class="px-5 py-4 text-right font-semibold text-slate-600">NGN {{ number_format((float) ($line['amount_paid_total'] ?? 0), 2) }}</td>
                                    <td class="px-5 py-4 text-right font-black text-slate-950 text-sm">
                                        NGN {{ number_format((float) ($line['balance'] ?? 0), 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/80 border-t border-[#c8d6ea]">
                            <tr class="font-black text-slate-900">
                                <td colspan="4" class="px-5 py-4.5 text-right text-xs uppercase tracking-[0.2em] text-slate-500">Amount Paid In Full</td>
                                <td class="px-5 py-4.5 text-right text-lg text-emerald-700 font-black">
                                    NGN {{ number_format((float) $payment->amount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if ($payment->note)
                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/40 p-5 text-xs leading-relaxed text-slate-600">
                    <div class="font-bold text-slate-900 uppercase tracking-wider text-[9px] mb-1.5">Bursary Notes / Remarks</div>
                    <p>{{ $payment->note }}</p>
                </div>
            @endif

            <!-- Sign Off Area for Official Feel -->
            <div class="mt-12 pt-8 border-t border-dashed border-slate-200 grid grid-cols-2 gap-8 text-xs font-semibold text-slate-500">
                <div>
                    <div class="h-10 border-b border-slate-200 w-44"></div>
                    <div class="mt-2">Received By (Bursary / Accountant)</div>
                </div>
                <div class="text-right flex flex-col items-end">
                    <div class="h-10 border-b border-slate-200 w-44"></div>
                    <div class="mt-2">Official Stamp & Signature</div>
                </div>
            </div>
        </section>
    </main>
    @vite(['resources/js/print-settings.js'])
</body>
</html>
