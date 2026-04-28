<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Administration</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">People hub for the school system</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">Manage students, parents, and staff from dedicated workspaces while keeping the rest of your custom website features untouched.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-[1.5rem] border border-white/20 bg-white/70 px-4 py-4 text-center shadow-sm backdrop-blur">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Students</div>
                    <div class="display-font mt-2 text-2xl font-bold text-slate-950">{{ $studentCount }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-white/20 bg-white/70 px-4 py-4 text-center shadow-sm backdrop-blur">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Parents</div>
                    <div class="display-font mt-2 text-2xl font-bold text-slate-950">{{ $parentCount }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-white/20 bg-white/70 px-4 py-4 text-center shadow-sm backdrop-blur">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Staff</div>
                    <div class="display-font mt-2 text-2xl font-bold text-slate-950">{{ $staffCount }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-white/20 bg-white/70 px-4 py-4 text-center shadow-sm backdrop-blur">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Classes</div>
                    <div class="display-font mt-2 text-2xl font-bold text-slate-950">{{ $classCount }}</div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-8 xl:grid-cols-[1.15fr,0.85fr]">
        <section class="section-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Management areas</div>
                    <h2 class="display-font mt-2 text-2xl font-bold text-slate-950">Dedicated record pages</h2>
                </div>
                <div class="text-sm text-slate-500">Each page keeps its own search, grouping, and profile tools.</div>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                <a href="{{ route('admin.students.index') }}" class="management-module-card module-tone-student">
                    <div class="management-module-badge">Student Management</div>
                    <h3 class="display-font mt-5 text-2xl font-bold">Students</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-700">Register students, assign classes, update biodata, and open full student profiles.</p>
                    <div class="management-module-meta mt-5">{{ $studentCount }} student record{{ $studentCount === 1 ? '' : 's' }}</div>
                </a>

                <a href="{{ route('admin.parents.index') }}" class="management-module-card module-tone-parent">
                    <div class="management-module-badge">Parents Management</div>
                    <h3 class="display-font mt-5 text-2xl font-bold">Parents</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-700">Review linked parent accounts, child assignments, contact details, and portal coverage.</p>
                    <div class="management-module-meta mt-5">{{ $parentCount }} linked parent account{{ $parentCount === 1 ? '' : 's' }}</div>
                </a>

                <a href="{{ route('admin.staff.index') }}" class="management-module-card module-tone-staff">
                    <div class="management-module-badge">Staff Management</div>
                    <h3 class="display-font mt-5 text-2xl font-bold">Staff</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-700">Manage staff records, departments, and the teaching side of the school structure.</p>
                    <div class="management-module-meta mt-5">{{ $staffCount }} staff profile{{ $staffCount === 1 ? '' : 's' }}</div>
                </a>
            </div>
        </section>

        <section class="section-card">
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">School structure</div>
            <h2 class="display-font mt-2 text-2xl font-bold text-slate-950">What this hub now covers</h2>

            <div class="mt-6 space-y-4">
                <div class="rounded-[1.5rem] border border-slate-200 bg-white/70 px-5 py-5">
                    <div class="font-semibold text-slate-900">Student records</div>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Admissions, parent linkage, class placement, and profile-level updates.</p>
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-white/70 px-5 py-5">
                    <div class="font-semibold text-slate-900">Parent linkage</div>
                    <p class="mt-2 text-sm leading-7 text-slate-600">A dedicated parent page now makes it easy to see which families are already connected to the portal.</p>
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-white/70 px-5 py-5">
                    <div class="font-semibold text-slate-900">Staff organization</div>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Department-based staff records remain separate so the school workspace stays clean and easy to scan.</p>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
