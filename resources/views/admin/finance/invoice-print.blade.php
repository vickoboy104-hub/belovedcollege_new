<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/css/print.css'])
    <style>
        @page { size: A4; margin: 14mm; }
        body { background: #eef3f8; color: #0f172a; }
        .sheet { width: 100%; max-width: 210mm; margin: 0 auto; background: #fff; }
        .page-break { break-before: page; }
        .avoid-break { break-inside: avoid; }
        @media print {
            body { background: #fff !important; }
            .no-print { display: none !important; }
            .sheet { max-width: none; box-shadow: none !important; border: 0 !important; }
        }
    </style>
</head>
<body class="antialiased">
@php
    $sessionName = $invoice->feeItem?->academicSession?->name
        ?? $invoice->student?->academicSession?->name
        ?? 'Not specified';
    $paidPayments = $invoice->payments->filter(fn ($payment) => $payment->status->value === 'paid')->sortBy('paid_at');
    $statusLabel = match ((string) $invoice->status) {
        'paid' => 'PAID',
        'part-paid' => 'PARTIALLY PAID',
        default => 'UNPAID',
    };
@endphp

<div class="no-print mx-auto flex max-w-4xl items-center justify-between gap-4 px-4 py-5">
    <a href="{{ route('admin.finance', ['section' => 'recent-invoices']) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700">Back to finance</a>
    <button type="button" onclick="window.print()" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-black text-white shadow-sm">Print / Save as PDF</button>
</div>

<main class="sheet rounded-2xl border border-slate-200 p-7 shadow-xl sm:p-10">
    <header class="avoid-break flex flex-col gap-6 border-b-2 border-slate-900 pb-6 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-4">
            <x-application-logo class="h-16 w-16 text-[#071833]" />
            <div>
                <h1 class="text-2xl font-black tracking-tight text-[#071833]">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</h1>
                <p class="mt-1 text-xs font-semibold text-slate-600">{{ $schoolSettings['school_address'] ?? 'Nigeria' }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $schoolSettings['school_phone'] ?? '' }} @if(filled($schoolSettings['school_email'] ?? null)) • {{ $schoolSettings['school_email'] }} @endif</p>
            </div>
        </div>
        <div class="rounded-xl bg-slate-900 px-5 py-4 text-white sm:text-right">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-300">Student Invoice</p>
            <p class="mt-1 font-mono text-sm font-bold">{{ $invoice->invoice_no }}</p>
            <p class="mt-2 text-xs text-slate-300">Issued {{ optional($invoice->issued_at)->format('d M Y') ?: '—' }}</p>
        </div>
    </header>

    <section class="avoid-break mt-7 grid gap-5 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 p-5">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Student</p>
            <p class="mt-2 text-lg font-black text-slate-900">{{ $invoice->student->user->fullName() }}</p>
            <dl class="mt-3 space-y-1 text-xs text-slate-600">
                <div><dt class="inline font-bold">Admission No:</dt> <dd class="inline">{{ $invoice->student->admission_no }}</dd></div>
                <div><dt class="inline font-bold">Class:</dt> <dd class="inline">{{ $invoice->student->schoolClass->display_name ?? 'Unassigned' }}</dd></div>
                <div><dt class="inline font-bold">Session:</dt> <dd class="inline">{{ $sessionName }}</dd></div>
            </dl>
        </div>
        <div class="rounded-xl border border-slate-200 p-5">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Invoice Status</p>
            <p class="mt-2 text-xl font-black {{ $invoice->status === 'paid' ? 'text-emerald-700' : ($invoice->status === 'part-paid' ? 'text-amber-700' : 'text-rose-700') }}">{{ $statusLabel }}</p>
            <dl class="mt-3 space-y-1 text-xs text-slate-600">
                <div><dt class="inline font-bold">Due Date:</dt> <dd class="inline">{{ optional($invoice->due_date)->format('d M Y') ?: 'Not specified' }}</dd></div>
                <div><dt class="inline font-bold">Term:</dt> <dd class="inline">{{ $invoice->feeItem?->term?->name ?? 'Not specified' }}</dd></div>
            </dl>
        </div>
    </section>

    <section class="mt-8">
        <h2 class="mb-3 text-sm font-black uppercase tracking-wider text-[#071833]">Fee Breakdown</h2>
        <div class="overflow-hidden rounded-xl border border-slate-300">
            <table class="w-full border-collapse text-sm">
                <thead class="bg-slate-100 text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                    <tr><th class="px-4 py-3">Fee Item</th><th class="px-4 py-3 text-right">Amount</th></tr>
                </thead>
                <tbody>
                    <tr class="border-t border-slate-200">
                        <td class="px-4 py-4 font-bold text-slate-900">{{ $invoice->feeItem->name ?? 'School Fee Invoice' }}</td>
                        <td class="px-4 py-4 text-right font-black">NGN {{ number_format((float) $invoice->amount_due, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot class="border-t-2 border-slate-900 bg-slate-50">
                    <tr><td class="px-4 py-3 text-right text-xs font-black uppercase">Total</td><td class="px-4 py-3 text-right text-lg font-black">NGN {{ number_format((float) $invoice->amount_due, 2) }}</td></tr>
                    <tr><td class="px-4 py-2 text-right text-xs font-bold text-slate-500">Paid</td><td class="px-4 py-2 text-right font-bold text-emerald-700">NGN {{ number_format((float) $invoice->amount_paid, 2) }}</td></tr>
                    <tr><td class="px-4 py-3 text-right text-xs font-black uppercase">Balance</td><td class="px-4 py-3 text-right text-lg font-black text-rose-700">NGN {{ number_format((float) $invoice->balance, 2) }}</td></tr>
                </tfoot>
            </table>
        </div>
    </section>

    @if ($invoice->notes)
        <section class="avoid-break mt-6 rounded-xl bg-slate-50 p-4 text-xs leading-relaxed text-slate-600">
            <span class="font-black text-slate-800">Invoice note:</span> {{ $invoice->notes }}
        </section>
    @endif

    <section class="page-break mt-8 pt-2">
        <h2 class="mb-3 text-sm font-black uppercase tracking-wider text-[#071833]">Payment History</h2>
        <div class="overflow-hidden rounded-xl border border-slate-300">
            <table class="w-full border-collapse text-xs">
                <thead class="bg-slate-100 text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                    <tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">Method</th><th class="px-3 py-3">Reference</th><th class="px-3 py-3 text-right">Amount</th></tr>
                </thead>
                <tbody>
                    @forelse ($paidPayments as $payment)
                        <tr class="border-t border-slate-200">
                            <td class="px-3 py-3">{{ optional($payment->paid_at)->format('d M Y') ?: '—' }}</td>
                            <td class="px-3 py-3 font-bold">{{ $payment->channel ? str($payment->channel)->replace('-', ' ')->title() : $payment->provider->label() }}</td>
                            <td class="px-3 py-3 font-mono text-[10px]">{{ $payment->reference }}</td>
                            <td class="px-3 py-3 text-right font-black">NGN {{ number_format((float) $payment->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No confirmed payments have been recorded for this invoice.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <footer class="avoid-break mt-12 border-t border-slate-200 pt-5 text-center text-[10px] font-semibold text-slate-400">
        This invoice was generated by {{ $schoolSettings['school_name'] ?? 'Beloved Schools' }} ERP. Verify all payments with the bursary.
    </footer>
</main>
</body>
</html>
