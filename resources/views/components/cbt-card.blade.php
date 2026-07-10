@props(['title', 'subject', 'duration', 'questionsCount' => null, 'status' => 'Draft', 'actionUrl' => null, 'actionText' => 'Start Assessment', 'actions' => null])

<div {{ $attributes->merge(['class' => 'card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)] hover:border-[#fbbf24] hover:shadow-[0_16px_35px_rgba(15,23,42,0.14)] hover:-translate-y-0.5 transition-all duration-250 flex flex-col justify-between gap-5']) }}>
    <div class="space-y-4">
        <!-- Header status -->
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-icon-box icon="portal" color="purple" size="md" />
                <div>
                    <h3 class="display-font text-lg font-bold text-slate-900 leading-snug">
                        {{ $title }}
                    </h3>
                    <p class="text-xs font-bold text-slate-500 mt-0.5">
                        {{ $subject }}
                    </p>
                </div>
            </div>
            <x-status-badge :status="$status" class="shrink-0 scale-95" />
        </div>

        <!-- Rules Specs -->
        <div class="grid grid-cols-2 gap-3.5 bg-slate-50 p-3 rounded-[14px] border border-slate-100/55 text-center text-xs font-bold">
            <div>
                <div class="text-[9px] uppercase tracking-wider text-slate-500">Duration</div>
                <div class="display-font text-sm font-extrabold text-slate-800 mt-1">{{ $duration }} Mins</div>
            </div>
            @if($questionsCount !== null)
                <div class="border-l border-slate-200 pl-3">
                    <div class="text-[9px] uppercase tracking-wider text-slate-500">Questions</div>
                    <div class="display-font text-sm font-extrabold text-slate-800 mt-1">{{ $questionsCount }} Qs</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions block -->
    <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-4 mt-1">
        <div>
            @if($actionUrl)
                <x-action-button variant="primary" :href="$actionUrl">
                    {{ $actionText }}
                </x-action-button>
            @endif
        </div>
        
        @if($actions || isset($actionsSlot))
            <div class="flex items-center gap-2.5">
                {{ $actions ?? $actionsSlot }}
            </div>
        @endif
    </div>
</div>
