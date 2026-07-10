<x-app-layout>
    @php
        $compactMoney = function (float $amount): string {
            $sign = $amount < 0 ? '-' : '';
            $absolute = abs($amount);

            return match (true) {
                $absolute >= 1000000000 => $sign.'₦'.number_format($absolute / 1000000000, 2).'B',
                $absolute >= 1000000 => $sign.'₦'.number_format($absolute / 1000000, 2).'M',
                $absolute >= 1000 => $sign.'₦'.number_format($absolute / 1000, 1).'K',
                default => $sign.'₦'.number_format($absolute, 0),
            };
        };
    @endphp

    <x-slot name="header">
        <x-page-header :title="'Welcome back, ' . $user->name . '.'">
            <x-slot name="eyebrow">Digital campus control room</x-slot>
            <x-slot name="description">{{ $schoolSettings['portal_notice'] ?? 'Manage people, academics, fees, assessments, and communication from one school-wide dashboard.' }}</x-slot>
            <x-slot name="actions">
                <div class="rounded-3xl brand-gradient px-5 py-4 text-white shadow-xl shadow-slate-900/10 sm:px-6 sm:py-5 flex flex-col justify-center min-w-[200px]">
                    <div class="text-[10px] font-extrabold uppercase tracking-[0.25em] text-white/70">{{ $user->roleLabel() }}</div>
                    <div class="display-font mt-1.5 text-xl sm:text-2xl font-black tracking-tight">{{ now()->format('F j, Y') }}</div>
                    <div class="mt-0.5 text-xs font-bold text-white/80 uppercase tracking-wider">{{ $schoolSettings['school_name'] ?? 'Beloved Schools' }}</div>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <!-- Stats Section —— vibrant gradient cards -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($stats as $stat)
            @php
                $accentColor = 'blue';
                $iconName = 'circle';
                $lbl = strtolower($stat['label']);
                if      (str_contains($lbl, 'student'))                            { $accentColor = 'blue';   $iconName = 'student'; }
                elseif  (str_contains($lbl, 'staff') || str_contains($lbl,'teacher')) { $accentColor = 'green';  $iconName = 'staff'; }
                elseif  (str_contains($lbl, 'invoice'))                            { $accentColor = 'orange'; $iconName = 'finance'; }
                elseif  (str_contains($lbl, 'payment'))                            { $accentColor = 'rose';   $iconName = 'finance'; }
                elseif  (str_contains($lbl, 'parent'))                             { $accentColor = 'purple'; $iconName = 'parents'; }
                elseif  (str_contains($lbl, 'class'))                              { $accentColor = 'teal';   $iconName = 'classes'; }
                elseif  (str_contains($lbl, 'subject'))                            { $accentColor = 'orange'; $iconName = 'learning'; }
                elseif  (str_contains($lbl, 'fee') || str_contains($lbl,'bill'))   { $accentColor = 'gold';   $iconName = 'finance'; }
                elseif  (str_contains($lbl, 'cbt') || str_contains($lbl,'exam'))   { $accentColor = 'purple'; $iconName = 'portal'; }
            @endphp
            <x-stat-card :label="$stat['label']" :value="$stat['value']" :accent="$accentColor" :icon="$iconName" />
        @endforeach
    </div>

    <!-- Main Grid Section -->
    <div class="mt-8 grid gap-8 lg:grid-cols-[1.25fr,0.95fr]">
        <x-section-card class="dashboard-dark-panel dashboard-quick-actions-panel" title="Quick actions" subtitle="Jump into the part of the platform your role uses most." icon="dashboard" tone="blue"
                        style="background: var(--dashboard-quick-action-bg, var(--theme-secondary)); border:1px solid rgba(255,255,255,0.09); box-shadow:0 20px 48px rgba(2,6,23,0.22); --panel-title-color:#ffffff; --panel-desc-color:rgba(226,232,240,0.82); --panel-content-color:#ffffff;">
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach ($quickAccessCards as $card)
                    <x-quick-action-card 
                        :title="$card['title']" 
                        :description="$card['description']" 
                        :href="$card['route']" 
                        :icon="$card['icon'] ?? 'circle'" 
                        :tone="$card['tone'] ?? 'blue'" 
                    />
                @endforeach
            </div>
        </x-section-card>

        <x-dashboard-card class="dashboard-dark-panel dashboard-announcements-panel" title="Latest announcements" subtitle="Recent publications across the school network" icon="announcement" accent="gold"
                          style="background: var(--dashboard-announcement-bg, var(--theme-secondary)); border:1px solid var(--theme-border-soft, rgba(255,255,255,0.09)); box-shadow:0 20px 48px rgba(2,6,23,0.22); --panel-title-color:var(--theme-text-dark-card, #ffffff); --panel-desc-color:var(--theme-text-muted, rgba(226,232,240,0.82)); --panel-content-color:var(--theme-text-dark-card, #ffffff);">
            <div class="mt-4 space-y-4">
                @forelse ($announcements as $announcement)
                    <article class="rounded-[16px] p-4 hover:shadow-md transition duration-200"
                             style="background: var(--theme-card-announcement, rgba(255,255,255,0.05)); border:1px solid var(--theme-border-soft, rgba(255,255,255,0.1));">
                        <x-status-badge :status="$announcement->category ?? 'General'" class="scale-90 origin-left" />
                        <h3 class="mt-2.5 display-font text-base font-extrabold leading-snug" style="color:var(--theme-text-dark-card, #ffffff);">{{ $announcement->title }}</h3>
                        <p class="mt-2 text-xs font-semibold leading-relaxed" style="color:var(--theme-text-muted, rgba(226,232,240,0.80));">{{ $announcement->excerpt ?: \Illuminate\Support\Str::limit($announcement->body, 120) }}</p>
                    </article>
                @empty
                    <x-empty-state title="No Announcements Yet" description="When administrators publish updates, they will appear here." icon="announcement" />
                @endforelse
            </div>
        </x-dashboard-card>
    </div>

    <!-- Finance Operational Picture -->
    @if ($financeSnapshot)
        <div class="mt-8">
            <x-section-card class="dashboard-dark-panel dashboard-finance-panel" title="Operational payment picture" subtitle="School finance board" icon="finance" tone="gold"
                        style="background: var(--dashboard-finance-bg, var(--theme-secondary)); border:1px solid var(--theme-border-soft, rgba(255,255,255,0.09)); box-shadow:0 20px 48px rgba(2,6,23,0.22); --panel-title-color:var(--theme-text-dark-card, #ffffff); --panel-desc-color:var(--theme-text-muted, rgba(226,232,240,0.82)); --panel-content-color:var(--theme-text-dark-card, #ffffff);">
                <x-slot name="actions">
                    <x-action-button variant="secondary" :href="route('admin.finance.records', ['section' => 'payment-summary'])" icon="finance-records">
                        Open Finance Records
                    </x-action-button>
                </x-slot>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <x-stat-card label="Students" :value="$financeSnapshot['students']" accent="blue" :link="route('admin.finance.records', ['section' => 'class-bills'])" linkText="View class billing">
                        {{ $financeSnapshot['classes'] }} class{{ $financeSnapshot['classes'] === 1 ? '' : 'es' }}
                    </x-stat-card>

                    <x-stat-card label="Total billed" :value="$compactMoney((float) $financeSnapshot['totalBilled'])" accent="gold" />

                    <x-stat-card label="Total collected" :value="$compactMoney((float) $financeSnapshot['totalCollected'])" accent="green" />

                    <x-stat-card label="Outstanding" :value="$compactMoney((float) $financeSnapshot['outstanding'])" accent="red">
                        {{ $financeSnapshot['debtorStudents'] }} student{{ $financeSnapshot['debtorStudents'] === 1 ? '' : 's' }} owing
                    </x-stat-card>
                </div>

                <div class="mt-6 pt-5" style="border-top:1px solid var(--theme-border-soft, rgba(255,255,255,0.12));">
                    <x-progress-bar :percentage="$financeSnapshot['collectionRate']" label="Collection Rate" color="green" :valueText="number_format((float) $financeSnapshot['collectionRate'], 1) . '%'" />
                </div>
            </x-section-card>
        </div>
    @endif
</x-app-layout>
