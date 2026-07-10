@props([
    'name',
    'className',
    'admissionNumber',
    'avatar' => null,
    'performance' => null,
    'feesBalance' => null,
    'attendance' => null,
    'actions' => null
])

<div {{ $attributes->merge(['class' => 'card bg-white border border-[#a2b4cd] rounded-[20px] p-6 shadow-[0_12px_30px_rgba(15,23,42,0.11)] hover:border-[#fbbf24] hover:shadow-[0_20px_45px_rgba(15,23,42,0.18)] hover:-translate-y-1 transition-all duration-250 flex flex-col justify-between gap-5']) }}>
    <!-- Upper Body details -->
    <div class="flex items-center gap-4 border-b border-slate-100 pb-4">
        <!-- Circular avatar initials -->
        <div class="shrink-0">
            @if($avatar)
                <img src="{{ $avatar }}" alt="{{ $name }}" class="w-14 h-14 rounded-full border-2 border-[#fbbf24] object-cover" />
            @else
                <div class="w-14 h-14 rounded-full border-2 border-[#fbbf24] bg-blue-50 text-blue-600 flex items-center justify-center font-extrabold text-lg">
                    {{ collect(explode(' ', $name))->map(fn($part) => substr($part, 0, 1))->take(2)->join('') }}
                </div>
            @endif
        </div>
        <div class="space-y-0.5">
            <h3 class="display-font text-base font-extrabold text-slate-900 tracking-tight leading-snug">
                {{ $name }}
            </h3>
            <p class="text-xs font-bold text-slate-500">
                {{ $className }} &bull; ID: <strong class="text-slate-800">{{ $admissionNumber }}</strong>
            </p>
        </div>
    </div>

    <!-- Summaries metrics -->
    <div class="grid grid-cols-3 gap-2.5 text-center text-xs font-bold text-slate-500 bg-slate-50 p-3 rounded-[14px] border border-slate-100/55">
        @if($performance !== null)
            <div>
                <div class="text-[9px] uppercase tracking-wider text-slate-400">Perf.</div>
                <div class="display-font text-sm font-extrabold text-blue-600 mt-1">{{ $performance }}%</div>
            </div>
        @endif
        
        @if($feesBalance !== null)
            <div class="border-x border-slate-200">
                <div class="text-[9px] uppercase tracking-wider text-slate-400">Fees Due</div>
                <div class="display-font text-sm font-extrabold text-rose-600 mt-1">NGN {{ number_format((float)$feesBalance, 0) }}</div>
            </div>
        @endif

        @if($attendance !== null)
            <div>
                <div class="text-[9px] uppercase tracking-wider text-slate-400">Attend.</div>
                <div class="display-font text-sm font-extrabold text-emerald-600 mt-1">{{ $attendance }}%</div>
            </div>
        @endif
    </div>

    <!-- lower CTA -->
    @if($actions || isset($actionsSlot))
        <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 pt-4 mt-1">
            {{ $actions ?? $actionsSlot }}
        </div>
    @endif
</div>
