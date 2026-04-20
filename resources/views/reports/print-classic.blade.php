<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Classic Result Sheet - {{ $report->student->user->fullName() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            size: A4 portrait;
            margin: 7mm;
        }

        body {
            margin: 0;
            background: #f4f1ea;
            color: #111;
            font-family: "Arial Narrow", Arial, sans-serif;
        }

        .classic-wrap {
            width: 196mm;
            min-height: 279mm;
            margin: 0 auto;
            background: #fffdf8;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
            padding: 4mm 4mm 3mm;
            box-sizing: border-box;
        }

        .classic-actions {
            width: 196mm;
            margin: 10px auto;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .classic-sheet {
            border: 1px solid #111;
            padding: 2mm;
        }

        .classic-header {
            display: grid;
            grid-template-columns: 24mm 1fr 31mm;
            gap: 2mm;
            align-items: start;
        }

        .classic-logo {
            width: 22mm;
            height: 22mm;
            object-fit: contain;
        }

        .classic-title {
            text-align: center;
            line-height: 1.05;
        }

        .classic-title .school-name {
            font-family: Georgia, "Times New Roman", serif;
            font-weight: 900;
            font-size: 7.2mm;
            letter-spacing: 0.3mm;
        }

        .classic-title .motto {
            display: inline-block;
            margin-top: 0.8mm;
            padding: 0.4mm 1.6mm;
            border: 1px solid #111;
            font-weight: 700;
            font-size: 3.5mm;
        }

        .classic-title .subtitle {
            margin-top: 1mm;
            font-weight: 700;
            font-size: 3.55mm;
            text-transform: uppercase;
        }

        .classic-address {
            font-size: 3mm;
            line-height: 1.25;
            font-weight: 700;
        }

        .classic-meta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5mm;
            font-size: 3mm;
        }

        .classic-meta td {
            padding: 0.8mm 0.8mm 0.4mm;
            vertical-align: bottom;
        }

        .classic-meta .line {
            display: inline-block;
            width: 100%;
            border-bottom: 1px solid #111;
            min-height: 4mm;
            vertical-align: bottom;
            padding: 0 0.6mm;
            box-sizing: border-box;
        }

        .classic-meta .line.center {
            text-align: center;
        }

        .classic-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 1mm;
            font-size: 2.45mm;
        }

        .classic-table th,
        .classic-table td {
            border: 1px solid #111;
            padding: 0;
            text-align: center;
            vertical-align: middle;
            height: 5.1mm;
            overflow: hidden;
        }

        .classic-table .subject-head {
            width: 34mm;
            font-size: 3.1mm;
            font-weight: 800;
        }

        .classic-table .ability-head {
            width: 10.5mm;
            font-size: 2.5mm;
            font-weight: 800;
        }

        .classic-table .remarks-head {
            width: 14mm;
            font-size: 2.6mm;
            font-weight: 800;
        }

        .classic-table .subject-cell {
            text-align: left;
            padding: 0 1.2mm;
            font-size: 2.7mm;
            font-weight: 700;
            white-space: nowrap;
        }

        .classic-table .vertical {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            font-size: 2.2mm;
            font-weight: 700;
            letter-spacing: 0.1mm;
            padding: 0.6mm 0;
        }

        .classic-table .tiny {
            font-size: 2.1mm;
            line-height: 1.05;
        }

        .classic-table .total-row td {
            font-weight: 800;
            height: 4.8mm;
        }

        .classic-lower {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            margin-top: 0.8mm;
        }

        .classic-mini {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 2.55mm;
        }

        .classic-mini th,
        .classic-mini td {
            border: 1px solid #111;
            padding: 0 0.9mm;
            height: 4.3mm;
            vertical-align: middle;
        }

        .classic-mini th {
            font-weight: 800;
            text-align: left;
        }

        .classic-mini td:last-child,
        .classic-mini th:last-child {
            width: 11mm;
            text-align: center;
        }

        .classic-remarks {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            margin-top: 0.8mm;
        }

        .classic-remark-box {
            border: 1px solid #111;
            border-top: 0;
            padding: 0.9mm 1.2mm 0.5mm;
            min-height: 39mm;
            box-sizing: border-box;
            font-size: 2.55mm;
        }

        .classic-remark-box + .classic-remark-box {
            border-left: 0;
        }

        .classic-label {
            font-weight: 800;
        }

        .classic-write-line {
            border-bottom: 1px solid #111;
            min-height: 4.2mm;
            line-height: 4.1mm;
            white-space: nowrap;
            overflow: hidden;
        }

        .classic-sign-row {
            margin-top: 0.8mm;
        }

        .print-only {
            display: none;
        }

        @media print {
            body {
                background: #fff;
            }

            .classic-actions {
                display: none;
            }

            .classic-wrap {
                width: auto;
                min-height: auto;
                padding: 0;
                box-shadow: none;
                background: #fff;
            }
        }
    </style>
</head>
<body>
    <div class="classic-actions print:hidden">
        <button onclick="window.print()" class="theme-button">Print / Save as PDF</button>
        <a href="{{ $backUrl }}" class="theme-button-secondary">{{ $backLabel }}</a>
        <a href="{{ url()->current() }}" class="theme-button-secondary">Open modern version</a>
    </div>

    <main class="classic-wrap">
        <section class="classic-sheet">
            <div class="classic-header">
                <div>
                    <x-application-logo class="classic-logo" />
                </div>
                <div class="classic-title">
                    <div class="school-name">{{ strtoupper($schoolSettings['school_name'] ?? 'BELOVED COLLEGE') }}</div>
                    <div class="motto">{{ $schoolSettings['motto'] ?? 'Knowledge for all Nation' }}</div>
                    <div class="subtitle">Continuous Assessment Dossier for Junior Secondary Schools</div>
                </div>
                <div class="classic-address">
                    @php
                        $addressLines = collect(preg_split('/[\r\n,]+/', (string) ($schoolSettings['school_address'] ?? 'Ore, Ondo State')))
                            ->map(fn ($line) => trim($line))
                            ->filter()
                            ->take(4)
                            ->values();
                    @endphp
                    @foreach ($addressLines as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                </div>
            </div>

            <table class="classic-meta">
                <tr>
                    <td style="width: 13%;">Report for</td>
                    <td style="width: 19%;"><span class="line center">{{ $report->term->name }}</span></td>
                    <td style="width: 12%;">term</td>
                    <td style="width: 12%;">Session:</td>
                    <td style="width: 22%;"><span class="line center">{{ $report->term->academicSession->name ?? '' }}</span></td>
                    <td style="width: 5%;">Sex</td>
                    <td style="width: 17%;"><span class="line center">{{ $report->student->gender ?? '' }}</span></td>
                </tr>
                <tr>
                    <td>Name of Student</td>
                    <td colspan="4"><span class="line">{{ strtoupper($report->student->user->fullName()) }}</span></td>
                    <td colspan="2"><span class="line center"></span></td>
                </tr>
                <tr>
                    <td>Class</td>
                    <td><span class="line center">{{ $report->student->schoolClass->name ?? '' }}</span></td>
                    <td>Total If. Raw</td>
                    <td><span class="line center">{{ $report->total_score !== null ? number_format((float) $report->total_score, 2) : '' }}</span></td>
                    <td>Attendance</td>
                    <td colspan="2"><span class="line center">{{ $report->days_present ?? '' }}{{ $report->days_school_open ? '/' . $report->days_school_open : '' }}</span></td>
                </tr>
            </table>

            <table class="classic-table">
                <thead>
                    <tr>
                        <th class="subject-head" rowspan="2">SUBJECTS</th>
                        <th colspan="8" class="ability-head">COGNITIVE ABILITY</th>
                        <th class="remarks-head tiny" rowspan="2">Teacher's Remarks</th>
                    </tr>
                    <tr>
                        <th class="ability-head">(a)<div class="vertical">Quiz</div></th>
                        <th class="ability-head">(b)<div class="vertical">Test</div></th>
                        <th class="ability-head">(c)<div class="vertical">Project</div></th>
                        <th class="ability-head">(d)<div class="vertical">Exam</div></th>
                        <th class="ability-head">(e)<div class="vertical">Total %</div></th>
                        <th class="ability-head">(f)<div class="vertical">Grade</div></th>
                        <th class="ability-head">(g)<div class="vertical">Pos.</div></th>
                        <th class="ability-head">(h)<div class="vertical">Remark</div></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($classicRows as $row)
                        <tr>
                            <td class="subject-cell">{{ $row['subject_name'] }}</td>
                            <td>{{ $row['a'] }}</td>
                            <td>{{ $row['b'] }}</td>
                            <td>{{ $row['c'] }}</td>
                            <td>{{ $row['d'] }}</td>
                            <td>{{ $row['e'] }}</td>
                            <td>{{ $row['f'] }}</td>
                            <td>{{ $row['g'] }}</td>
                            <td class="tiny">{{ $row['h'] }}</td>
                            <td class="tiny">{{ $row['teacher_remark'] }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td class="subject-cell">TOTAL SCORE</td>
                        <td colspan="4"></td>
                        <td>{{ $report->average_score !== null ? number_format((float) $report->average_score, 2) : '' }}</td>
                        <td>{{ $report->overall_grade ?? '' }}</td>
                        <td>{{ $report->class_position ?? '' }}</td>
                        <td class="tiny">OVERALL</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 0.7mm; font-size: 2.7mm; font-weight: 800;">Character Assessment Grade A-E</div>

            <div class="classic-lower">
                <table class="classic-mini">
                    <tr>
                        <th colspan="2">CHARACTER DEVELOPMENT</th>
                    </tr>
                    @foreach ($characterTraits as $key => $label)
                        <tr>
                            <td>{{ strtoupper($label) }}</td>
                            <td>{{ $report->character_traits[$key] ?? '' }}</td>
                        </tr>
                    @endforeach
                </table>

                <table class="classic-mini">
                    <tr>
                        <th>PRACTICAL SKILLS</th>
                        <th>Grade</th>
                    </tr>
                    @foreach ($practicalSkills as $key => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $report->practical_skills[$key] ?? '' }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>

            <div class="classic-remarks">
                <div class="classic-remark-box">
                    <div class="classic-label">Class Teacher's remark</div>
                    <div class="classic-write-line">{{ $report->class_teacher_remark ?? '' }}</div>
                    <div class="classic-sign-row classic-label">Signature/Date</div>
                    <div class="classic-write-line">{{ $report->approved_at?->format('d/m/Y') ?? '' }}</div>
                    <div class="classic-sign-row classic-label">Guidance Counselor's Remarks</div>
                    <div class="classic-write-line">{{ $report->guidance_remark ?? '' }}</div>
                    <div class="classic-sign-row classic-label">Signature</div>
                    <div class="classic-write-line"></div>
                </div>
                <div class="classic-remark-box">
                    <div class="classic-label">House Master/Mistress's remarks</div>
                    <div class="classic-write-line">{{ $report->house_master_remark ?? '' }}</div>
                    <div class="classic-sign-row classic-label">Signature/Date</div>
                    <div class="classic-write-line">{{ $report->published_at?->format('d/m/Y') ?? '' }}</div>
                    <div class="classic-sign-row classic-label">Principal's Remarks</div>
                    <div class="classic-write-line">{{ $report->principal_remark ?? '' }}</div>
                    <div class="classic-sign-row classic-label">Signature</div>
                    <div class="classic-write-line"></div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
