<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Digital campus control room</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">Welcome back, {{ $user->name }}.</h1>
                <p class="mt-3 max-w-2xl text-base text-slate-600">
                    {{ $schoolSettings['portal_notice'] ?? 'Manage people, academics, fees, assessments, and communication from one school-wide dashboard.' }}
                </p>
            </div>
            <div class="rounded-3xl brand-gradient px-6 py-5 text-white shadow-xl shadow-slate-900/10">
                <div class="text-xs uppercase tracking-[0.3em] text-white/70">{{ $user->roleLabel() }}</div>
                <div class="display-font mt-2 text-2xl font-bold">{{ now()->format('F j, Y') }}</div>
                <div class="mt-1 text-sm text-white/80">{{ $schoolSettings['school_name'] ?? 'SchoolSphere' }}</div>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="stat-tile {{ $stat['accent'] }}">
                <div class="text-sm uppercase tracking-[0.24em] text-slate-500">{{ $stat['label'] }}</div>
                <div class="display-font mt-3 text-4xl font-bold">{{ $stat['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-[1.25fr,0.95fr]">
        <section class="section-card">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Quick actions</h2>
                <p class="mt-1 text-sm text-slate-500">Jump into the part of the platform your role uses most.</p>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <a href="{{ route('dashboard') }}" class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="display-font text-lg font-bold text-slate-900">Overview</div>
                    <p class="mt-2 text-sm text-slate-600">System-wide metrics, announcements, and current operational status.</p>
                </a>

                @if ($user->hasAnyRole(['admin', 'principal']))
                    <a href="{{ route('admin.people') }}" class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="display-font text-lg font-bold text-slate-900">People hub</div>
                        <p class="mt-2 text-sm text-slate-600">Open the student page or staff page and manage each record set separately.</p>
                    </a>
                    <a href="{{ route('admin.academics') }}" class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="display-font text-lg font-bold text-slate-900">Academic structure</div>
                        <p class="mt-2 text-sm text-slate-600">Sessions, terms, classes, subjects, and public announcements.</p>
                    </a>
                @endif

                @if ($user->hasAnyRole(['admin', 'principal', 'accountant']))
                    <a href="{{ route('admin.finance') }}" class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="display-font text-lg font-bold text-slate-900">Fees and payments</div>
                        <p class="mt-2 text-sm text-slate-600">Create invoices, track school fees, and monitor gateway activity.</p>
                    </a>
                @endif

                @if ($user->hasAnyRole(['admin', 'principal', 'teacher']))
                    <a href="{{ route('teacher.learning') }}" class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="display-font text-lg font-bold text-slate-900">Teaching workspace</div>
                        <p class="mt-2 text-sm text-slate-600">Publish lessons, set assignments, grade work, and submit results.</p>
                    </a>
                @endif

                @if ($user->hasAnyRole(['student', 'parent']))
                    <a href="{{ route('portal.index') }}" class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-5">
                        <div class="display-font text-lg font-bold text-slate-900">Student portal</div>
                        <p class="mt-2 text-sm text-slate-600">Access learning materials, results, attendance, and fees from one place.</p>
                    </a>
                @endif
            </div>
        </section>

        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Latest announcements</h2>
            <div class="mt-5 space-y-4">
                @forelse ($announcements as $announcement)
                    <article class="rounded-3xl border border-slate-200 px-5 py-4">
                        <div class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $announcement->category }}</div>
                        <h3 class="mt-2 display-font text-lg font-bold text-slate-900">{{ $announcement->title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $announcement->excerpt ?: \Illuminate\Support\Str::limit($announcement->body, 120) }}</p>
                    </article>
                @empty
                    <p class="text-sm text-slate-500">No announcements published yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
