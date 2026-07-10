<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Record - {{ $student->user->fullName() }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme-overrides')
</head>
<body class="antialiased">
    <main class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap gap-3 print:hidden">
            <button onclick="window.print()" class="theme-button">Print / Save as PDF</button>
            <a href="{{ $backUrl }}" class="theme-button-secondary">{{ $backLabel }}</a>
        </div>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-900/5">
            <div class="flex flex-col gap-6 border-b border-slate-200 pb-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-center gap-4">
                    <x-application-logo class="h-16 w-16" />
                    <div>
                        <h1 class="display-font text-3xl font-bold text-slate-950">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</h1>
                        <div class="mt-1 text-sm text-slate-500">Complete student record and history</div>
                        <div class="text-sm text-slate-500">{{ $schoolSettings['school_address'] ?? 'Ore, Ondo State' }}</div>
                    </div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 text-sm text-slate-600">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Student status</div>
                    <div class="mt-3 font-semibold text-slate-900">{{ ucfirst($student->status ?? 'active') }}</div>
                    <div class="mt-1">Enrolled {{ optional($student->enrolled_at)->format('d M Y') ?? 'Unknown date' }}</div>
                </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Student</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $student->user->fullName() }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ $student->admission_no }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Class</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $student->schoolClass->display_name ?? 'Not assigned' }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ $student->academicSession->name ?? 'No active session' }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Parent / guardian</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $student->parent->name ?? $student->guardian_name ?? 'Not recorded' }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ $student->parent->email ?? $student->guardian_phone ?? 'No contact available' }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Attendance rate</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $record['attendance_summary']['present_rate'] !== null ? number_format((float) $record['attendance_summary']['present_rate'], 2).'%' : 'N/A' }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ $record['attendance_summary']['present_count'] }} present from {{ $record['attendance_summary']['total_entries'] }} entries</div>
                </div>
            </div>

            <div class="mt-8 grid gap-8 xl:grid-cols-2">
                <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                    <h2 class="display-font text-xl font-bold text-slate-950">Identity and background</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 text-sm text-slate-600">
                        <div><span class="font-semibold text-slate-900">Student ID:</span> {{ $student->student_id_no ?: 'Not assigned' }}</div>
                        <div><span class="font-semibold text-slate-900">Gender:</span> {{ $student->gender ?: 'Not recorded' }}</div>
                        <div><span class="font-semibold text-slate-900">Date of birth:</span> {{ optional($student->date_of_birth)->format('d M Y') ?: 'Not recorded' }}</div>
                        <div><span class="font-semibold text-slate-900">Place of birth:</span> {{ $student->place_of_birth ?: 'Not recorded' }}</div>
                        <div><span class="font-semibold text-slate-900">Nationality:</span> {{ $student->nationality ?: 'Not recorded' }}</div>
                        <div><span class="font-semibold text-slate-900">State of origin:</span> {{ $student->state_of_origin ?: 'Not recorded' }}</div>
                        <div><span class="font-semibold text-slate-900">Religion:</span> {{ $student->religion ?: 'Not recorded' }}</div>
                        <div><span class="font-semibold text-slate-900">Previous school:</span> {{ $student->previous_school ?: 'Not recorded' }}</div>
                    </div>
                    <div class="mt-4 text-sm text-slate-600">
                        <div class="font-semibold text-slate-900">Address</div>
                        <p class="mt-2">{{ $student->address ?: 'No home address recorded.' }}</p>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                    <h2 class="display-font text-xl font-bold text-slate-950">Health and emergency record</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 text-sm text-slate-600">
                        <div><span class="font-semibold text-slate-900">Doctor:</span> {{ $student->doctor_name ?: 'Not recorded' }}</div>
                        <div><span class="font-semibold text-slate-900">Doctor phone:</span> {{ $student->doctor_phone ?: 'Not recorded' }}</div>
                    </div>
                    <div class="mt-4 text-sm text-slate-600">
                        <div class="font-semibold text-slate-900">Physical notes</div>
                        <p class="mt-2">{{ $student->physical_notes ?: 'No physical notes recorded.' }}</p>
                    </div>
                    <div class="mt-4 text-sm text-slate-600">
                        <div class="font-semibold text-slate-900">Medical notes</div>
                        <p class="mt-2">{{ $student->medical_notes ?: 'No medical notes recorded.' }}</p>
                    </div>
                </section>
            </div>

            <div class="mt-8 grid gap-8 xl:grid-cols-3">
                <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                    <h2 class="display-font text-xl font-bold text-slate-950">Finance summary</h2>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">Invoices raised: <span class="font-semibold text-slate-900">NGN {{ number_format((float) $record['finance_summary']['invoice_total'], 2) }}</span></div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">Payments received: <span class="font-semibold text-slate-900">NGN {{ number_format((float) $record['finance_summary']['paid_total'], 2) }}</span></div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">Outstanding balance: <span class="font-semibold text-slate-900">NGN {{ number_format((float) $record['finance_summary']['outstanding_total'], 2) }}</span></div>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5 xl:col-span-2">
                    <h2 class="display-font text-xl font-bold text-slate-950">Promotion history</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($record['promotions'] as $promotion)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                                <div class="font-semibold text-slate-900">{{ ucfirst($promotion->promotion_status) }}</div>
                                <div class="mt-1">{{ $promotion->fromAcademicSession->name ?? 'Unknown session' }} to {{ $promotion->toAcademicSession->name ?? 'Unknown session' }}</div>
                                <div class="mt-1">{{ $promotion->fromSchoolClass->display_name ?? 'No class' }} to {{ $promotion->toSchoolClass->display_name ?? 'No class' }}</div>
                                <div class="mt-1">Approved {{ optional($promotion->approved_at)->format('d M Y') ?: 'N/A' }} by {{ $promotion->approver?->fullName() ?? 'System' }}</div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No promotion history has been recorded yet.</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="mt-8 grid gap-8 xl:grid-cols-2">
                <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                    <h2 class="display-font text-xl font-bold text-slate-950">Result publication history</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($record['reports'] as $reportRow)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                                <div class="font-semibold text-slate-900">{{ $reportRow->term->name }} - {{ $reportRow->term->academicSession->name ?? 'No session' }}</div>
                                <div class="mt-1">Average: {{ $reportRow->average_score !== null ? number_format((float) $reportRow->average_score, 2).'%' : 'N/A' }}</div>
                                <div class="mt-1">Portal access: {{ $reportRow->portal_enabled ? 'Enabled' : 'Disabled' }} | Checker access: {{ $reportRow->checker_enabled ? 'Enabled' : 'Disabled' }}</div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No term reports have been approved yet.</div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                    <h2 class="display-font text-xl font-bold text-slate-950">Recent payments</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($record['payments']->take(8) as $payment)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                                <div class="font-semibold text-slate-900">{{ $payment->feeInvoice->feeItem->name ?? 'School fee payment' }}</div>
                                <div class="mt-1">NGN {{ number_format((float) $payment->amount, 2) }} | {{ $payment->status->label() }}</div>
                                <div class="mt-1">{{ optional($payment->paid_at)->format('d M Y, h:i A') ?: 'Date unavailable' }}</div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No payment has been recorded for this student yet.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        </section>
    </main>
</body>
</html>
