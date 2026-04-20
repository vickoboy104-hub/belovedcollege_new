<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $payment->receipt_no ?: $payment->reference }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
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
    <main class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap gap-3 print:hidden">
            <button onclick="window.print()" class="theme-button">Print / Save as PDF</button>
            <a href="{{ $viewer && $viewer->hasAnyRole(['admin', 'principal', 'accountant']) ? route('admin.finance') : route('portal.index') }}" class="theme-button-secondary">
                {{ $viewer && $viewer->hasAnyRole(['admin', 'principal', 'accountant']) ? 'Back to finance' : 'Back to portal' }}
            </a>
        </div>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-900/5">
            <div class="flex flex-col gap-6 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-center gap-4">
                    <x-application-logo class="h-16 w-16" />
                    <div>
                        <h1 class="display-font text-3xl font-bold text-slate-950">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</h1>
                        <div class="mt-1 text-sm text-slate-500">{{ $schoolSettings['school_address'] ?? 'Lagos, Nigeria' }}</div>
                        <div class="text-sm text-slate-500">{{ $schoolSettings['school_phone'] ?? '+234 000 000 0000' }} | {{ $schoolSettings['school_email'] ?? 'info@school.test' }}</div>
                    </div>
                </div>
                <div class="text-sm text-slate-600">
                    <div class="font-semibold text-slate-900">Payment Receipt</div>
                    <div class="mt-2">Receipt No: {{ $payment->receipt_no ?: 'Pending receipt number' }}</div>
                    <div>Reference: {{ $payment->reference }}</div>
                    <div>Date: {{ optional($payment->paid_at)->format('d M Y, h:i A') }}</div>
                </div>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2">
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.28em] text-slate-500">Student</div>
                    <div class="mt-3 text-lg font-semibold text-slate-900">{{ $payment->student->user->fullName() }}</div>
                    <div class="mt-2 text-sm text-slate-600">Admission No: {{ $payment->student->admission_no }}</div>
                    <div class="text-sm text-slate-600">Class: {{ $payment->student->schoolClass->display_name ?? 'Not assigned' }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.28em] text-slate-500">Payment details</div>
                    <div class="mt-3 text-sm text-slate-600">Provider: {{ $payment->provider->label() }}</div>
                    <div class="text-sm text-slate-600">Status: {{ $payment->status->label() }}</div>
                    <div class="text-sm text-slate-600">Channel: {{ $payment->channel ?: 'Not specified' }}</div>
                    <div class="text-sm text-slate-600">Recorded by: {{ $payment->recorder?->fullName() ?? 'System' }}</div>
                </div>
            </div>

            <div class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Fee item</th>
                            <th class="px-5 py-4">Amount due</th>
                            <th class="px-5 py-4">Amount paid now</th>
                            <th class="px-5 py-4">Amount paid total</th>
                            <th class="px-5 py-4">Remaining balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($receiptLines as $line)
                            <tr class="border-t border-slate-200">
                                <td class="px-5 py-4 text-slate-900">{{ $line['fee_item'] ?? 'School fee payment' }}</td>
                                <td class="px-5 py-4 text-slate-600">NGN {{ number_format((float) ($line['amount_due'] ?? 0), 2) }}</td>
                                <td class="px-5 py-4 text-slate-600">NGN {{ number_format((float) ($line['amount_paid_now'] ?? 0), 2) }}</td>
                                <td class="px-5 py-4 text-slate-600">NGN {{ number_format((float) ($line['amount_paid_total'] ?? 0), 2) }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-900">NGN {{ number_format((float) ($line['balance'] ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($payment->note)
                <div class="mt-6 rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 text-sm leading-7 text-slate-600">
                    <div class="font-semibold text-slate-900">Note</div>
                    <p class="mt-2">{{ $payment->note }}</p>
                </div>
            @endif
        </section>
    </main>
</body>
</html>
