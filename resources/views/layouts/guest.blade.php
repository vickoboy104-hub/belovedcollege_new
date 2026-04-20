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
    <div class="relative flex min-h-screen items-center justify-center px-4 py-10">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(15,118,110,0.16),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(245,158,11,0.16),_transparent_24%)]"></div>
        <div class="relative w-full max-w-md">
            <div class="mb-6 flex justify-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 rounded-full bg-white/80 px-4 py-3 shadow-lg shadow-slate-900/5 backdrop-blur">
                    <x-application-logo class="h-10 w-10" />
                    <div>
                        <div class="display-font text-sm font-bold text-slate-900">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                        <div class="text-xs text-slate-500">{{ $schoolSettings['site_tagline'] ?? 'Secondary school digital campus' }}</div>
                    </div>
                </a>
            </div>

            <div class="mesh-card px-6 py-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
