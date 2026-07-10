<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $schoolSettings['school_name'] ?? config('app.name', 'BELOVED SCHOOLS') }} | {{ $schoolSettings['site_subtitle'] ?? 'Secondary School Website' }}</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme-overrides')
    <link rel="stylesheet" href="{{ asset('portal-refresh.css') }}?v=20260706-overflow-fix">
</head>
<body class="antialiased">
    @php
        $publicNavItems = [
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'About', 'route' => 'about'],
            ['label' => 'Admissions', 'route' => 'admissions'],
            ['label' => 'Contact', 'route' => 'contact'],
        ];
        $publicAccessItems = auth()->check()
            ? [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'strong' => true],
            ]
            : [
                ['label' => 'Student Login', 'route' => 'student.login', 'strong' => false],
                ['label' => 'Staff Login', 'route' => 'staff.login', 'strong' => true],
            ];
    @endphp
    <div class="site-page-shell public-shell">
        @include('partials.site-background')

        <div
            x-data="{ open: false }"
            x-effect="document.body.classList.toggle('overflow-hidden', open)"
            x-on:keydown.escape.window="open = false"
        >
            <header class="classic-public-header fixed inset-x-0 top-0 z-50">
                <div class="classic-topbar">
                    <div class="classic-topbar__inner mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                        <div class="classic-topbar__contacts">
                            <a href="tel:{{ $schoolSettings['school_phone'] ?? '08067046701' }}">{{ $schoolSettings['school_phone'] ?? '08067046701' }}</a>
                            <span>{{ $schoolSettings['school_address'] ?? 'Ayeteju Street, Ore, Ondo State' }}</span>
                        </div>
                        <div class="classic-topbar__links">
                            @foreach ($publicAccessItems as $item)
                                <a href="{{ route($item['route']) }}" class="{{ $item['route'] === 'staff.login' ? 'public-staff-login-button' : '' }}">{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="classic-announce">
                    <div class="classic-announce__track">
                        <span>Admissions in progress for the current academic session</span>
                        <span>Knowledge, discipline, integrity, responsibility, and Godliness</span>
                        <span>Student and staff portals are open</span>
                        <span>Admissions in progress for the current academic session</span>
                        <span>Knowledge, discipline, integrity, responsibility, and Godliness</span>
                        <span>Student and staff portals are open</span>
                    </div>
                </div>

                <nav class="public-topbar border-b">
                    <div class="public-topbar-row mx-auto flex max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
                        <button
                            @click="open = !open"
                            :class="{ 'is-open': open }"
                            :aria-expanded="open.toString()"
                            class="hamburger-button rounded-xl border border-slate-200 text-slate-900 lg:hidden"
                            aria-label="Toggle public navigation menu"
                        >
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                        </button>

                        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                            <x-application-logo class="h-10 w-10 shrink-0 sm:h-11 sm:w-11" />
                            <div class="nav-brand-copy">
                                <div class="nav-brand-title display-font text-sm font-bold" style="color: var(--theme-text, #0f172a);">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                                <div class="nav-brand-subtitle text-xs uppercase tracking-[0.22em]" style="color: var(--theme-muted, #64748b);">{{ $schoolSettings['site_tagline'] ?? 'Building Minds, Shaping Character' }}</div>
                            </div>
                        </a>

                        <nav class="public-desktop-nav hidden min-w-0 items-center gap-1 lg:flex">
                            @foreach ($publicNavItems as $item)
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="public-nav-pill transition {{ request()->routeIs($item['route']) ? 'is-active' : '' }}"
                                    {{ request()->routeIs($item['route']) ? 'aria-current="page"' : '' }}
                                >
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </nav>

                        <div class="public-access-group ms-auto hidden items-center gap-2 lg:flex">
                            @foreach ($publicAccessItems as $item)
                                <a href="{{ route($item['route']) }}" class="public-nav-pill transition {{ !empty($item['strong']) ? 'is-strong' : '' }} {{ $item['route'] === 'staff.login' ? 'public-staff-login-button' : '' }}">{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                </nav>
            </header>

            <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-[80] lg:hidden" style="display: none;">
                <button type="button" class="absolute inset-0" style="background: rgba(0,0,0,0.55);" @click="open = false" aria-label="Close public navigation menu"></button>
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
                            <div class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-500">{{ $schoolSettings['site_tagline'] ?? 'Building Minds, Shaping Character' }}</div>
                        </div>
                        <button @click="open = false" class="hamburger-button is-open rounded-xl border border-slate-200 text-slate-900" aria-label="Close public navigation menu">
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach ($publicNavItems as $item)
                            <a href="{{ route($item['route']) }}" @click="open = false" class="public-drawer-link {{ request()->routeIs($item['route']) ? 'is-primary' : '' }} drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold">{{ $item['label'] }}</a>
                        @endforeach
                        @foreach ($publicAccessItems as $item)
                            <a href="{{ route($item['route']) }}" @click="open = false" class="public-drawer-link {{ !empty($item['strong']) ? 'is-primary' : '' }} {{ $item['route'] === 'staff.login' ? 'public-staff-login-button' : '' }} drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <main class="public-content-shell">
            @if (session('status'))
                <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="public-footer mt-20 border-t border-slate-200 bg-white/90">
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
