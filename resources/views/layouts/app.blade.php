<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $schoolSettings['school_name'] ?? config('app.name', 'BELOVED SCHOOLS') }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="site-page-shell min-h-screen">
        @include('partials.admin-background')

        @include('layouts.navigation')

        @isset($header)
            <header class="mx-auto mt-4 max-w-7xl px-4 sm:mt-6 sm:px-6 lg:px-8">
                <div class="mesh-card app-header-card">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-8 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
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
</body>
</html>
