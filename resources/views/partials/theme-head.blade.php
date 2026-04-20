@php
    $themePresets = [
        'classic-blue' => [
            'primary' => '#174ea6',
            'secondary' => '#0f3d91',
            'accent' => '#0f766e',
            'highlight' => '#f59e0b',
            'text' => '#0f172a',
        ],
        'navy-gold' => [
            'primary' => '#14213d',
            'secondary' => '#1d3557',
            'accent' => '#457b9d',
            'highlight' => '#f4b400',
            'text' => '#0f172a',
        ],
        'royal-cyan' => [
            'primary' => '#1d4ed8',
            'secondary' => '#1e40af',
            'accent' => '#0891b2',
            'highlight' => '#22c55e',
            'text' => '#0f172a',
        ],
    ];

    $selectedPreset = $schoolSettings['theme_preset'] ?? 'classic-blue';
    $preset = $themePresets[$selectedPreset] ?? $themePresets['classic-blue'];
    $theme = [
        'primary' => $schoolSettings['theme_primary'] ?? $preset['primary'],
        'secondary' => $schoolSettings['theme_secondary'] ?? $preset['secondary'],
        'accent' => $schoolSettings['theme_accent'] ?? $preset['accent'],
        'highlight' => $schoolSettings['theme_highlight'] ?? $preset['highlight'],
        'text' => $schoolSettings['theme_text'] ?? $preset['text'],
    ];
    $topBarColor = $schoolSettings['top_bar_color'] ?? '#0b2a66';
@endphp
@if (! empty($schoolSettings['favicon_path']))
    <link rel="icon" type="image/png" href="{{ asset($schoolSettings['favicon_path']) }}">
@endif
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700,800&display=swap" rel="stylesheet" />
<style>
    :root {
        --theme-primary: {{ $theme['primary'] }};
        --theme-secondary: {{ $theme['secondary'] }};
        --theme-accent: {{ $theme['accent'] }};
        --theme-highlight: {{ $theme['highlight'] }};
        --theme-text: {{ $theme['text'] }};
        --theme-top-bar: {{ $topBarColor }};
        --theme-surface: #f8fbff;
        --theme-soft: rgba(23, 78, 166, 0.08);
    }

    html,
    body,
    button,
    input,
    select,
    textarea {
        font-family: 'Montserrat', sans-serif !important;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    .display-font {
        font-family: 'Montserrat', sans-serif !important;
    }

    body {
        min-height: 100%;
        color: var(--theme-text);
        background: #edf3fb;
    }

    .site-page-shell {
        position: relative;
        min-height: 100vh;
        isolation: isolate;
    }

    .site-background-stack {
        position: absolute;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        overflow: hidden;
    }

    .site-background-band {
        position: absolute;
        left: 0;
        right: 0;
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
    }

    .site-background-band.is-top {
        top: 0;
        height: 100svh;
    }

    .site-background-band.is-middle {
        top: 100svh;
        height: 100svh;
    }

    .site-background-band.is-bottom {
        top: 200svh;
        bottom: 0;
        min-height: 100svh;
    }

    .hero-intro-card {
        position: relative;
        overflow: hidden;
    }

    .hero-intro-card > * {
        position: relative;
        z-index: 1;
    }

    .hero-intro-card-has-image {
        background-image: var(--hero-intro-bg-image);
        background-size: cover;
        background-position: center;
        border-color: rgba(255, 255, 255, 0.18);
        box-shadow: 0 30px 70px rgba(15, 23, 42, 0.22);
    }

    .hero-intro-card-has-image::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            linear-gradient(
                108deg,
                rgba(2, 6, 23, var(--hero-intro-overlay-strong, 0.46)) 0%,
                rgba(2, 6, 23, var(--hero-intro-overlay-base, 0.22)) 54%,
                rgba(15, 23, 42, var(--hero-intro-overlay-soft, 0.12)) 100%
            ),
            radial-gradient(circle at top right, color-mix(in srgb, var(--theme-highlight) 18%, transparent), transparent 34%);
    }

    .hero-intro-card-has-image h1,
    .hero-intro-card-has-image p {
        color: #fff !important;
        text-shadow: 0 4px 18px rgba(2, 6, 23, 0.42);
    }

    .hero-intro-card-has-image .brand-text {
        background-image: none !important;
        color: rgba(255, 255, 255, 0.96) !important;
    }

    .hero-intro-card-has-image .theme-button {
        box-shadow: 0 18px 34px rgba(2, 6, 23, 0.28);
    }

    .hero-intro-card-has-image .theme-button-secondary {
        border-color: rgba(255, 255, 255, 0.68) !important;
        background: rgba(255, 255, 255, 0.14) !important;
        color: #fff !important;
        backdrop-filter: blur(8px);
    }

    .hero-intro-card-has-image .theme-button-secondary:hover {
        background: rgba(255, 255, 255, 0.22) !important;
    }

    .content-media-card {
        position: relative;
        overflow: hidden;
        background-image: var(--content-card-bg);
        background-size: cover;
        background-position: center;
        color: #fff !important;
        text-shadow: 0 4px 18px rgba(2, 6, 23, 0.36);
        border-color: rgba(255, 255, 255, 0.16) !important;
        box-shadow: 0 24px 55px rgba(15, 23, 42, 0.18);
    }

    .content-media-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            linear-gradient(135deg, rgba(2, 6, 23, 0.72), rgba(2, 6, 23, 0.26) 48%, rgba(15, 23, 42, 0.55)),
            radial-gradient(circle at top right, color-mix(in srgb, var(--theme-highlight) 18%, transparent), transparent 34%);
    }

    .content-media-card > * {
        position: relative;
        z-index: 1;
    }

    .content-media-card h1,
    .content-media-card h2,
    .content-media-card h3,
    .content-media-card p,
    .content-media-card .display-font,
    .content-media-card .section-kicker,
    .content-media-card .text-slate-500,
    .content-media-card .text-slate-600,
    .content-media-card .text-slate-700,
    .content-media-card .text-slate-900 {
        color: #fff !important;
        text-shadow: 0 4px 18px rgba(2, 6, 23, 0.36);
    }

    .content-media-card .theme-button-secondary {
        border-color: rgba(255, 255, 255, 0.62) !important;
        background: rgba(255, 255, 255, 0.12) !important;
        color: #fff !important;
    }
</style>
