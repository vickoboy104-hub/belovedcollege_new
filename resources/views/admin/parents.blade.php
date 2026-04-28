<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Parents management</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">Guardian and family records</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">This page adds the parent-management layer from the digital school system without disturbing the custom parts of your website.</p>
            </div>
            <form method="GET" action="{{ route('admin.parents.index') }}" class="flex w-full max-w-xl flex-col gap-3 sm:flex-row">
                <input name="search" value="{{ $search }}" placeholder="Search by parent, phone, child, admission number, or class" class="theme-input" />
                <button type="submit" class="theme-button">Search</button>
            </form>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Linked parents</div>
            <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $summary['linkedParents'] }}</div>
        </div>
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Children covered</div>
            <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $summary['childrenCovered'] }}</div>
        </div>
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Multi-child families</div>
            <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $summary['multiChildFamilies'] }}</div>
        </div>
        <div class="stat-tile">
            <div class="text-sm uppercase tracking-[0.24em] text-slate-500">Students without parent portal</div>
            <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $summary['studentsWithoutPortalParent'] }}</div>
        </div>
    </div>

    <section class="section-card mt-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Parent directory</h2>
                <p class="mt-2 text-sm text-slate-500">Each parent account shows contact details, linked children, and the class spread under that family.</p>
            </div>
            <a href="{{ route('admin.students.index') }}" class="theme-button-secondary">Open student page</a>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($parentRows as $row)
                <article class="rounded-[1.75rem] border border-slate-200 bg-white/70 px-5 py-5 shadow-sm">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <div class="display-font text-xl font-bold text-slate-950">{{ $row['parent']->fullName() }}</div>
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-2 text-sm text-slate-600">
                                <span>{{ $row['parent']->email ?: 'No email' }}</span>
                                <span>{{ $row['parent']->phone ?: 'No phone' }}</span>
                                <span>{{ $row['child_count'] }} child{{ $row['child_count'] === 1 ? '' : 'ren' }}</span>
                            </div>
                            <div class="mt-3 text-xs uppercase tracking-[0.24em] text-slate-500">
                                Classes: {{ $row['class_names']->implode(', ') }}
                            </div>
                        </div>
                        <div class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700">
                            Parent portal active
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($row['children'] as $child)
                            <div class="rounded-[1.35rem] border border-slate-200 bg-slate-50/90 px-4 py-4">
                                <div class="font-semibold text-slate-900">{{ $child->user->fullName() }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $child->admission_no ?: 'No admission number' }}</div>
                                <div class="mt-2 text-sm text-slate-600">{{ $child->schoolClass->display_name ?? 'No class assigned' }}</div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.students.show', $child) }}" class="text-sm font-semibold text-[color:var(--theme-primary)]">Open student profile</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="rounded-[1.75rem] border border-dashed border-slate-300 px-6 py-8 text-sm text-slate-500">
                    No linked parent records were found for this search yet.
                </div>
            @endforelse
        </div>
    </section>
</x-app-layout>
