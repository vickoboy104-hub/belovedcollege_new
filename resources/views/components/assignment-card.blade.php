@props(['title', 'className', 'subjectName', 'dueDate', 'submissionsCount' => null, 'pendingMarkingCount' => null, 'status' => null, 'actions' => null])

<div {{ $attributes->merge(['class' => 'card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)] hover:border-[#fbbf24] hover:shadow-[0_16px_35px_rgba(15,23,42,0.14)] hover:-translate-y-0.5 transition-all duration-250 flex flex-col justify-between gap-5']) }}>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-icon-box icon="bills" color="green" size="md" />
                <div>
                    <h3 class="display-font text-lg font-bold text-slate-900 leading-snug">
                        {{ $title }}
                    </h3>
                    <p class="text-xs font-bold text-slate-500 mt-0.5">
                        {{ $className }} &bull; {{ $subjectName }}
                    </p>
                </div>
            </div>
            @if($status)
                <x-status-badge :status="$status" class="shrink-0" />
            @endif
        </div>

        <!-- Metrics Grid -->
        @if($submissionsCount !== null || $pendingMarkingCount !== null)
            <div class="grid grid-cols-2 gap-3.5 bg-slate-50 p-3 rounded-[14px] border border-slate-100">
                @if($submissionsCount !== null)
                    <div class="text-center sm:text-left">
                        <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Submissions</div>
                        <div class="display-font text-lg font-extrabold text-slate-800 mt-0.5">{{ $submissionsCount }}</div>
                    </div>
                @endif
                @if($pendingMarkingCount !== null)
                    <div class="text-center sm:text-left border-l border-slate-200 pl-3">
                        <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Pending Mark</div>
                        <div class="display-font text-lg font-extrabold text-amber-600 mt-0.5">{{ $pendingMarkingCount }}</div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Due Date -->
        <div class="flex items-center gap-2 text-xs font-bold text-rose-600 bg-rose-50/50 p-2.5 rounded-[12px] border border-rose-100/50">
            <x-app-icon name="circle" class="h-4 w-4 shrink-0" />
            <span>Due Date: <strong class="text-rose-700">{{ $dueDate }}</strong></span>
        </div>
    </div>

    <!-- Actions -->
    @if($actions || isset($actionsSlot))
        <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 pt-4 mt-1">
            {{ $actions ?? $actionsSlot }}
        </div>
    @endif
</div>
