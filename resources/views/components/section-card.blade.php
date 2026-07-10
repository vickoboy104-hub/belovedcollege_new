@props(['title' => null, 'subtitle' => null, 'icon' => null, 'accent' => 'blue', 'tone' => null])

@php
    // tone overrides accent for backwards-compat
    $effectiveTone = $tone ?? $accent;

    // Whether this is a "dark panel" (has class dashboard-dark-panel or similar)
    // The component can be used on light bg too, so we keep icon styling flexible
    $toneIconMap = [
        'blue'   => ['bg' => 'var(--theme-primary-soft, rgba(56,189,248,0.18))',   'color' => 'var(--theme-primary-light, #38bdf8)'],
        'green'  => ['bg' => 'var(--theme-success-soft, rgba(16,185,129,0.18))',   'color' => 'var(--theme-success-light, #34d399)'],
        'purple' => ['bg' => 'var(--theme-purple-soft, rgba(139,92,246,0.18))',   'color' => 'var(--theme-purple-light, #c4b5fd)'],
        'orange' => ['bg' => 'var(--theme-warning-soft, rgba(251,146,60,0.18))',   'color' => 'var(--theme-warning-light, #fb923c)'],
        'gold'   => ['bg' => 'var(--theme-accent-soft, rgba(252,211,77,0.18))',   'color' => 'var(--theme-accent-light, #fcd34d)'],
        'red'    => ['bg' => 'var(--theme-danger-soft, rgba(248,113,113,0.18))',  'color' => 'var(--theme-danger-light, #fca5a5)'],
        'gray'   => ['bg' => 'var(--theme-muted-soft, rgba(148,163,184,0.18))',  'color' => 'var(--theme-muted-light, #94a3b8)'],
    ];
    $iconStyle = $toneIconMap[$effectiveTone] ?? $toneIconMap['blue'];
@endphp

<div {{ $attributes->merge(['class' => 'admin-panel section-card-panel p-6 transition-all duration-200']) }}>
    @if($title || isset($header) || $icon)
        <div class="section-header pb-5 mb-6 flex items-center justify-between gap-4"
             style="border-bottom: 1px solid rgba(255,255,255,0.1);">
            <div class="flex items-center gap-3.5 min-w-0">
                @if($icon)
                    <div class="icon-box w-11 h-11 rounded-[14px] flex items-center justify-center shadow-md shrink-0"
                         style="background: {{ $iconStyle['bg'] }}; color: {{ $iconStyle['color'] }}; border: 1px solid rgba(255,255,255,0.14);">
                        <x-app-icon :name="$icon" class="h-6 w-6" />
                    </div>
                @endif
                <div class="min-w-0">
                    @if($title)
                        <h2 class="section-title" style="color: var(--panel-title-color, inherit);">
                            {{ $title }}
                        </h2>
                    @endif
                    @if($subtitle)
                        <p class="section-description" style="color: var(--panel-desc-color, inherit);">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
            </div>
            @if(isset($actions))
                <div class="flex items-center gap-2.5 shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div style="color: var(--panel-content-color, var(--text-main, #334155));">
        {{ $slot }}
    </div>
</div>
