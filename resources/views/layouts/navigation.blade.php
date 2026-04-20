@php
    $user = auth()->user();
    $links = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard'],
    ];

    if ($user->hasAnyRole(['admin', 'principal'])) {
        $links[] = ['label' => 'People', 'route' => 'admin.people', 'active' => 'admin.people|admin.students.*|admin.staff.*'];
        $links[] = ['label' => 'Academics', 'route' => 'admin.academics', 'active' => 'admin.academics*'];
        $links[] = ['label' => 'Reports', 'route' => 'admin.reports.index', 'active' => 'admin.reports*'];
        $links[] = ['label' => 'Settings', 'route' => 'admin.settings', 'active' => 'admin.settings*'];
    }

    if ($user->hasAnyRole(['admin', 'principal', 'accountant'])) {
        $links[] = ['label' => 'Finance', 'route' => 'admin.finance', 'active' => 'admin.finance*'];
    }

    if ($user->hasAnyRole(['admin', 'principal', 'teacher'])) {
        $links[] = ['label' => 'Teaching', 'route' => 'teacher.learning', 'active' => 'teacher.learning*'];
    }

    if ($user->hasAnyRole(['student', 'parent'])) {
        $links[] = ['label' => 'Portal', 'route' => 'portal.index', 'active' => 'portal.index*'];
    }
@endphp

<nav
    x-data="{ open: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    class="sticky top-0 z-40 border-b"
    style="background-color: var(--theme-top-bar); border-color: rgba(255, 255, 255, 0.16);"
>
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <x-application-logo class="h-11 w-11" />
                <div>
                    <div class="display-font text-sm font-bold" style="color: #ffffff;">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                    <div class="text-xs uppercase tracking-[0.3em]" style="color: rgba(255, 255, 255, 0.84);">{{ $user->roleLabel() }}</div>
                </div>
            </a>

            <div class="hidden items-center gap-2 md:flex">
                @foreach ($links as $link)
                    @php($isActive = collect(explode('|', $link['active']))->contains(fn ($pattern) => request()->routeIs($pattern)))
                    <a
                        href="{{ route($link['route']) }}"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition"
                        style="{{ $isActive ? 'background-color: rgba(255, 255, 255, 0.18); color: #ffffff;' : 'color: #ffffff;' }}"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="hidden items-center gap-3 md:flex">
            <a href="{{ route('profile.edit') }}" class="rounded-full border px-4 py-2 text-sm font-semibold" style="border-color: rgba(255, 255, 255, 0.42); background-color: rgba(255, 255, 255, 0.08); color: #ffffff;">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-full border px-4 py-2 text-sm font-semibold" style="border-color: rgba(255, 255, 255, 0.42); background-color: rgba(255, 255, 255, 0.14); color: #ffffff;">Log Out</button>
            </form>
        </div>

        <button @click="open = !open" class="rounded-full border border-white/25 p-3 text-white md:hidden" aria-label="Toggle navigation menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 md:hidden" style="display: none;">
        <button type="button" class="absolute inset-0 bg-slate-950/55" @click="open = false" aria-label="Close navigation menu"></button>
        <div class="drawer-panel relative ml-auto h-full overflow-y-auto border-l px-4 py-5 shadow-2xl" style="background-color: var(--theme-top-bar); border-color: rgba(255, 255, 255, 0.16);">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <div class="display-font text-sm font-bold text-white">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</div>
                    <div class="mt-1 text-xs uppercase tracking-[0.28em] text-white/75">{{ $user->roleLabel() }}</div>
                </div>
                <button @click="open = false" class="rounded-full border border-white/20 p-3 text-white" aria-label="Close navigation menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>

            <div class="space-y-2">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}" @click="open = false" class="drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-white/10" style="color: #ffffff;">
                        {{ $link['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('profile.edit') }}" @click="open = false" class="drawer-link block rounded-2xl px-4 py-3 text-sm font-semibold hover:bg-white/10" style="color: #ffffff;">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="drawer-link block w-full rounded-2xl px-4 py-3 text-left text-sm font-semibold" style="background-color: rgba(255, 255, 255, 0.14); color: #ffffff;">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
