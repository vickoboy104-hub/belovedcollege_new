<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Guardian and family records" eyebrow="Parents management" description="Manage parent contacts, linked children, billing follow-up, and family portal records." />
    </x-slot>

    <!-- Parents Workspace Stats Grid -->
    <div class="metrics-grid metrics-grid-4 mb-8">
        <x-stat-card label="Linked parents" :value="$summary['linkedParents']" accent="blue" icon="parents" />
        <x-stat-card label="Children covered" :value="$summary['childrenCovered']" accent="green" icon="student" />
        <x-stat-card label="Multi-child families" :value="$summary['multiChildFamilies']" accent="purple" icon="parents" />
        <x-stat-card label="Students without parent" :value="$summary['studentsWithoutPortalParent']" accent="red" icon="student" />
    </div>

    <!-- Parent Directory List Section -->
    <section class="admin-workspace-card p-6">
        <div class="section-header border-b border-slate-100 pb-5 mb-4">
            <div>
                <h2 class="section-title">Parent directory</h2>
                <p class="section-description">Each parent account shows contact details, linked children, and class spread across families.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.parents.index') }}" class="table-toolbar mb-4">
            <input name="search" value="{{ $search }}" placeholder="Search parent, phone, email, or child name" class="theme-input" />
            <x-action-button type="submit" variant="primary">Search</x-action-button>
            <x-action-button variant="secondary" :href="route('admin.parents.index')">Reset</x-action-button>
        </form>

        <div class="desktop-only-table">
            <x-data-table :headers="['Parent / Guardian', 'Children', 'Classes', 'Phone', 'Status', 'Actions']" minWidth="1180px">
                @forelse ($parentRows as $row)
                    @php
                        $parent = $row['parent'];
                        $firstChild = $row['children']->first();
                        $childrenNames = $row['children']->map(fn ($child) => $child->user->fullName())->join(', ');
                        $parentInitials = collect(explode(' ', $parent->fullName() ?: $parent->name ?: 'Parent'))
                            ->filter()
                            ->map(fn ($part) => substr($part, 0, 1))
                            ->take(2)
                            ->join('');
                        $parentPreview = [
                            'type' => 'parent',
                            'title' => $parent->fullName(),
                            'subtitle' => 'Active Parent - '.$row['child_count'].' child'.($row['child_count'] === 1 ? '' : 'ren'),
                            'avatar' => $parentInitials ?: 'PA',
                            'profileUrl' => $firstChild ? route('admin.students.show', $firstChild) : route('admin.students.index'),
                            'ctaLabel' => 'View Full Profile',
                            'fields' => [
                                ['label' => 'Email', 'value' => $parent->email ?: 'No email address registered'],
                                ['label' => 'Phone', 'value' => $parent->phone ?: 'No phone registered'],
                                ['label' => 'Children Covered', 'value' => $row['child_count'].' child'.($row['child_count'] === 1 ? '' : 'ren')],
                                ['label' => 'Classes', 'value' => $row['class_names']->implode(', ') ?: 'No class assigned'],
                                ['label' => 'Linked Children', 'value' => $childrenNames ?: 'No children linked'],
                            ],
                        ];
                        $contactUrl = $parent->phone ? 'tel:'.$parent->phone : ($parent->email ? 'mailto:'.$parent->email : '#');
                    @endphp
                    <tr>
                        <td>
                            <div class="table-person">
                                <div class="table-avatar">{{ $parentPreview['avatar'] }}</div>
                                <div class="table-person-text">
                                    <strong>{{ $parent->fullName() }}</strong>
                                    <span>{{ $parent->email ?: 'No email address registered' }}</span>
                                </div>
                            </div>
                        </td>
                        <td><span class="table-text-clip">{{ $childrenNames ?: 'No children linked' }}</span></td>
                        <td><span class="table-text-clip">{{ $row['class_names']->implode(', ') ?: 'No class assigned' }}</span></td>
                        <td>{{ $parent->phone ?: 'No phone registered' }}</td>
                        <td><x-status-badge status="Active" /></td>
                        <td>
                            <div class="table-action-group">
                                <button type="button" class="table-view-btn" data-preview='@json($parentPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                <a class="table-mini-link" href="{{ route('admin.students.index', ['search' => $parent->email ?: $parent->fullName()]) }}">Link Child</a>
                                <a class="table-mini-link" href="{{ route('admin.finance.records', ['section' => 'student-balances', 'search' => $parent->email ?: $parent->fullName()]) }}">Billing</a>
                                <a class="table-mini-link" href="{{ $contactUrl }}">Contact</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-empty-state title="No parent records found" description="Search returned no parent or guardian records matching those parameters." icon="parents" />
                        </td>
                    </tr>
                @endforelse
            </x-data-table>
        </div>

        <!-- Responsive Mobile View for Parents -->
        <div class="mobile-record-list mt-6 space-y-4 md:hidden">
            @forelse ($parentRows as $row)
                @php
                    $parent = $row['parent'];
                    $firstChild = $row['children']->first();
                    $childrenNames = $row['children']->map(fn ($child) => $child->user->fullName())->join(', ');
                    $parentInitials = collect(explode(' ', $parent->fullName() ?: $parent->name ?: 'Parent'))
                        ->filter()
                        ->map(fn ($part) => substr($part, 0, 1))
                        ->take(2)
                        ->join('');
                    $parentPreview = [
                        'type' => 'parent',
                        'title' => $parent->fullName(),
                        'subtitle' => 'Active Parent - '.$row['child_count'].' child'.($row['child_count'] === 1 ? '' : 'ren'),
                        'avatar' => $parentInitials ?: 'PA',
                        'profileUrl' => $firstChild ? route('admin.students.show', $firstChild) : route('admin.students.index'),
                        'ctaLabel' => 'View Full Profile',
                        'fields' => [
                            ['label' => 'Email', 'value' => $parent->email ?: 'No email address registered'],
                            ['label' => 'Phone', 'value' => $parent->phone ?: 'No phone registered'],
                            ['label' => 'Children Covered', 'value' => $row['child_count'].' child'.($row['child_count'] === 1 ? '' : 'ren')],
                            ['label' => 'Classes', 'value' => $row['class_names']->implode(', ') ?: 'No class assigned'],
                            ['label' => 'Linked Children', 'value' => $childrenNames ?: 'No children linked'],
                        ],
                    ];
                    $contactUrl = $parent->phone ? 'tel:'.$parent->phone : ($parent->email ? 'mailto:'.$parent->email : '#');
                @endphp
                <article class="mobile-record-card">
                    <div class="flex items-start justify-between border-b border-slate-100 pb-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="table-avatar !h-9 !w-9 !text-xs">
                                {{ $parentPreview['avatar'] }}
                            </div>
                            <div>
                                <div class="mobile-record-title">{{ $parent->fullName() }}</div>
                                <div class="text-[10px] text-slate-500 font-semibold mt-0.5">{{ $parent->email ?: 'No email address registered' }}</div>
                            </div>
                        </div>
                        <x-status-badge status="Active" class="scale-90 origin-right" />
                    </div>

                    <div class="mobile-record-grid">
                        <div class="mobile-record-item">
                            <span class="mobile-record-label">Children</span>
                            <span class="mobile-record-value text-slate-800">{{ $childrenNames ?: 'No children linked' }}</span>
                        </div>
                        <div class="mobile-record-item">
                            <span class="mobile-record-label">Phone</span>
                            <span class="mobile-record-value text-slate-800">{{ $parent->phone ?: 'No phone registered' }}</span>
                        </div>
                    </div>

                    <div class="mobile-action-row border-t border-slate-100 pt-3 mt-4 flex flex-col gap-2">
                        <button
                            type="button"
                            class="table-view-btn w-full !text-center !py-2 !rounded-xl !bg-slate-100 hover:!bg-slate-200 text-slate-700 font-bold transition"
                            data-preview='@json($parentPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'
                        >
                            Quick View
                        </button>
                        <div class="flex w-full gap-2 mt-1">
                            <a href="{{ route('admin.students.index', ['search' => $parent->email ?: $parent->fullName()]) }}" class="theme-button-secondary w-full text-center py-2 px-3 text-xs font-bold rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700">Link Child</a>
                            <a href="{{ route('admin.finance.records', ['section' => 'student-balances', 'search' => $parent->email ?: $parent->fullName()]) }}" class="theme-button-secondary w-full text-center py-2 px-3 text-xs font-bold rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700">Billing</a>
                            <a href="{{ $contactUrl }}" class="theme-button-secondary w-full text-center py-2 px-3 text-xs font-bold rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700">Contact</a>
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state title="No parent records found" description="Search returned no parent or guardian records matching those parameters." icon="parents" />
            @endforelse
        </div>
    </section>

    <x-entity-preview-modal />
</x-app-layout>
