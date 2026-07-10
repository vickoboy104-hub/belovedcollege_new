@props(['title' => null, 'subtitle' => null, 'icon' => null, 'accent' => 'blue'])

@php
    $toneIconMap = [
        'blue'   => ['bg' => 'rgba(56,189,248,0.18)',  'color' => '#38bdf8'],
        'green'  => ['bg' => 'rgba(16,185,129,0.18)',  'color' => '#34d399'],
        'purple' => ['bg' => 'rgba(139,92,246,0.18)',  'color' => '#c4b5fd'],
        'orange' => ['bg' => 'rgba(251,146,60,0.18)',  'color' => '#fb923c'],
        'gold'   => ['bg' => 'rgba(252,211,77,0.18)',  'color' => '#fcd34d'],
        'red'    => ['bg' => 'rgba(248,113,113,0.18)', 'color' => '#fca5a5'],
        'gray'   => ['bg' => 'rgba(148,163,184,0.18)', 'color' => '#94a3b8'],
    ];
    $iconStyle = $toneIconMap[$accent] ?? $toneIconMap['blue'];
@endphp

<div {{ $attributes->merge(['class' => 'admin-panel section-card-panel p-6 transition-all duration-200']) }}>
    @if($title || isset($header) || $icon)
        <div class="section-header pb-4 mb-5 flex items-center justify-between gap-4"
             style="border-bottom: 1px solid rgba(255,255,255,0.1);">
            <div class="flex items-center gap-3 min-w-0">
                @if($icon)
                    <div class="icon-box w-10 h-10 rounded-[12px] flex items-center justify-center shadow-sm shrink-0"
                         style="background: {{ $iconStyle['bg'] }}; color: {{ $iconStyle['color'] }}; border: 1px solid rgba(255,255,255,0.14);">
                        <x-app-icon :name="$icon" class="h-5 w-5" />
                    </div>
                @endif
                <div class="min-w-0">
                    @if($title)
                        <h3 class="section-title" style="color: var(--panel-title-color, inherit);">
                            {{ $title }}
                        </h3>
                    @endif
                    @if($subtitle)
                        <p class="section-description" style="color: var(--panel-desc-color, inherit);">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
            </div>
            @if(isset($actions))
                <div class="flex items-center gap-2 shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="text-sm" style="color: var(--panel-content-color, var(--text-main, #475569));">
        {{ $slot }}
    </div>
</div>
