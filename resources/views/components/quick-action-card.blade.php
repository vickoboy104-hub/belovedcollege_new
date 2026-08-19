@props(['title', 'description', 'route' => null, 'href' => null, 'icon' => 'circle', 'tone' => 'blue'])

@php
    $resolvedUrl = $route ? route($route) : $href;

    // Keep each action visually distinct while the overall card surface remains
    // controlled by the active admin theme (primary/surface/border/text settings).
    $toneMap = [
        'blue'   => ['accent' => 'var(--theme-primary)', 'soft' => 'var(--theme-primary-soft, rgba(37,99,235,0.18))'],
        'green'  => ['accent' => 'var(--theme-success)', 'soft' => 'var(--theme-success-soft, rgba(5,150,105,0.18))'],
        'purple' => ['accent' => 'var(--theme-purple, #7C3AED)', 'soft' => 'var(--theme-purple-soft, rgba(124,58,237,0.18))'],
        'gold'   => ['accent' => 'var(--theme-accent)', 'soft' => 'var(--theme-accent-soft, rgba(251,191,36,0.18))'],
        'orange' => ['accent' => 'var(--theme-warning)', 'soft' => 'var(--theme-warning-soft, rgba(217,119,6,0.18))'],
        'red'    => ['accent' => 'var(--theme-danger)', 'soft' => 'var(--theme-danger-soft, rgba(225,29,72,0.18))'],
        'gray'   => ['accent' => 'var(--theme-muted)', 'soft' => 'var(--theme-muted-soft, rgba(148,163,184,0.18))'],
    ];
    $tc = $toneMap[$tone] ?? $toneMap['blue'];
@endphp

<a href="{{ $resolvedUrl }}"
   aria-label="Open {{ $title }}"
   data-tone="{{ $tone }}"
   {{ $attributes->merge(['class' => 'quick-action-card group relative flex flex-col justify-between gap-5 overflow-hidden rounded-[18px] p-5 shadow-[0_12px_28px_rgba(15,23,42,0.12)] transition-all duration-200 hover:-translate-y-1 hover:shadow-[0_18px_38px_rgba(15,23,42,0.18)] hover:ring-2 hover:ring-[color:var(--theme-primary-soft)] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[color:var(--theme-primary-soft)]']) }}
   style="--qa-accent: {{ $tc['accent'] }}; --qa-accent-soft: {{ $tc['soft'] }}; background-color: var(--theme-card-soft) !important; background-image: linear-gradient(145deg, color-mix(in srgb, var(--theme-primary) 18%, var(--theme-card)) 0%, color-mix(in srgb, var(--theme-primary) 7%, var(--theme-card)) 100%) !important; border: 1.5px solid color-mix(in srgb, var(--theme-primary) 48%, var(--theme-border)) !important;">

    {{-- Strong top accent makes the entire surface read as an interactive control. --}}
    <span aria-hidden="true" class="absolute inset-x-0 top-0 h-1 transition-all duration-200 group-hover:h-1.5" style="background: var(--qa-accent);"></span>

    <div class="space-y-4">
        <div class="quick-action-card-icon"
             style="width:2.55rem; height:2.55rem; border-radius:0.8rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:color-mix(in srgb, var(--qa-accent) 16%, var(--theme-card)); color:var(--qa-accent); border:1.5px solid color-mix(in srgb, var(--qa-accent) 48%, var(--theme-border)); box-shadow:0 5px 14px color-mix(in srgb, var(--qa-accent) 13%, transparent);">
            <x-app-icon :name="$icon" class="h-5 w-5" />
        </div>

        <div class="space-y-1.5">
            <h3 class="display-font text-base font-extrabold tracking-tight leading-snug quick-action-card-title"
                style="color: var(--theme-text) !important;">
                {{ $title }}
            </h3>
            <p class="text-xs font-semibold leading-relaxed quick-action-card-desc"
               style="color: var(--theme-muted) !important;">
                {{ $description }}
            </p>
        </div>
    </div>

    <div class="flex justify-end">
        <span class="quick-action-card-link inline-flex min-h-8 items-center gap-1.5 rounded-lg px-3 py-1.5 text-[11px] font-extrabold tracking-wide transition-transform duration-150 group-hover:translate-x-0.5"
              style="background: var(--theme-button-bg); color: var(--theme-button-text); border: 1px solid color-mix(in srgb, var(--theme-button-bg) 72%, var(--theme-border)); box-shadow: 0 5px 12px color-mix(in srgb, var(--theme-button-bg) 18%, transparent);">
            <span>Explore</span>
            <span aria-hidden="true" class="transition-transform duration-150 group-hover:translate-x-0.5">→</span>
        </span>
    </div>
</a>