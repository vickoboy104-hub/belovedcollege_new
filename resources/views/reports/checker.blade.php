<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Result Checker</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme-overrides')
</head>
<body class="result-checker-page antialiased">
    <main class="result-checker-shell mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="result-checker-card mx-auto max-w-3xl rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-900/5">
            <div class="flex items-center gap-4">
                <x-application-logo class="h-14 w-14" />
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Public result checker</p>
                    <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</h1>
                    <p class="mt-2 text-sm text-slate-600">Enter the approved admission number, term, and checker PIN to open a student's published report card.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('reports.checker.lookup') }}" class="mt-6 grid gap-4 md:grid-cols-[1fr,1fr,1fr,auto]">
                @csrf
                <input name="admission_no" value="{{ old('admission_no') }}" placeholder="Admission number" class="theme-input" required />
                <select name="term_id" class="theme-input" required>
                    <option value="">Select term</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" @selected((string) old('term_id') === (string) $term->id)>{{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}</option>
                    @endforeach
                </select>
                <input name="pin" value="{{ old('pin') }}" placeholder="Checker PIN" class="theme-input" required />
                <button type="submit" class="theme-button">Check result</button>
            </form>
        </div>

        @if ($report)
            <section class="result-checker-card mt-8 rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-900/5">
                <div class="mb-6 flex flex-wrap gap-3 print:hidden">
                    <button onclick="window.print()" class="theme-button">Print / Save as PDF</button>
                    <a href="{{ route('reports.checker') }}" class="theme-button-secondary">Check another result</a>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Student</div>
                        <div class="mt-2 text-lg font-semibold text-slate-900">{{ $report->student->user->fullName() }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $report->student->admission_no }}</div>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Class</div>
                        <div class="mt-2 text-lg font-semibold text-slate-900">{{ $report->student->schoolClass->display_name ?? 'Not assigned' }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $report->term->name }}</div>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Average</div>
                        <div class="mt-2 text-lg font-semibold text-slate-900">{{ $report->average_score !== null ? number_format((float) $report->average_score, 2).'%' : 'N/A' }}</div>
                        <div class="mt-1 text-sm text-slate-600">Grade: {{ $report->overall_grade ?: 'N/A' }}</div>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Position</div>
                        <div class="mt-2 text-lg font-semibold text-slate-900">{{ $report->class_position ?: 'N/A' }}</div>
                        <div class="mt-1 text-sm text-slate-600">Published {{ $report->published_at?->format('d M Y') }}</div>
                    </div>
                </div>

                <div class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200">
                    <div class="overflow-x-auto w-full">
                        <table class="min-w-full text-left text-sm" style="min-width: 800px;">
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
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subjectRows as $row)
                                    <tr class="border-t border-slate-200">
                                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $row['subject_name'] }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['quiz_score'], 2) }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['test_score'], 2) }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['project_score'], 2) }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ number_format((float) $row['exam_score'], 2) }}</td>
                                        <td class="px-5 py-4 font-semibold text-slate-900">{{ number_format((float) $row['percentage'], 2) }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ $row['grade'] }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ $row['remark'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 grid gap-6 xl:grid-cols-2">
                    <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 text-sm text-slate-600">
                        <div class="font-semibold text-slate-900">Class teacher remark</div>
                        <p class="mt-2">{{ $report->class_teacher_remark ?: 'No class teacher remark yet.' }}</p>
                        <div class="mt-4 font-semibold text-slate-900">Principal remark</div>
                        <p class="mt-2">{{ $report->principal_remark ?: 'No principal remark yet.' }}</p>
                    </div>
                    <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 text-sm text-slate-600">
                        <div class="font-semibold text-slate-900">Attendance</div>
                        <p class="mt-2">Days opened: {{ $report->days_school_open ?? 0 }}</p>
                        <p>Days present: {{ $report->days_present ?? 0 }}</p>
                        <p>Days absent: {{ $report->days_absent ?? 0 }}</p>
                        <div class="mt-4 font-semibold text-slate-900">Next term begins</div>
                        <p class="mt-2">{{ $report->next_term_begins_on?->format('d M Y') ?? 'Not set' }}</p>
                    </div>
                </div>
            </section>
        @endif
    </main>
</body>
</html>
