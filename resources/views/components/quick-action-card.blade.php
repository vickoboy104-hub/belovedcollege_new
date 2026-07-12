@props(['title', 'description', 'route' => null, 'href' => null, 'icon' => 'circle', 'tone' => 'blue'])

@php
    $resolvedUrl = $route ? route($route) : $href;
    
    // Dark-panel-friendly tones — use CSS variable-controlled gradients with white text
    $toneMap = [
        'blue'   => ['grad' => 'var(--theme-card-reg-student, linear-gradient(135deg,rgba(37,99,235,0.18) 0%,rgba(29,78,216,0.10) 100%))', 'border' => 'var(--theme-primary-soft, rgba(96,165,250,0.3))',  'iconBg' => 'var(--theme-primary-soft, rgba(96,165,250,0.2))',  'iconFg' => 'var(--theme-primary-light, #93c5fd)'],
        'green'  => ['grad' => 'var(--theme-card-add-parent,  linear-gradient(135deg,rgba(16,185,129,0.18) 0%,rgba(4,120,87,0.10) 100%))',  'border' => 'var(--theme-success-soft, rgba(52,211,153,0.3))',  'iconBg' => 'var(--theme-success-soft, rgba(52,211,153,0.2))',  'iconFg' => 'var(--theme-success-light, #6ee7b7)'],
        'purple' => ['grad' => 'linear-gradient(135deg,rgba(139,92,246,0.18) 0%,rgba(109,40,217,0.10) 100%)', 'border' => 'var(--theme-purple-soft, rgba(167,139,250,0.3))', 'iconBg' => 'var(--theme-purple-soft, rgba(167,139,250,0.2))', 'iconFg' => 'var(--theme-purple-light, #c4b5fd)'],
        'gold'   => ['grad' => 'linear-gradient(135deg,rgba(217,119,6,0.18)  0%,rgba(180,83,9,0.10) 100%)',  'border' => 'var(--theme-accent-soft, rgba(252,211,77,0.3))',  'iconBg' => 'var(--theme-accent-soft, rgba(252,211,77,0.2))',  'iconFg' => 'var(--theme-accent-light, #fcd34d)'],
        'orange' => ['grad' => 'linear-gradient(135deg,rgba(234,88,12,0.18)  0%,rgba(194,65,12,0.10) 100%)',  'border' => 'var(--theme-warning-soft, rgba(253,186,116,0.3))', 'iconBg' => 'var(--theme-warning-soft, rgba(253,186,116,0.2))', 'iconFg' => 'var(--theme-warning-light, #fdba74)'],
        'red'    => ['grad' => 'linear-gradient(135deg,rgba(220,38,38,0.18)  0%,rgba(185,28,28,0.10) 100%)',  'border' => 'var(--theme-danger-soft, rgba(252,165,165,0.3))', 'iconBg' => 'var(--theme-danger-soft, rgba(252,165,165,0.2))', 'iconFg' => 'var(--theme-danger-light, #fca5a5)'],
        'gray'   => ['grad' => 'linear-gradient(135deg,rgba(100,116,139,0.18) 0%,rgba(71,85,105,0.10) 100%)', 'border' => 'var(--theme-muted-soft, rgba(148,163,184,0.3))', 'iconBg' => 'var(--theme-muted-soft, rgba(148,163,184,0.2))', 'iconFg' => 'var(--theme-muted-light, #94a3b8)'],
    ];
    $tc = $toneMap[$tone] ?? $toneMap['blue'];
@endphp

<a href="{{ $resolvedUrl }}"
   data-tone="{{ $tone }}"
   {{ $attributes->merge(['class' => 'quick-action-card flex flex-col justify-between gap-5 rounded-[18px] p-5 transition-all duration-200 hover:-translate-y-0.5']) }}
   style="background: {{ $tc['grad'] }} !important; border: 1px solid {{ $tc['border'] }} !important; box-shadow: 0 8px 20px rgba(0,0,0,0.14);">

    <div class="space-y-4">
        {{-- Icon --}}
        <div class="quick-action-card-icon" style="width:2.4rem; height:2.4rem; border-radius:0.75rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:{{ $tc['iconBg'] }}; color:{{ $tc['iconFg'] }}; border:1px solid rgba(255,255,255,0.14);">
            <x-app-icon :name="$icon" class="h-5 w-5" />
        </div>

        <div class="space-y-1">
            <h3 class="display-font text-base font-extrabold tracking-tight leading-snug quick-action-card-title"
                style="color: var(--dashboard-quick-action-text, var(--dashboard-card-text, var(--theme-text-dark-card, #ffffff)));">
                {{ $title }}
            </h3>
            <p class="text-xs font-semibold leading-relaxed quick-action-card-desc"
               style="color: var(--dashboard-quick-action-text-muted, var(--dashboard-card-text-muted, rgba(255,255,255,0.72)));">
                {{ $description }}
            </p>
        </div>
    </div>

    {{-- Arrow pointer --}}
    <div class="text-right">
        <span class="text-xs font-bold inline-flex items-center gap-1 quick-action-card-link"
              style="color: {{ $tc['iconFg'] }};">
            <span>Explore</span>
            <span class="transform transition-transform duration-150">→</span>
        </span>
    </div>
</a>
