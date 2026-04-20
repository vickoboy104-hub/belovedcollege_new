<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fee List {{ $schoolClass->display_name }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <main class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap gap-3 print:hidden">
            <button onclick="window.print()" class="theme-button">Print / Save as PDF</button>
            <a href="{{ route('admin.finance.records') }}" class="theme-button-secondary">Back to finance records</a>
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
                    <div class="font-semibold text-slate-900">Printable Class Fee List</div>
                    <div class="mt-2">Class: {{ $schoolClass->display_name }}</div>
                    <div>Date: {{ now()->format('d M Y, h:i A') }}</div>
                    <div>Items: {{ $feeItems->count() }}</div>
                </div>
            </div>

            <div class="mt-8 rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                <div class="text-xs uppercase tracking-[0.28em] text-slate-500">Fee list summary</div>
                <div class="mt-3 text-lg font-semibold text-slate-900">{{ $schoolClass->display_name }}</div>
                <div class="mt-2 text-sm text-slate-600">Only the selected fee items are listed below for printing or export.</div>
            </div>

            <div class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Fee item</th>
                            <th class="px-5 py-4">Scope</th>
                            <th class="px-5 py-4">Term / session</th>
                            <th class="px-5 py-4">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($feeItems as $item)
                            <tr class="border-t border-slate-200">
                                <td class="px-5 py-4 text-slate-900">
                                    <div class="font-semibold">{{ $item->name }}</div>
                                    @if ($item->description)
                                        <div class="mt-1 text-xs text-slate-500">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $item->schoolClass->display_name ?? 'All classes' }}</td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $item->term->name ?? 'No term' }}
                                    @if ($item->academicSession)
                                        <div class="text-xs text-slate-500">{{ $item->academicSession->name }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-900">NGN {{ number_format((float) $item->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr class="border-t border-slate-200">
                                <td colspan="4" class="px-5 py-6 text-slate-500">No fee items were selected for this class.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50">
                        <tr class="border-t border-slate-200">
                            <td colspan="3" class="px-5 py-4 text-right text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Total</td>
                            <td class="px-5 py-4 text-lg font-bold text-slate-950">NGN {{ number_format((float) $total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
