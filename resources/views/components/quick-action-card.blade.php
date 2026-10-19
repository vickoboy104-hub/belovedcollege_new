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
   {{ $attributes->merge(['class' => 'quick-action-card']) }}
   style="background: {{ $tc['grad'] }} !important; border: 1px solid {{ $tc['border'] }} !important; display: flex; align-items: center; gap: 0.75rem; min-height: 44px; padding: 0.7rem 0.85rem; border-radius: 14px; box-shadow: none; transition: transform 0.15s ease, border-color 0.15s ease; text-decoration: none;">

    <span class="quick-action-card-icon" style="width:1.85rem; height:1.85rem; border-radius:0.55rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:{{ $tc['iconBg'] }}; color:{{ $tc['iconFg'] }}; border:1px solid {{ $tc['border'] }};">
        <x-app-icon :name="$icon" class="h-4 w-4" />
    </span>

    <span style="min-width:0; flex:1 1 auto;">
        <span class="display-font quick-action-card-title"
              style="display:block; font-size:0.875rem; font-weight:800; line-height:1.25; letter-spacing:-0.01em; color: var(--dashboard-quick-action-text, var(--dashboard-card-text, var(--theme-text-dark-card, #ffffff)));">
            {{ $title }}
        </span>
        <span class="quick-action-card-desc"
              style="display:block; margin-top:0.1rem; font-size:0.75rem; font-weight:600; line-height:1.45; color: var(--dashboard-quick-action-text-muted, var(--dashboard-card-text-muted, rgba(255,255,255,0.72)));">
            {{ $description }}
        </span>
    </span>

    <span class="quick-action-card-link" aria-hidden="true"
          style="flex-shrink:0; font-size:0.875rem; font-weight:800; color: {{ $tc['iconFg'] }};">→</span>
</a>
