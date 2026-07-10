<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fee List - {{ $schoolClass->display_name }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/print.css'])
    @include('partials.theme-overrides')
</head>
<body class="antialiased bg-[#edf2f9] text-slate-900 min-h-screen">
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">

        <!-- Print Header & Controls (hidden when printing) -->
        <div class="ps-controls-bar no-print mb-6">
            <div>
                <h2 class="font-bold text-slate-900 text-sm">Printable Fee List Ready</h2>
                <p class="text-xs text-slate-500 mt-0.5">Configure layout and print or save as PDF.</p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <button
                    id="print-settings-btn"
                    class="ps-trigger-btn"
                    onclick="openPrintSettings('#main-print-card', { itemsPerPage: 1, duplicate: true })"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Print Settings</span>
                </button>
                <a href="{{ route('admin.finance.records') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-[#c8d6ea] bg-white hover:bg-slate-50 font-bold text-xs text-slate-700 uppercase tracking-wider transition">
                    Back to records
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
                <div class="text-xs text-slate-600 bg-slate-50 border border-slate-150 rounded-2xl p-4 min-w-[200px] shadow-sm">
                    <div class="font-black text-slate-900 uppercase tracking-wider text-[10px] text-blue-600 mb-2">Printable Class Fee List</div>
                    <div class="space-y-1 font-semibold">
                        <div>Class: <span class="text-slate-900 font-bold">{{ $schoolClass->display_name }}</span></div>
                        <div>Date: <span class="text-slate-700">{{ now()->format('d M Y, h:i A') }}</span></div>
                        <div>Items Included: <span class="text-slate-700">{{ $feeItems->count() }}</span></div>
                    </div>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-blue-100 bg-blue-50/20 p-5">
                <div class="text-[10px] font-extrabold uppercase tracking-[0.25em] text-blue-600">Fee List Summary</div>
                <div class="mt-2 text-lg font-bold text-slate-950">{{ $schoolClass->display_name }} Program Schedule</div>
                <div class="mt-1 text-xs text-slate-500 font-medium">This schedule outlines selected term or session fee items authorized for instruction and facility access.</div>
            </div>

            <div class="mt-8 overflow-hidden rounded-2xl border border-[#c8d6ea] shadow-sm">
                <div class="overflow-x-auto w-full">
                    <table class="min-w-full text-left text-sm border-collapse" style="min-width: 640px;">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-extrabold tracking-wider border-b border-[#c8d6ea]">
                            <tr>
                                <th class="px-5 py-4">Fee Item</th>
                                <th class="px-5 py-4">Scope</th>
                                <th class="px-5 py-4">Term / Session</th>
                                <th class="px-5 py-4 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-150 text-slate-700 text-xs">
                            @forelse ($feeItems as $item)
                                <tr class="hover:bg-slate-50/30 transition">
                                    <td class="px-5 py-4 text-slate-900">
                                        <div class="font-bold text-sm text-[#071833]">{{ $item->name }}</div>
                                        @if ($item->description)
                                            <div class="mt-1 text-[10px] text-slate-400 font-medium leading-relaxed max-w-md">{{ $item->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-bold text-slate-600">{{ $item->schoolClass->display_name ?? 'All Classes' }}</td>
                                    <td class="px-5 py-4 font-semibold text-slate-500">
                                        {{ $item->term->name ?? 'No Term Specified' }}
                                        @if ($item->academicSession)
                                            <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $item->academicSession->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-bold text-slate-950 text-right text-sm">
                                        NGN {{ number_format((float) $item->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-slate-400 font-bold">
                                        No fee items were selected for this class list.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50/80 border-t border-[#c8d6ea]">
                            <tr class="font-black">
                                <td colspan="3" class="px-5 py-4.5 text-right text-xs uppercase tracking-[0.2em] text-slate-500">Total Authorized Amount</td>
                                <td class="px-5 py-4.5 text-right text-lg text-slate-950 font-black">
                                    NGN {{ number_format((float) $total, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Sign Off Area for Official Feel -->
            <div class="mt-12 pt-8 border-t border-dashed border-slate-200 grid grid-cols-2 gap-8 text-xs font-semibold text-slate-500">
                <div>
                    <div class="h-10 border-b border-slate-200 w-44"></div>
                    <div class="mt-2">Prepared By (Bursar / Accountant)</div>
                </div>
                <div class="text-right flex flex-col items-end">
                    <div class="h-10 border-b border-slate-200 w-44"></div>
                    <div class="mt-2">Authorized Signatory & Stamp</div>
                </div>
            </div>
        </section>
    </main>

    <!-- Print Settings JS (pure vanilla, no Alpine dependency) -->
    @vite(['resources/js/print-settings.js'])
</body>
</html>