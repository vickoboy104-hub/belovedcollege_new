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

        <div
            x-data="{ open: false }"
            x-effect="document.body.classList.toggle('overflow-hidden', open)"
            x-on:keydown.escape.window="open = false"
        >
            <header
                class="public-topbar fixed inset-x-0 top-0 z-50 border-b"
                style="background-color: var(--theme-top-bar); border-color: rgba(255, 255, 255, 0.16);"
            >
                <div class="public-topbar-row mx-auto flex max-w-7xl items-center justify-start gap-3 px-4 sm:px-6 lg:px-8">
                    <button
                        @click="open = !open"
                        :class="{ 'is-open': open }"
                        :aria-expanded="open.toString()"
                        class="hamburger-button rounded-2xl border border-white/25 text-white md:hidden"
                        aria-label="Toggle public navigation menu"
                    >
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>

                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                        <x-application-logo class="h-10 w-10 shrink-0 sm:h-11 sm:w-11" />
                        <div class="nav-brand-copy">
                            <div class="nav-brand-title display-font text-sm font-bold" style="color: #ffffff;">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                            <div class="nav-brand-subtitle text-xs uppercase tracking-[0.32em]" style="color: rgba(255, 255, 255, 0.86);">{{ $schoolSettings['site_tagline'] ?? 'School Website + SMS + LMS' }}</div>
                        </div>
                    </a>

                    <nav class="public-desktop-nav hidden min-w-0 items-center gap-2 text-sm font-semibold md:flex" style="color: #ffffff;">
                        <a href="{{ route('about') }}" class="public-nav-pill transition">About</a>
                        <a href="{{ route('admissions') }}" class="public-nav-pill transition">Admissions</a>
                        <a href="{{ route('contact') }}" class="public-nav-pill transition">Contact</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="public-nav-pill is-strong transition">Dashboard</a>
                        @else
                            <a href="{{ route('student.login') }}" class="public-nav-pill transition">Student Login</a>
                            <a href="{{ route('staff.login') }}" class="public-nav-pill is-strong transition">Staff Login</a>
                        @endauth
                    </nav>
                </div>
            </header>

            <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-[80] md:hidden" style="display: none;">
                <button type="button" class="absolute inset-0 bg-slate-950/55" @click="open = false" aria-label="Close public navigation menu"></button>
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    class="drawer-panel public-drawer-panel relative h-full overflow-y-auto border-r px-4 py-5 shadow-2xl"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div class="min-w-0">
                            <div class="display-font text-sm font-bold text-slate-950">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                            <div class="mt-1 text-xs uppercase tracking-[0.28em] text-slate-500">{{ $schoolSettings['site_tagline'] ?? 'School Website + SMS + LMS' }}</div>
                        </div>
                        <button @click="open = false" class="hamburger-button is-open rounded-2xl border border-slate-200 text-slate-900" aria-label="Close public navigation menu">
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                        </button>
                    </div>

                    <div class="space-y-2">
                        <a href="{{ route('about') }}" @click="open = false" class="public-drawer-link drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold">About</a>
                        <a href="{{ route('admissions') }}" @click="open = false" class="public-drawer-link drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold">Admissions</a>
                        <a href="{{ route('contact') }}" @click="open = false" class="public-drawer-link drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold">Contact</a>
                        @auth
                            <a href="{{ route('dashboard') }}" @click="open = false" class="public-drawer-link is-primary drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold">Dashboard</a>
                        @else
                            <a href="{{ route('student.login') }}" @click="open = false" class="public-drawer-link drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold">Student Login</a>
                            <a href="{{ route('staff.login') }}" @click="open = false" class="public-drawer-link is-primary drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold">Staff Login</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <main class="public-content-shell">
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
