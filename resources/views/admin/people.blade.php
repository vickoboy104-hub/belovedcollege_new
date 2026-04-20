<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Administration</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">People management</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">Choose the record area you want to manage. Students and staff now live on separate pages with their own search and directory tools.</p>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-8 lg:grid-cols-2">
        <a href="{{ route('admin.students.index') }}" class="section-card block transition hover:-translate-y-1 hover:shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Student page</div>
            <h2 class="display-font mt-4 text-3xl font-bold text-slate-950">Students</h2>
            <p class="mt-3 text-sm text-slate-600">Register students, search by name or class, and open each student profile for updates.</p>
            <div class="mt-6 inline-flex rounded-full px-5 py-3 text-sm font-semibold text-white" style="background-color: var(--theme-primary);">
                Open student management
            </div>
        </a>

        <a href="{{ route('admin.staff.index') }}" class="section-card block transition hover:-translate-y-1 hover:shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Staff page</div>
            <h2 class="display-font mt-4 text-3xl font-bold text-slate-950">Staff</h2>
            <p class="mt-3 text-sm text-slate-600">Register staff, search by name or department, and manage staff records from a dedicated page.</p>
            <div class="mt-6 inline-flex rounded-full px-5 py-3 text-sm font-semibold text-white" style="background-color: var(--theme-primary);">
                Open staff management
            </div>
        </a>
    </div>
</x-app-layout>
