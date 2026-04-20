<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Result center</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">Choose a student before editing reports</h1>
                <p class="mt-2 text-sm text-slate-600">Browse students by class category, then open a dedicated report workspace for one student at a time.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('reports.checker') }}" target="_blank" class="theme-button-secondary">Open public result checker</a>
            </div>
        </div>
    </x-slot>

    @php
        $currentIndexRoute = $activeReportClassPage === 'all'
            ? route('admin.reports.index')
            : route('admin.reports.index', ['classSlug' => $activeReportClassPage]);
    @endphp

    <section class="section-card">
        <form method="GET" action="{{ $currentIndexRoute }}" class="grid gap-4 lg:grid-cols-[0.9fr,1.1fr,auto,auto]">
            <select name="term_id" class="theme-input">
                @foreach ($terms as $term)
                    <option value="{{ $term->id }}" @selected($selectedTerm?->id === $term->id)>{{ $term->name }} - {{ $term->academicSession->name ?? 'No session' }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by student name, admission number, or student ID" class="theme-input" />
            <button type="submit" class="theme-button">Refresh</button>
            <a href="{{ $currentIndexRoute }}" class="theme-button-secondary text-center">Reset</a>
        </form>
    </section>

    <div class="mt-8">
        <x-section-nav :items="$classNavItems" :active="$activeReportClassPage" />
    </div>

    @if ($activeReportClassPage === 'all' && $search === '')
        <section class="section-card">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">{{ $pageTitle }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Pick a class category first so the report center only shows the students you want to work on.</p>
                </div>
                <div class="rounded-3xl bg-slate-50 px-5 py-4 text-right">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Available categories</div>
                    <div class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $classDirectory->count() }}</div>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($classDirectory as $classItem)
                    <a href="{{ $classItem['href'] }}" class="rounded-[2rem] border border-slate-200 bg-slate-50 px-5 py-5 transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-white">
                        <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Category</div>
                        <div class="display-font mt-3 text-2xl font-bold text-slate-950">{{ $classItem['name'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">{{ $classItem['count'] }} student{{ $classItem['count'] === 1 ? '' : 's' }}</div>
                        <div class="mt-4 text-sm font-semibold" style="color: var(--theme-primary);">Open category</div>
                    </a>
                @endforeach
            </div>
        </section>
    @else
        <section class="section-card">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">{{ $pageTitle }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Each student opens into a dedicated report workspace where you can review scores, update remarks, publish, and print.</p>
                </div>
                <div class="rounded-3xl bg-slate-50 px-5 py-4 text-right">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Students shown</div>
                    <div class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $students->count() }}</div>
                </div>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($students as $student)
                    <article class="rounded-[2rem] border border-slate-200 bg-white px-5 py-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="display-font text-xl font-bold text-slate-950">{{ $student->user->fullName() }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $student->schoolClass->display_name ?? 'Unassigned class' }}</div>
                            </div>
                            <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                {{ $selectedTerm?->name ?? 'Term' }}
                            </div>
                        </div>

                        <div class="mt-5 space-y-2 text-sm text-slate-600">
                            <div><span class="font-semibold text-slate-900">Admission No:</span> {{ $student->admission_no ?: 'Not set' }}</div>
                            <div><span class="font-semibold text-slate-900">Student ID:</span> {{ $student->student_id_no ?: 'Not set' }}</div>
                            <div><span class="font-semibold text-slate-900">Guardian:</span> {{ $student->guardian_name ?: ($student->parent->name ?? 'No guardian') }}</div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a
                                href="{{ route('admin.reports.show', array_filter([
                                    'student' => $student,
                                    'section' => 'overview',
                                    'term_id' => $selectedTerm?->id,
                                    'classSlug' => $activeReportClassPage !== 'all' ? $activeReportClassPage : null,
                                    'search' => $search !== '' ? $search : null,
                                ], fn ($value) => $value !== null && $value !== '')) }}"
                                class="theme-button"
                            >
                                Open workspace
                            </a>
                            <a href="{{ route('admin.students.record', $student) }}" target="_blank" class="theme-button-secondary">Student dossier</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[2rem] border border-dashed border-slate-300 px-6 py-8 text-sm text-slate-500 md:col-span-2 xl:col-span-3">No students matched this category or search.</div>
                @endforelse
            </div>
        </section>
    @endif
</x-app-layout>
