<x-app-layout>
    <x-slot name="header">
        <x-page-header title="People Hub" eyebrow="Administration" description="Manage students, parents, and staff from dedicated workspaces while keeping the rest of your custom website features untouched.">
            <x-slot name="actions">
                <x-action-button variant="secondary" :href="route('admin.parents.index')">Parents</x-action-button>
                <x-action-button variant="secondary" :href="route('admin.staff.index')">Staff</x-action-button>
                <x-action-button variant="primary" :href="route('admin.students.index')">Students</x-action-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <!-- People Hub Metrics Grid -->
    <div class="metrics-grid metrics-grid-4 mb-8">
        <x-stat-card label="Students" :value="$studentCount" accent="blue" icon="student" :link="route('admin.students.index')" linkText="Student directory" />
        <x-stat-card label="Parents" :value="$parentCount" accent="green" icon="parents" :link="route('admin.parents.index')" linkText="Parent directory" />
        <x-stat-card label="Staff" :value="$staffCount" accent="purple" icon="staff" :link="route('admin.staff.index')" linkText="Staff directory" />
        <x-stat-card label="Classes" :value="$classCount" accent="gold" icon="classes" :link="route('admin.academics')" linkText="Manage classes" />
    </div>

    <!-- Management Modules and System Scope -->
    <div class="grid gap-8 xl:grid-cols-[1.2fr,0.8fr]">
        <!-- Left Side: Management Area modules -->
        <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)]">
            <div class="border-b border-slate-100 pb-4 mb-6">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">School Office Areas</span>
                <h2 class="display-font mt-1 text-2xl font-bold text-slate-900 leading-snug">Dedicated management portals</h2>
                <p class="text-xs font-semibold text-slate-500 mt-1">Each workspace features customized search filters, class groupings, and dedicated profile actions.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Students Card -->
                <div class="card bg-blue-50/20 border border-blue-100/60 rounded-[18px] p-5 hover:border-[#fbbf24] hover:shadow-lg hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shadow-sm">
                            <x-app-icon name="student" class="w-5 h-5" />
                        </div>
                        <h3 class="display-font mt-4 text-xl font-bold text-slate-900">Student Profiles</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">Register new intakes, update sibling lists, configure class billings, and search dynamic rosters.</p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-150 flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-450">{{ $studentCount }} Records</span>
                        <x-action-button variant="primary" :href="route('admin.students.index')" class="!px-3 !py-1.5 !rounded-lg text-[10px]">Open</x-action-button>
                    </div>
                </div>

                <!-- Parents Card -->
                <div class="card bg-emerald-50/20 border border-emerald-100/60 rounded-[18px] p-5 hover:border-[#fbbf24] hover:shadow-lg hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shadow-sm">
                            <x-app-icon name="parents" class="w-5 h-5" />
                        </div>
                        <h3 class="display-font mt-4 text-xl font-bold text-slate-900">Guardians &amp; Sibling</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">Monitor parent portals, track contact details, check linked children lists, and verify active coverages.</p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-150 flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-450">{{ $parentCount }} Linked</span>
                        <x-action-button variant="primary" :href="route('admin.parents.index')" class="!px-3 !py-1.5 !rounded-lg text-[10px]">Open</x-action-button>
                    </div>
                </div>

                <!-- Staff Card -->
                <div class="card bg-purple-50/20 border border-purple-100/60 rounded-[18px] p-5 hover:border-[#fbbf24] hover:shadow-lg hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center shadow-sm">
                            <x-app-icon name="staff" class="w-5 h-5" />
                        </div>
                        <h3 class="display-font mt-4 text-xl font-bold text-slate-900">Staff Directories</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">Organize educational staff, assign class teachers, setup monthly salaries, and edit profiles.</p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-150 flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-450">{{ $staffCount }} Profiles</span>
                        <x-action-button variant="primary" :href="route('admin.staff.index')" class="!px-3 !py-1.5 !rounded-lg text-[10px]">Open</x-action-button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Hub scope explanation -->
        <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)] flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-100 pb-4 mb-4">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-450">System Coverage</span>
                    <h2 class="display-font mt-1 text-xl font-bold text-slate-900 leading-snug">What this hub covers</h2>
                </div>

                <div class="space-y-4">
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-[#fbbf24] transition duration-200">
                        <div class="font-bold text-slate-800 text-sm">Full student records tracking</div>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Admissions registration, parent association links, class assignment updates, and medical data files.</p>
                    </div>
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-[#fbbf24] transition duration-200">
                        <div class="font-bold text-slate-800 text-sm">Automated guardian mappings</div>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Family profile cards, linking multiple children to a single guardian account, and tracing billing balances.</p>
                    </div>
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-[#fbbf24] transition duration-200">
                        <div class="font-bold text-slate-800 text-sm">Staff &amp; Academic structure depts</div>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Dedicated teacher directories, employee record numbers, designation titles, and academic qualifications.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
