<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $schoolSettings['school_name'] ?? config('app.name', 'BELOVED SCHOOLS') }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme-overrides')
    <link rel="stylesheet" href="{{ asset('portal-refresh.css') }}?v=20260706-overflow-fix">
    <link rel="stylesheet" href="{{ asset('ui-stability.css') }}?v=20260710-ui-audit-1">
</head>
<body class="antialiased">
    @php
        $authImage = collect([
            $schoolSettings['hero_slide_1_image'] ?? null,
            $schoolSettings['hero_intro_background_image'] ?? null,
            $schoolSettings['section_background_1'] ?? null,
        ])->filter()->first();
        $authShellStyle = $authImage ? "style=\"--auth-bg-image: url('" . asset($authImage) . "');\"" : '';
    @endphp
    <div class="auth-shell" {!! $authShellStyle !!}>
        <div class="auth-layout">
            <section class="auth-copy-panel">
                <a href="{{ route('home') }}" class="auth-brand">
                    <x-application-logo class="h-12 w-12 shrink-0" />
                    <span>
                        <span class="display-font block text-sm font-bold" style="color: var(--theme-text-dark-card, #ffffff);">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</span>
                        <span class="mt-1 block text-xs uppercase tracking-[0.18em]" style="color: rgba(255,255,255,0.68);">{{ $schoolSettings['site_tagline'] ?? 'Building Minds, Shaping Character' }}</span>
                    </span>
                </a>

                <div class="auth-copy-body">
                    <p class="auth-kicker">School Portal</p>
                    <h1 class="display-font mt-4 text-4xl font-bold leading-tight" style="color: var(--theme-text-dark-card, #ffffff);">A secure workspace for learning, teaching, and school records.</h1>
                    <p class="mt-5 max-w-xl text-sm leading-7" style="color: rgba(255,255,255,0.74);">Students and staff can sign in to continue from the right portal. Guest visitors can return to the school website for admissions, contact, and public information.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('home') }}" class="auth-copy-link">School Website</a>
                        <a href="{{ route('contact') }}" class="auth-copy-link">Contact Office</a>
                    </div>
                </div>
            </section>

            <div class="auth-form-panel">
                <div class="auth-mobile-brand">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <x-application-logo class="h-10 w-10 shrink-0" />
                        <span>
                            <span class="display-font block text-sm font-bold" style="color: var(--theme-text, #0f172a);">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</span>
                            <span class="block text-xs" style="color: var(--theme-muted, #64748b);">{{ $schoolSettings['site_tagline'] ?? 'Building Minds, Shaping Character' }}</span>
                        </span>
                    </a>
                </div>

                <div class="auth-card">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
