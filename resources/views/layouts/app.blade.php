@php
    $storedThemePreset = $schoolSettings['theme_preset'] ?? 'light-corporate';
    $activeThemePreset = match ($storedThemePreset) {
        'dark-corporate', 'midnight-cyber' => 'dark-corporate',
        'colourful-professional' => 'colourful-professional',
        'custom' => 'custom',
        default => 'light-corporate',
    };
    $themeDocumentClass = 'theme-'.$activeThemePreset;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $themeDocumentClass }}" data-theme-preset="{{ $activeThemePreset }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (request()->routeIs('teacher.*'))
        <meta name="teacher-access-map-url" content="{{ route('teacher.access-map') }}">
    @endif
    <title>{{ $schoolSettings['school_name'] ?? config('app.name', 'BELOVED SCHOOLS') }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/print.css'])
    @include('partials.theme-overrides')
    <link rel="stylesheet" href="{{ asset('portal-refresh.css') }}?v=20260706-overflow-fix">
    <link rel="stylesheet" href="{{ asset('ui-stability.css') }}?v=20260710-ui-audit-1">
    <link rel="stylesheet" href="{{ asset('table-usability.css') }}?v=20260714-sticky-actions-2">
    <link rel="stylesheet" href="{{ asset('interface-corrections.css') }}?v=20260712-dashboard-table-1">
    <link rel="stylesheet" href="{{ asset('mobile-interface.css') }}?v=20260713-mobile-audit-1">
    <link rel="stylesheet" href="{{ asset('mobile-interface-fixes.css') }}?v=20260713-mobile-audit-2">
    <link rel="stylesheet" href="{{ asset('student-actions-overlay.css') }}?v=20260713-student-actions-overlay-1">
    <link rel="stylesheet" href="{{ asset('report-search-controls.css') }}?v=20260714-report-search-2">
    <link rel="stylesheet" href="{{ asset('theme-variants.css') }}?v=20260714-theme-variants-1">
    <link rel="stylesheet" href="{{ asset('theme-settings-presets.css') }}?v=20260714-theme-settings-presets-1">
</head>
@php
    $routeCssClass = 'route-'.str_replace('.', '-', request()->route()?->getName() ?? 'unknown');
@endphp
<body class="antialiased {{ $routeCssClass }}">
    <div class="site-page-shell app-shell min-h-screen">
        @include('partials.admin-background')

        @include('layouts.navigation')

        <div class="app-content-shell">
            @isset($header)
                <header class="app-content-inner mx-auto mt-3 w-full px-3 sm:mt-4 sm:px-4 lg:px-6">
                    <div class="mesh-card app-header-card">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="app-content-inner app-main mx-auto w-full px-3 py-4 sm:px-4 sm:py-4 lg:px-6">
                @if (request()->routeIs('admin.students.show'))
                    <span class="sr-only">Edit full student profile</span>
                @endif

                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ session('status') }}
                    </div>
                @endif

                @if (isset($errors) && $errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                        <div class="font-semibold">Please review the form.</div>
                        <ul class="mt-2 list-disc ps-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
    @isset($paymentGatewayCatalog)
        <script type="application/json" id="payment-gateway-catalog">{!! json_encode($paymentGatewayCatalog, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endisset
    @vite(['resources/js/print-settings.js'])
    <script src="{{ asset('sidebar-scroll-persistence.js') }}?v=20260711-sidebar-scroll-1"></script>
    @if (auth()->user()?->hasAnyRole(['admin', 'principal']))
        <script
            src="{{ asset('admin-navigation-shortcuts.js') }}?v=20260714-admin-shortcuts-1"
            data-teacher-access-url="{{ route('admin.teacher-access.index') }}"
            data-payment-gateways-url="{{ route('admin.payment-gateways.index') }}"
        ></script>
    @endif
    @if (request()->routeIs('teacher.*'))
        <script src="{{ asset('teacher-access-filter.js') }}?v=20260714-teacher-access-1"></script>
    @endif
    @isset($paymentGatewayCatalog)
        <script src="{{ asset('payment-gateway-buttons.js') }}?v=20260714-payment-gateways-1"></script>
    @endisset
</body>
</html>
