@props(['percentage' => null, 'value' => null, 'label' => null, 'color' => 'green', 'valueText' => null])

@php
    $pct = min(100, max(0, (float) ($percentage ?? $value ?? 0)));
@endphp

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if($label || $valueText || isset($slot))
        <div class="flex items-center justify-between gap-4 text-xs font-bold"
             style="color: var(--panel-title-color, var(--theme-text-main, #334155));">
            @if($label)
                <span class="truncate uppercase tracking-wider">{{ $label }}</span>
            @endif
            <span>{{ $valueText ?? $pct . '%' }}</span>
        </div>
    @endif
    <div class="portal-progress-track h-3.5 w-full rounded-full overflow-hidden p-0.5"
         style="background: var(--theme-progress-track, var(--theme-surface-soft, #e2e8f0)); border: 1px solid var(--theme-border-soft, rgba(0,0,0,0.1));">
        <div class="portal-progress-fill h-full rounded-full transition-all duration-500 ease-out"
             style="width: {{ $pct }}%; background: var(--theme-progress-bar, var(--theme-success, #10b981));">
        </div>
    </div>
</div>
