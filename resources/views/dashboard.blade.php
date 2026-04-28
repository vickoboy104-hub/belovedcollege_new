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
            <div class="rounded-3xl brand-gradient px-5 py-4 text-white shadow-xl shadow-slate-900/10 sm:px-6 sm:py-5">
                <div class="text-xs uppercase tracking-[0.3em] text-white/70">{{ $user->roleLabel() }}</div>
                <div class="display-font mt-2 text-2xl font-bold">{{ now()->format('F j, Y') }}</div>
                <div class="mt-1 text-sm text-white/80">{{ $schoolSettings['school_name'] ?? 'SchoolSphere' }}</div>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
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
                <a href="{{ route('dashboard') }}" class="management-module-card module-tone-school">
                    <div class="management-module-badge">Dashboard</div>
                    <h3 class="display-font mt-5 text-2xl font-bold">Overview</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-700">System-wide metrics, announcements, and current operational status.</p>
                </a>

                @foreach ($quickAccessCards as $card)
                    <a href="{{ $card['route'] }}" class="management-module-card module-tone-{{ $card['tone'] }}">
                        <div class="management-module-badge">{{ $card['title'] }}</div>
                        <h3 class="display-font mt-5 text-2xl font-bold">{{ $card['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-700">{{ $card['description'] }}</p>
                    </a>
                @endforeach
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

    @if ($financeSnapshot)
        <section class="section-card mt-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">School finance board</div>
                    <h2 class="display-font mt-2 text-2xl font-bold text-slate-950">Operational payment picture</h2>
                </div>
                <a href="{{ route('admin.finance.records', ['section' => 'payment-summary']) }}" class="theme-button-secondary">Open finance records</a>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Students</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">{{ $financeSnapshot['students'] }}</div>
                    <div class="mt-2 text-sm text-slate-500">{{ $financeSnapshot['classes'] }} class{{ $financeSnapshot['classes'] === 1 ? '' : 'es' }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Total billed</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">NGN {{ number_format((float) $financeSnapshot['totalBilled'], 2) }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Total collected</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">NGN {{ number_format((float) $financeSnapshot['totalCollected'], 2) }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Outstanding / debtors</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">NGN {{ number_format((float) $financeSnapshot['outstanding'], 2) }}</div>
                    <div class="mt-2 text-sm text-slate-500">{{ $financeSnapshot['debtorStudents'] }} student{{ $financeSnapshot['debtorStudents'] === 1 ? '' : 's' }} owing</div>
                </div>
            </div>

            <div class="mt-5 rounded-[1.75rem] border border-slate-200 bg-white/80 px-5 py-5">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Collection rate</div>
                        <div class="display-font mt-2 text-3xl font-bold text-slate-950">{{ number_format((float) $financeSnapshot['collectionRate'], 1) }}%</div>
                    </div>
                    <a href="{{ route('admin.finance.records', ['section' => 'class-bills']) }}" class="text-sm font-semibold text-[color:var(--theme-primary)]">View class billing</a>
                </div>
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-200">
                    <div class="h-full rounded-full" style="width: {{ min(100, max(0, (float) $financeSnapshot['collectionRate'])) }}%; background: linear-gradient(135deg, var(--theme-primary), var(--theme-accent));"></div>
                </div>
            </div>
        </section>
    @endif
</x-app-layout>
