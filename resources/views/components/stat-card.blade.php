@props(['label', 'value', 'icon' => null, 'accent' => 'blue', 'link' => null, 'linkText' => null])

@php
    // Map accent names to dashboard stat variables with gradient fallbacks and glow colours.
    $gradientMap = [
        'blue'   => ['var' => '--dashboard-stat-blue',   'fallback' => 'linear-gradient(135deg,#0284c7 0%,#0369a1 100%)', 'glow' => '2,132,199'],
        'green'  => ['var' => '--dashboard-stat-green',  'fallback' => 'linear-gradient(135deg,#059669 0%,#047857 100%)', 'glow' => '5,150,105'],
        'purple' => ['var' => '--dashboard-stat-purple', 'fallback' => 'linear-gradient(135deg,#7c3aed 0%,#6d28d9 100%)', 'glow' => '124,58,237'],
        'orange' => ['var' => '--dashboard-stat-orange', 'fallback' => 'linear-gradient(135deg,#ea580c 0%,#c2410c 100%)', 'glow' => '234,88,12'],
        'gold'   => ['var' => '--dashboard-stat-orange', 'fallback' => 'linear-gradient(135deg,#d97706 0%,#b45309 100%)', 'glow' => '217,119,6'],
        'amber'  => ['var' => '--dashboard-stat-orange', 'fallback' => 'linear-gradient(135deg,#d97706 0%,#b45309 100%)', 'glow' => '217,119,6'],
        'red'    => ['var' => '--dashboard-stat-rose',   'fallback' => 'linear-gradient(135deg,#ea580c 0%,#c2410c 100%)', 'glow' => '220,38,38'],
        'rose'   => ['var' => '--dashboard-stat-rose',   'fallback' => 'linear-gradient(135deg,#be185d 0%,#9d174d 100%)', 'glow' => '190,24,93'],
        'teal'   => ['var' => '--dashboard-stat-green',  'fallback' => 'linear-gradient(135deg,#0d9488 0%,#0f766e 100%)', 'glow' => '13,148,136'],
        'gray'   => ['var' => '--dashboard-stat-blue',   'fallback' => 'linear-gradient(135deg,#64748b 0%,#475569 100%)', 'glow' => '100,116,139'],
    ];
    $accent_data = $gradientMap[$accent] ?? $gradientMap['blue'];
    $cssVar      = $accent_data['var'];
    $fallback    = $accent_data['fallback'];
    $gradient    = "var({$cssVar}, {$fallback})";
    $glowRgb     = $accent_data['glow'];
@endphp

<div {{ $attributes->merge(['class' => 'metric-card stat-gradient-card']) }}
     style="background: {{ $gradient }} !important; border: 1px solid var(--theme-border-soft, rgba(255,255,255,0.14)) !important; border-radius: var(--radius-xl,1rem); padding: 1.25rem 1.4rem; color: var(--dashboard-stat-text, #ffffff); position: relative; overflow: hidden; box-shadow: 0 10px 28px rgba({{ $glowRgb }},0.25), 0 2px 8px rgba(0,0,0,0.12); transition: transform 0.2s ease, box-shadow 0.2s ease; min-width: 0;">

    {{-- Decorative shimmer blob --}}
    <div style="position:absolute; top:-24px; right:-20px; width:110px; height:110px; border-radius:50%; background:rgba(255,255,255,0.07); pointer-events:none;"></div>
    <div style="position:absolute; bottom:-32px; left:-18px; width:80px; height:80px; border-radius:50%; background:rgba(255,255,255,0.04); pointer-events:none;"></div>

    <div style="position:relative; z-index:1; min-width:0;">
        <div class="flex items-start justify-between gap-4">
            <span class="metric-label" style="color: var(--dashboard-stat-text-muted, rgba(255,255,255,0.88)); font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.07em; overflow-wrap:anywhere;">
                {{ $label }}
            </span>
            @if($icon)
                <div class="metric-icon" style="background: var(--dashboard-stat-icon-bg, rgba(255,255,255,0.14)); color: var(--dashboard-stat-text, #ffffff); border:1px solid rgba(255,255,255,0.22); border-radius:0.75rem; width:2.2rem; height:2.2rem; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <x-app-icon :name="$icon" />
                </div>
            @endif
        </div>
        <div class="metric-value" style="color: var(--dashboard-stat-text, #ffffff); font-size:1.85rem; font-weight:900; line-height:1.2; margin-top:0.5rem; letter-spacing:-0.02em;">
            {{ $value }}
        </div>

        @if(isset($slot) && $slot->isNotEmpty())
            <div class="metric-note" style="color: var(--dashboard-stat-text-muted, rgba(255,255,255,0.78)); font-size:0.75rem; font-weight:600; margin-top:0.3rem;">
                {{ $slot }}
            </div>
        @endif
    </div>

    @if($link)
        <div class="metric-link-row" style="border-top:1px solid rgba(255,255,255,0.18); margin-top:0.85rem; padding-top:0.7rem; position:relative; z-index:1;">
            <a href="{{ $link }}" class="metric-link group" style="color: var(--dashboard-stat-text, #ffffff); font-size:0.8rem; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:0.35rem;">
                <span>{{ $linkText ?? 'Manage ' . strtolower($label) }}</span>
                <span class="metric-link-arrow" style="transition:transform 0.15s ease;">→</span>
            </a>
        </div>
    @endif
</div>
