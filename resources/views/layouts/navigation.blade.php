@php
    $user = auth()->user();
    $roleLabel = $user?->roleLabel() ?? 'Portal';
    $navGroups = [
        [
            'label' => 'Workspace',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'DB'],
            ],
        ],
    ];

    if ($user?->hasAnyRole(['admin', 'principal'])) {
        $navGroups[] = [
            'label' => 'Administration',
            'items' => [
                ['label' => 'People Hub', 'route' => 'admin.people', 'active' => 'admin.people', 'icon' => 'PH'],
                ['label' => 'School Management', 'route' => 'admin.academics', 'active' => 'admin.academics*', 'icon' => 'SM'],
                ['label' => 'Student Management', 'route' => 'admin.students.index', 'active' => 'admin.students.*', 'icon' => 'ST'],
                ['label' => 'Parents Management', 'route' => 'admin.parents.index', 'active' => 'admin.parents.*', 'icon' => 'PA'],
                ['label' => 'Staff Management', 'route' => 'admin.staff.index', 'active' => 'admin.staff.*', 'icon' => 'SF'],
                ['label' => 'Reports', 'route' => 'admin.reports.index', 'active' => 'admin.reports*', 'icon' => 'RP'],
                ['label' => 'Settings', 'route' => 'admin.settings', 'active' => 'admin.settings*', 'icon' => 'PF'],
            ],
        ];
    }

    if ($user?->hasAnyRole(['admin', 'principal', 'accountant'])) {
        $navGroups[] = [
            'label' => 'Finance',
            'items' => [
                ['label' => 'Bills & Payment', 'route' => 'admin.finance', 'active' => 'admin.finance', 'icon' => 'BP'],
                ['label' => 'Finance Records', 'route' => 'admin.finance.records', 'active' => 'admin.finance.records|admin.finance.printable-fee-list', 'icon' => 'FR'],
            ],
        ];
    }

    if ($user?->hasAnyRole(['admin', 'principal', 'teacher'])) {
        $navGroups[] = [
            'label' => 'Teaching',
            'items' => [
                ['label' => 'Learning Workspace', 'route' => 'teacher.learning', 'active' => 'teacher.learning|teacher.cbt.*', 'icon' => 'TW'],
            ],
        ];
    }

    if ($user?->hasAnyRole(['student', 'parent'])) {
        $navGroups[] = [
            'label' => 'Student',
            'items' => [
                ['label' => 'Student Portal', 'route' => 'portal.index', 'active' => 'portal.index|portal.cbt.*|portal.results.*|portal.record', 'icon' => 'SP'],
            ],
        ];
    }

    $accountLinks = [
        ['label' => 'Profile', 'route' => 'profile.edit', 'active' => 'profile.*', 'icon' => 'AC'],
    ];
@endphp

<div
    x-data="{ open: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    x-on:keydown.escape.window="open = false"
>
    <nav
        class="app-topbar fixed inset-x-0 top-0 z-50 border-b"
        style="background-color: var(--theme-top-bar); border-color: rgba(255, 255, 255, 0.16);"
    >
        <div class="app-topbar-row flex items-center justify-between gap-3 px-3 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <button
                    type="button"
                    @click="open = !open"
                    :class="{ 'is-open': open }"
                    :aria-expanded="open.toString()"
                    class="hamburger-button rounded-2xl border border-white/25 text-white shadow-sm lg:hidden"
                    aria-label="Toggle sidebar navigation"
                >
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>

                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <x-application-logo class="h-10 w-10 shrink-0 sm:h-11 sm:w-11" />
                    <div class="nav-brand-copy">
                        <div class="nav-brand-title display-font text-sm font-bold" style="color: #ffffff;">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                        <div class="nav-brand-subtitle text-xs uppercase tracking-[0.3em]" style="color: rgba(255, 255, 255, 0.84);">{{ $roleLabel }}</div>
                    </div>
                </a>
            </div>

            <div class="flex min-w-0 items-center justify-end gap-3">
                <div class="hidden min-w-0 text-right sm:block">
                    <div class="max-w-[14rem] truncate text-sm font-semibold text-white">{{ $user?->fullName() ?? $user?->name }}</div>
                    <div class="text-xs uppercase tracking-[0.22em] text-white/70">{{ $roleLabel }}</div>
                </div>

                <a href="{{ route('profile.edit') }}" class="hidden rounded-full border px-4 py-2 text-sm font-semibold sm:inline-flex" style="border-color: rgba(255, 255, 255, 0.42); background-color: rgba(255, 255, 255, 0.08); color: #ffffff;">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit" class="rounded-full border px-4 py-2 text-sm font-semibold" style="border-color: rgba(255, 255, 255, 0.42); background-color: rgba(255, 255, 255, 0.14); color: #ffffff;">Log Out</button>
                </form>
            </div>
        </div>
    </nav>

    <aside class="app-sidebar fixed left-0 z-40 hidden border-r lg:flex lg:flex-col">
        <div class="flex items-center gap-3 border-b border-slate-200/80 px-4 py-4">
            <x-application-logo class="h-11 w-11 shrink-0" />
            <div class="min-w-0">
                <div class="display-font truncate text-sm font-bold text-white">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                <div class="mt-1 text-xs uppercase tracking-[0.22em] text-white/60">Navigation</div>
            </div>
        </div>

        <div class="app-sidebar-scroll flex-1 overflow-y-auto px-3 py-4">
            @foreach ($navGroups as $group)
                <div class="app-sidebar-group">
                    <div class="app-sidebar-label">{{ $group['label'] }}</div>
                    <div class="mt-2 space-y-1">
                        @foreach ($group['items'] as $link)
                            @php($isActive = collect(explode('|', $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern)))
                            <a
                                href="{{ route($link['route'], $link['params'] ?? []) }}"
                                class="app-sidebar-link {{ $isActive ? 'is-active' : '' }}"
                                @if ($isActive) aria-current="page" @endif
                            >
                                <span class="app-sidebar-link-icon">{{ $link['icon'] ?? 'GO' }}</span>
                                <span class="min-w-0 truncate">{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="app-sidebar-group">
                <div class="app-sidebar-label">Account</div>
                <div class="mt-2 space-y-1">
                    @foreach ($accountLinks as $link)
                        @php($isActive = collect(explode('|', $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern)))
                        <a
                            href="{{ route($link['route'], $link['params'] ?? []) }}"
                            class="app-sidebar-link {{ $isActive ? 'is-active' : '' }}"
                            @if ($isActive) aria-current="page" @endif
                        >
                            <span class="app-sidebar-link-icon">{{ $link['icon'] ?? 'GO' }}</span>
                            <span class="min-w-0 truncate">{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200/80 px-4 py-4">
            <div class="min-w-0">
                <div class="truncate text-sm font-bold text-white">{{ $user?->fullName() ?? $user?->name }}</div>
                <div class="mt-1 text-xs uppercase tracking-[0.22em] text-white/60">{{ $roleLabel }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/15">
                    Log Out
                </button>
            </form>
        </div>
    </aside>

    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-[60] lg:hidden">
        <button type="button" class="absolute inset-0 bg-slate-950/55" @click="open = false" aria-label="Close sidebar navigation"></button>
        <aside
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="mobile-sidebar-panel relative h-full overflow-y-auto border-r border-slate-200 shadow-2xl"
        >
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-4">
                <div class="flex min-w-0 items-center gap-3">
                    <x-application-logo class="h-10 w-10 shrink-0" />
                    <div class="min-w-0">
                        <div class="display-font truncate text-sm font-bold text-white">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                        <div class="mt-1 text-xs uppercase tracking-[0.22em] text-white/60">{{ $roleLabel }}</div>
                    </div>
                </div>
                <button @click="open = false" class="hamburger-button is-open rounded-2xl border border-white/15 text-white" aria-label="Close sidebar navigation">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
            </div>

            <div class="px-3 py-4">
                @foreach ($navGroups as $group)
                    <div class="app-sidebar-group">
                        <div class="app-sidebar-label">{{ $group['label'] }}</div>
                        <div class="mt-2 space-y-1">
                            @foreach ($group['items'] as $link)
                                @php($isActive = collect(explode('|', $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern)))
                                <a
                                    href="{{ route($link['route'], $link['params'] ?? []) }}"
                                    @click="open = false"
                                    class="app-sidebar-link {{ $isActive ? 'is-active' : '' }}"
                                    @if ($isActive) aria-current="page" @endif
                                >
                                    <span class="app-sidebar-link-icon">{{ $link['icon'] ?? 'GO' }}</span>
                                    <span class="min-w-0 truncate">{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="app-sidebar-group">
                    <div class="app-sidebar-label">Account</div>
                    <div class="mt-2 space-y-1">
                        @foreach ($accountLinks as $link)
                            @php($isActive = collect(explode('|', $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern)))
                            <a
                                href="{{ route($link['route'], $link['params'] ?? []) }}"
                                @click="open = false"
                                class="app-sidebar-link {{ $isActive ? 'is-active' : '' }}"
                                @if ($isActive) aria-current="page" @endif
                            >
                                <span class="app-sidebar-link-icon">{{ $link['icon'] ?? 'GO' }}</span>
                                <span class="min-w-0 truncate">{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-5">
                    @csrf
                    <button type="submit" class="w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-left text-sm font-semibold text-white">
                        Log Out
                    </button>
                </form>
            </div>
        </aside>
    </div>
</div>
