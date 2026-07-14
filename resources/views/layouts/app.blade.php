<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $schoolSettings['school_name'] ?? config('app.name', 'BELOVED SCHOOLS') }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/print.css'])
    @include('partials.theme-overrides')
    <link rel="stylesheet" href="{{ asset('portal-refresh.css') }}?v=20260706-overflow-fix">
    <link rel="stylesheet" href="{{ asset('ui-stability.css') }}?v=20260710-ui-audit-1">
    <link rel="stylesheet" href="{{ asset('table-usability.css') }}?v=20260711-sticky-actions-1">
    <link rel="stylesheet" href="{{ asset('interface-corrections.css') }}?v=20260712-dashboard-table-1">
    <link rel="stylesheet" href="{{ asset('mobile-interface.css') }}?v=20260713-mobile-audit-1">
    <link rel="stylesheet" href="{{ asset('mobile-interface-fixes.css') }}?v=20260713-mobile-audit-2">
    <link rel="stylesheet" href="{{ asset('student-actions-overlay.css') }}?v=20260713-student-actions-overlay-1">
    <link rel="stylesheet" href="{{ asset('report-search-controls.css') }}?v=20260714-report-search-1">
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
    @vite(['resources/js/print-settings.js'])
    <script src="{{ asset('sidebar-scroll-persistence.js') }}?v=20260711-sidebar-scroll-1"></script>
</body>
</html>