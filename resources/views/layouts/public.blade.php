<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $schoolSettings['school_name'] ?? config('app.name', 'BELOVED SCHOOLS') }} | {{ $schoolSettings['site_subtitle'] ?? 'Secondary School Website' }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="site-page-shell">
        @include('partials.site-background')

        <header
            x-data="{ open: false }"
            x-effect="document.body.classList.toggle('overflow-hidden', open)"
            class="sticky top-0 z-40 border-b"
            style="background-color: var(--theme-top-bar); border-color: rgba(255, 255, 255, 0.16);"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <x-application-logo class="h-11 w-11" />
                    <div>
                        <div class="display-font text-sm font-bold" style="color: #ffffff;">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                        <div class="text-xs uppercase tracking-[0.32em]" style="color: rgba(255, 255, 255, 0.86);">{{ $schoolSettings['site_tagline'] ?? 'School Website + SMS + LMS' }}</div>
                    </div>
                </a>
                <nav class="hidden items-center gap-6 text-sm font-semibold md:flex" style="color: #ffffff;">
                    <a href="{{ route('about') }}" class="transition" style="color: #ffffff;">About</a>
                    <a href="{{ route('admissions') }}" class="transition" style="color: #ffffff;">Admissions</a>
                    <a href="{{ route('contact') }}" class="transition" style="color: #ffffff;">Contact</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full border px-5 py-2.5 text-sm font-semibold shadow-lg transition" style="border-color: rgba(255, 255, 255, 0.42); background-color: rgba(255, 255, 255, 0.12); color: #ffffff;">Dashboard</a>
                    @else
                        <a href="{{ route('student.login') }}" class="rounded-full border px-5 py-2.5 text-sm font-semibold transition" style="border-color: rgba(255, 255, 255, 0.42); background-color: rgba(255, 255, 255, 0.08); color: #ffffff;">Student Login</a>
                        <a href="{{ route('staff.login') }}" class="rounded-full border px-5 py-2.5 text-sm font-semibold shadow-lg transition" style="border-color: rgba(255, 255, 255, 0.42); background-color: rgba(255, 255, 255, 0.16); color: #ffffff;">Staff Login</a>
                    @endauth
                </nav>
                <button @click="open = !open" class="inline-flex rounded-full border border-white/25 p-3 text-white md:hidden" aria-label="Toggle public navigation menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
            <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 md:hidden" style="display: none;">
                <button type="button" class="absolute inset-0 bg-slate-950/55" @click="open = false" aria-label="Close public navigation menu"></button>
                <div class="drawer-panel relative ml-auto h-full overflow-y-auto border-l px-4 py-5 shadow-2xl" style="background-color: var(--theme-top-bar); border-color: rgba(255, 255, 255, 0.16);">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <div class="display-font text-sm font-bold text-white">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                            <div class="mt-1 text-xs uppercase tracking-[0.28em] text-white/75">{{ $schoolSettings['site_tagline'] ?? 'School Website + SMS + LMS' }}</div>
                        </div>
                        <button @click="open = false" class="rounded-full border border-white/20 p-3 text-white" aria-label="Close public navigation menu">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-2">
                        <a href="{{ route('about') }}" @click="open = false" class="drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-white/10" style="color: #ffffff;">About</a>
                        <a href="{{ route('admissions') }}" @click="open = false" class="drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-white/10" style="color: #ffffff;">Admissions</a>
                        <a href="{{ route('contact') }}" @click="open = false" class="drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-white/10" style="color: #ffffff;">Contact</a>
                        @auth
                            <a href="{{ route('dashboard') }}" @click="open = false" class="drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold" style="background-color: rgba(255, 255, 255, 0.14); color: #ffffff;">Dashboard</a>
                        @else
                            <a href="{{ route('student.login') }}" @click="open = false" class="drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-white/10" style="color: #ffffff;">Student Login</a>
                            <a href="{{ route('staff.login') }}" @click="open = false" class="drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold" style="background-color: rgba(255, 255, 255, 0.14); color: #ffffff;">Staff Login</a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <main>
            @if (session('status'))
                <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="mt-20 border-t border-slate-200 bg-white/80">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-10 text-sm text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div class="space-y-1">
                    <p class="font-semibold text-slate-800">Location: {{ $schoolSettings['school_address'] ?? 'Ayeteju Street, Ore, Ondo State' }}</p>
                    <p class="font-semibold text-slate-800">Phone: {{ $schoolSettings['school_phone'] ?? '08067046701' }}</p>
                    <p class="font-semibold text-slate-800">Established: 2006</p>
                </div>
                <p>&copy; {{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}. Raising disciplined and educated citizens for the future.</p>
            </div>
        </footer>
    </div>
</body>
</html>
