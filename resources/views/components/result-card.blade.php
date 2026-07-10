@props(['subject', 'grade', 'testScore' => null, 'examScore' => null, 'totalScore', 'remark' => null])

@php
    $gradeColor = [
        'A1' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'B2' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'B3' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'C4' => 'bg-blue-50 text-blue-700 border border-blue-200',
        'C5' => 'bg-blue-50 text-blue-700 border border-blue-200',
        'C6' => 'bg-blue-50 text-blue-700 border border-blue-200',
        'D7' => 'bg-amber-50 text-amber-800 border border-amber-200',
        'E8' => 'bg-orange-50 text-orange-800 border border-orange-200',
        'F9' => 'bg-rose-50 text-rose-700 border border-rose-200',
    ][strtoupper((string) $grade)] ?? 'bg-slate-50 text-slate-700 border border-slate-200';
@endphp

<div {{ $attributes->merge(['class' => 'card bg-white border border-[#c8d6ea] rounded-[18px] p-5 shadow-[0_10px_25px_rgba(15,23,42,0.08)] hover:border-[#fbbf24] hover:shadow-[0_16px_35px_rgba(15,23,42,0.14)] hover:-translate-y-0.5 transition-all duration-250 flex flex-col justify-between gap-4']) }}>
    <div class="space-y-3">
        <!-- Top bar -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-icon-box icon="reports" color="purple" size="sm" />
                <h3 class="display-font text-base font-extrabold text-slate-900 tracking-tight leading-snug">
                    {{ $subject }}
                </h3>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-extrabold {{ $gradeColor }}">
                {{ $grade }}
            </span>
        </div>

        <!-- Scores Grid -->
        <div class="grid grid-cols-3 gap-2 text-center bg-slate-50 p-2.5 rounded-[12px] border border-slate-100/55">
            <div>
                <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500">Test</div>
                <div class="display-font text-sm font-extrabold text-slate-800 mt-0.5">{{ $testScore ?? '-' }}</div>
            </div>
            <div class="border-x border-slate-200">
                <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500">Exam</div>
                <div class="display-font text-sm font-extrabold text-slate-800 mt-0.5">{{ $examScore ?? '-' }}</div>
            </div>
            <div>
                <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500">Total</div>
                <div class="display-font text-sm font-extrabold text-blue-600 mt-0.5">{{ $totalScore }}</div>
            </div>
        </div>
    </div>

    <!-- Bottom Remark -->
    @if($remark)
        <div class="text-xs font-semibold text-slate-500 border-t border-slate-100 pt-3 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 shrink-0"></span>
            <span>Remark: <strong class="text-slate-700">{{ $remark }}</strong></span>
        </div>
    @endif
</div>
