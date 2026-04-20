<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Result Sheet - {{ $report->student->user->fullName() }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <main class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap gap-3 print:hidden">
            <button onclick="window.print()" class="theme-button">Print / Save as PDF</button>
            <a href="{{ url()->current() }}?layout=classic" class="theme-button-secondary">Open classic one-page version</a>
            <a href="{{ $backUrl }}" class="theme-button-secondary">{{ $backLabel }}</a>
        </div>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-900/5">
            <div class="flex flex-col gap-6 border-b border-slate-200 pb-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-center gap-4">
                    <x-application-logo class="h-16 w-16" />
                    <div>
                        <h1 class="display-font text-3xl font-bold text-slate-950">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</h1>
                        <div class="mt-1 text-sm text-slate-500">{{ $schoolSettings['motto'] ?? 'Knowledge for all Nation' }}</div>
                        <div class="text-sm text-slate-500">{{ $schoolSettings['school_address'] ?? 'Ore, Ondo State' }}</div>
                        <div class="text-sm text-slate-500">{{ $schoolSettings['school_phone'] ?? '+234 000 000 0000' }} | {{ $schoolSettings['school_email'] ?? 'info@school.test' }}</div>
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 text-sm text-slate-600">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Report summary</div>
                    <div class="mt-3">Term: <span class="font-semibold text-slate-900">{{ $report->term->name }}</span></div>
                    <div>Session: <span class="font-semibold text-slate-900">{{ $report->term->academicSession->name ?? 'Not assigned' }}</span></div>
                    <div>Published: <span class="font-semibold text-slate-900">{{ $report->published_at?->format('d M Y, h:i A') ?? 'Draft' }}</span></div>
                </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Student</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $report->student->user->fullName() }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ $report->student->admission_no }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Class</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $report->student->schoolClass->display_name ?? 'Not assigned' }}</div>
                    <div class="mt-1 text-sm text-slate-600">Position: {{ $report->class_position ?: 'N/A' }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Average</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $report->average_score !== null ? number_format((float) $report->average_score, 2).'%' : 'N/A' }}</div>
                    <div class="mt-1 text-sm text-slate-600">Grade: {{ $report->overall_grade ?: 'N/A' }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Attendance</div>
                    <div class="mt-2 text-lg font-semibold text-slate-900">{{ $report->days_present ?? 0 }} / {{ $report->days_school_open ?? 0 }}</div>
                    <div class="mt-1 text-sm text-slate-600">Absent: {{ $report->days_absent ?? 0 }}</div>
                </div>
            </div>

            <div class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Subject</th>
                            <th class="px-5 py-4">Quiz</th>
                            <th class="px-5 py-4">Test</th>
                            <th class="px-5 py-4">Project</th>
                            <th class="px-5 py-4">Exam</th>
                            <th class="px-5 py-4">Total %</th>
                            <th class="px-5 py-4">Grade</th>
                            <th class="px-5 py-4">Remark</th>
                            <th class="px-5 py-4">Teacher</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subjectRows as $row)
                            <tr class="border-t border-slate-200">
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $row['subject_name'] }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['quiz_score'], 2) }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['test_score'], 2) }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['project_score'], 2) }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['exam_score'], 2) }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ number_format((float) $row['percentage'], 2) }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $row['grade'] }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $row['remark'] }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $row['teachers'] ?: 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-6 text-sm text-slate-500">No compiled term scores are available for this report yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8 grid gap-8 xl:grid-cols-2">
                <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                    <h2 class="display-font text-xl font-bold text-slate-950">Character development</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($characterTraits as $key => $label)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="text-sm font-semibold text-slate-900">{{ $label }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $report->character_traits[$key] ?? 'Not graded' }}</div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                    <h2 class="display-font text-xl font-bold text-slate-950">Practical skills</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($practicalSkills as $key => $label)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="text-sm font-semibold text-slate-900">{{ $label }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $report->practical_skills[$key] ?? 'Not graded' }}</div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 text-sm leading-7 text-slate-600">
                    <div class="font-semibold text-slate-900">Class teacher remark</div>
                    <p class="mt-2">{{ $report->class_teacher_remark ?: 'No class teacher remark yet.' }}</p>
                    <div class="mt-4 font-semibold text-slate-900">Guidance counsellor remark</div>
                    <p class="mt-2">{{ $report->guidance_remark ?: 'No guidance counsellor remark yet.' }}</p>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 text-sm leading-7 text-slate-600">
                    <div class="font-semibold text-slate-900">Principal remark</div>
                    <p class="mt-2">{{ $report->principal_remark ?: 'No principal remark yet.' }}</p>
                    <div class="mt-4 font-semibold text-slate-900">House master / mistress remark</div>
                    <p class="mt-2">{{ $report->house_master_remark ?: 'No house remark yet.' }}</p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-3">
                <div class="rounded-[1.75rem] border border-slate-200 px-5 py-5 text-sm text-slate-600">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Approved by</div>
                    <div class="mt-3 font-semibold text-slate-900">{{ $report->approver?->fullName() ?? 'Awaiting approval' }}</div>
                    <div class="mt-1">{{ $report->approved_at?->format('d M Y, h:i A') ?? 'Not yet approved' }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 px-5 py-5 text-sm text-slate-600">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Next term begins</div>
                    <div class="mt-3 font-semibold text-slate-900">{{ $report->next_term_begins_on?->format('d M Y') ?? 'Not set' }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 px-5 py-5 text-sm text-slate-600">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Student signature</div>
                    <div class="mt-10 border-t border-dashed border-slate-300 pt-3">__________________________</div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
