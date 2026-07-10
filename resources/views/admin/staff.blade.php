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
        <x-page-header title="Staff management" eyebrow="Administration" description="This workspace includes professional school-office views for payroll, departmental metrics, and class allocations in addition to the staff directory.">
            <x-slot name="actions">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                    <x-action-button 
                        variant="success" 
                        icon="plus" 
                        class="!rounded-3xl !py-4 !px-6 shadow-xl shadow-emerald-900/10"
                        x-on:click="$dispatch('open-modal', 'register-staff-modal')"
                    >
                        Register Staff Member
                    </x-action-button>

                    <div class="rounded-3xl brand-gradient px-5 py-4 text-white shadow-xl shadow-slate-900/10 sm:px-6 sm:py-5 flex flex-col justify-center min-w-[200px]">
                        <div class="text-[10px] font-extrabold uppercase tracking-[0.25em] text-white/70">Total Active Staff</div>
                        <div class="display-font mt-1.5 text-xl font-black tracking-tight">{{ $staffWorkspaceStats['active_count'] }} Active</div>
                        <div class="mt-0.5 text-xs font-bold text-white/80 uppercase tracking-wider">Out of {{ $staffWorkspaceStats['staff_count'] }} Total</div>
                    </div>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if (session('generated_credentials'))
        @php
            $credentials = session('generated_credentials');
        @endphp
        <div class="mb-8 rounded-[18px] border border-emerald-200 bg-emerald-50 px-6 py-5 text-sm text-emerald-900 shadow-sm">
            <div class="font-bold flex items-center gap-2 text-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Generated {{ $credentials['audience'] }} credentials
            </div>
            <div class="mt-2.5 font-mono bg-white/60 border border-emerald-100 rounded-xl p-3 text-xs leading-relaxed">
                <span class="font-bold text-slate-700">Name:</span> {{ $credentials['name'] }} <span class="mx-2 text-slate-350">|</span>
                <span class="font-bold text-slate-700">Login ID:</span> <span class="bg-emerald-100/80 px-1.5 py-0.5 rounded font-extrabold">{{ $credentials['identifier'] }}</span> <span class="mx-2 text-slate-350">|</span>
                <span class="font-bold text-slate-700">Email:</span> {{ $credentials['email'] }} <span class="mx-2 text-slate-350">|</span>
                <span class="font-bold text-slate-700">Password:</span> <span class="bg-amber-100 px-1.5 py-0.5 rounded font-extrabold">{{ $credentials['password'] }}</span>
            </div>
        </div>
    @endif

    <!-- Workspace Stats Cards Grid -->
    <div class="metrics-grid metrics-grid-5 mb-8">
        <x-stat-card label="Staff" :value="$staffWorkspaceStats['staff_count']" accent="blue" icon="staff" />
        <x-stat-card label="Active" :value="$staffWorkspaceStats['active_count']" accent="green" icon="staff" />
        <x-stat-card label="On Payroll" :value="$staffWorkspaceStats['salary_count']" accent="purple" icon="staff" />
        <x-stat-card label="Monthly Gross Payroll" :value="$compactMoney((float) $staffWorkspaceStats['monthly_total'])" accent="gold" icon="finance" class="sm:col-span-2 lg:col-span-1" />
        <x-stat-card label="Class Teachers" :value="$staffWorkspaceStats['class_teachers']" accent="blue" icon="classes" />
    </div>

    <div class="grid gap-8">
        <!-- Filter Card -->
        @if ($activeStaffView !== 'class-allocation')
            <x-filter-card 
                :action="route('admin.staff.index')" 
                method="GET" 
                :title="$activeStaffView === 'payroll' ? 'Payroll Search' : 'Staff search directory'"
                description="Search staff records by name, email, employee ID, role, or department.">
                <input type="hidden" name="view" value="{{ $activeStaffView }}" />
                <input name="search" value="{{ $search }}" placeholder="Search by name or staff details..." class="theme-input flex-1" />
                <select name="department" class="theme-input lg:w-56">
                    <option value="">All departments</option>
                    @foreach ($departmentOptions as $department)
                        <option value="{{ $department }}" @selected($departmentFilter === $department)>{{ $department }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2">
                    <x-action-button type="submit" variant="primary">Search</x-action-button>
                    <x-action-button variant="secondary" :href="route('admin.staff.index', ['view' => $activeStaffView])">Reset</x-action-button>
                </div>
            </x-filter-card>
        @else
            <x-dashboard-card title="Class allocation status" subtitle="Allocations of teachers to class records across primary and secondary divisions." icon="classes" accent="blue" />
        @endif

    <!-- Main Body Panel -->
    <div class="space-y-6">
        <!-- Portal Tab Contents -->
        <div class="space-y-6">
            <!-- 1. DIRECTORY VIEW -->
                @if ($activeStaffView === 'directory')
                    <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)]">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-5">
                            <div>
                                <h2 class="display-font text-xl font-bold text-slate-900 leading-snug">Staff directory by department</h2>
                                <p class="text-xs font-semibold text-slate-400 mt-1">Manage departmental groups, active assignments, and payroll accounts.</p>
                            </div>
                            <div class="bg-blue-50 text-blue-700 px-4 py-3 rounded-2xl border border-blue-100 shrink-0 text-center">
                                <div class="text-[9px] font-extrabold uppercase tracking-wider text-blue-500">Active Count</div>
                                <div class="display-font text-2xl font-black mt-0.5">{{ $staff->count() }}</div>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @forelse ($departmentDirectory as $dept)
                                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4 hover:border-blue-200 transition duration-200">
                                    <div class="font-bold text-slate-800 text-sm leading-tight">{{ $dept['name'] }}</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-500">{{ $dept['count'] }} member{{ $dept['count'] === 1 ? '' : 's' }}</div>
                                </div>
                            @empty
                                <div class="col-span-3 rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-400 text-center font-semibold">No records found.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="space-y-6">
                        @forelse ($staffGroups as $deptName => $profiles)
                            <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)] overflow-hidden">
                                <div class="border-b border-slate-100 pb-4 mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h3 class="display-font text-base font-extrabold text-slate-900">{{ $deptName }}</h3>
                                        <p class="text-xs font-semibold text-slate-450 mt-1">{{ $profiles->count() }} registered staff member{{ $profiles->count() === 1 ? '' : 's' }}</p>
                                    </div>
                                    <div class="flex justify-end">
                                        <x-status-badge status="Active" />
                                    </div>
                                </div>

                                <div class="desktop-only-table">
                                    <x-data-table :headers="['Staff Member', 'Portal Role', 'Designation / Staff ID', 'Salary setup', 'Actions']" :minWidth="'1100px'">
                                        @foreach ($profiles as $profile)
                                            @php
                                                $staffProfileUrl = route('admin.staff.show', ['staffProfile' => $profile] + array_filter(['search' => $search, 'department' => $departmentFilter, 'view' => $activeStaffView]));
                                                $staffPreview = [
                                                    'type' => 'staff',
                                                    'title' => $profile->user->fullName(),
                                                    'subtitle' => $profile->user->roleLabel().' - '.($profile->department ?: 'General'),
                                                    'avatar' => substr($profile->user->first_name, 0, 1).substr($profile->user->last_name, 0, 1),
                                                    'profileUrl' => $staffProfileUrl,
                                                    'ctaLabel' => 'View Full Profile',
                                                    'fields' => [
                                                        ['label' => 'Employee ID', 'value' => $profile->employee_no ?: 'Not set'],
                                                        ['label' => 'Portal Role', 'value' => $profile->user->roleLabel()],
                                                        ['label' => 'Department', 'value' => $profile->department ?: 'General'],
                                                        ['label' => 'Designation', 'value' => $profile->designation ?: 'Not assigned'],
                                                        ['label' => 'Email', 'value' => $profile->user->email ?: 'No email'],
                                                        ['label' => 'Salary Setup', 'value' => $profile->salary ? 'NGN '.number_format((float) $profile->salary, 2) : 'Not configured'],
                                                    ],
                                                ];
                                            @endphp
                                            <tr class="hover:bg-slate-50/80 transition duration-150">
                                                <td class="w-[35%]">
                                                    <div class="table-person">
                                                        <div class="table-avatar">
                                                            {{ $staffPreview['avatar'] }}
                                                        </div>
                                                        <div class="table-person-text">
                                                            <strong>{{ $profile->user->fullName() }}</strong>
                                                            <span>{{ $profile->user->email ?: 'No email' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="w-[12%] px-6 py-4 text-xs font-semibold text-slate-700 uppercase whitespace-nowrap">
                                                    {{ $profile->user->roleLabel() }}
                                                </td>
                                                <td class="w-[23%] px-6 py-4 text-xs">
                                                    <div class="font-semibold text-slate-800">{{ $profile->designation ?: 'Staff Designation' }}</div>
                                                    <div class="text-slate-400 mt-0.5 font-mono">{{ $profile->employee_no }}</div>
                                                </td>
                                                <td class="w-[15%] px-6 py-4 text-xs font-bold text-slate-700 whitespace-nowrap">
                                                    {{ $profile->salary ? 'NGN '.number_format((float) $profile->salary, 2) : 'Not configured' }}
                                                </td>
                                                <td class="w-[150px] px-6 py-4 text-right whitespace-nowrap">
                                                    <button type="button" class="table-view-btn" data-preview='@json($staffPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </x-data-table>
                                </div>

                                <!-- Mobile View for Staff Roster -->
                                <div class="mobile-record-list mt-4 space-y-3 md:hidden">
                                    @foreach ($profiles as $profile)
                                        @php
                                            $staffProfileUrl = route('admin.staff.show', ['staffProfile' => $profile] + array_filter(['search' => $search, 'department' => $departmentFilter, 'view' => $activeStaffView]));
                                            $staffPreview = [
                                                'type' => 'staff',
                                                'title' => $profile->user->fullName(),
                                                'subtitle' => $profile->user->roleLabel().' - '.($profile->department ?: 'General'),
                                                'avatar' => substr($profile->user->first_name, 0, 1).substr($profile->user->last_name, 0, 1),
                                                'profileUrl' => $staffProfileUrl,
                                                'ctaLabel' => 'View Full Profile',
                                                'fields' => [
                                                    ['label' => 'Employee ID', 'value' => $profile->employee_no ?: 'Not set'],
                                                    ['label' => 'Portal Role', 'value' => $profile->user->roleLabel()],
                                                    ['label' => 'Department', 'value' => $profile->department ?: 'General'],
                                                    ['label' => 'Designation', 'value' => $profile->designation ?: 'Not assigned'],
                                                    ['label' => 'Email', 'value' => $profile->user->email ?: 'No email'],
                                                    ['label' => 'Salary Setup', 'value' => $profile->salary ? 'NGN '.number_format((float) $profile->salary, 2) : 'Not configured'],
                                                ],
                                            ];
                                        @endphp
                                        <article class="mobile-record-card">
                                            <div class="flex items-start justify-between border-b border-slate-100 pb-3 mb-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="table-avatar !h-9 !w-9 !text-xs">
                                                        {{ substr($profile->user->first_name, 0, 1) }}{{ substr($profile->user->last_name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="mobile-record-title">{{ $profile->user->fullName() }}</div>
                                                        <div class="text-[10px] text-slate-500 font-semibold mt-0.5">{{ $profile->user->roleLabel() }}</div>
                                                    </div>
                                                </div>
                                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-705">{{ $profile->employee_no ?: 'No ID' }}</span>
                                            </div>

                                            <div class="mobile-record-grid">
                                                <div class="mobile-record-item">
                                                    <span class="mobile-record-label">Designation</span>
                                                    <span class="mobile-record-value font-bold text-slate-800">{{ $profile->designation ?: 'Not assigned' }}</span>
                                                </div>
                                                <div class="mobile-record-item">
                                                    <span class="mobile-record-label">Salary Setup</span>
                                                    <span class="mobile-record-value font-semibold text-slate-800">{{ $profile->salary ? 'NGN '.number_format((float) $profile->salary, 2) : 'Not configured' }}</span>
                                                </div>
                                            </div>

                                            <div class="mobile-action-row border-t border-slate-100 pt-3 mt-4">
                                                <button
                                                    type="button"
                                                    class="table-view-btn w-full !text-center !py-2 !rounded-xl !bg-slate-100 hover:!bg-slate-200 text-slate-700 font-bold transition"
                                                    data-preview='@json($staffPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'
                                                >
                                                    Quick View
                                                </button>
                                                <a
                                                    href="{{ $staffProfileUrl }}"
                                                    class="theme-button w-full !min-h-[2.45rem] !py-2 !px-4 !text-xs font-bold text-center !rounded-xl"
                                                >
                                                    Full Profile
                                                </a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <x-empty-state title="No staff records" description="Department directory returned no staff profiles matching the search queries." icon="staff" />
                        @endforelse
                    </div>

                <!-- 2. PAYROLL DESK VIEW -->
                @elseif ($activeStaffView === 'payroll')
                    <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)] mb-6">
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="display-font text-xl font-bold text-slate-900 leading-snug">Payroll administration desk</h2>
                                <p class="text-xs font-semibold text-slate-400 mt-1">Summary billing, salaries setup, and monthly school budget metrics.</p>
                            </div>
                            <x-status-badge status="Active" />
                        </div>
                    </div>

                    <div class="space-y-6">
                        @forelse ($payrollRows as $row)
                            <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)] overflow-hidden">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between border-b border-slate-100 pb-4 mb-4">
                                    <div>
                                        <h3 class="display-font text-base font-extrabold text-slate-900 leading-snug">{{ $row['department'] }}</h3>
                                        <p class="text-xs font-semibold text-slate-400 mt-1">{{ $row['staff_count'] }} staff members &bull; {{ $row['staff_with_salary'] }} payroll setup</p>
                                    </div>
                                    <div class="text-left sm:text-right shrink-0">
                                        <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Monthly gross total</div>
                                        <div class="display-font text-lg font-black text-slate-900 mt-0.5">NGN {{ number_format((float) $row['monthly_total'], 2) }}</div>
                                        <div class="text-[10px] font-semibold text-slate-500 mt-0.5">Avg: NGN {{ number_format((float) $row['average_salary'], 2) }}</div>
                                    </div>
                                </div>

                                <div class="desktop-only-table">
                                    <x-data-table :headers="['Staff Member', 'Role', 'Designation', 'Gross Salary', 'Actions']" :minWidth="'1100px'">
                                        @foreach ($row['profiles'] as $profile)
                                            @php
                                                $staffProfileUrl = route('admin.staff.show', ['staffProfile' => $profile] + array_filter(['search' => $search, 'department' => $departmentFilter, 'view' => $activeStaffView]));
                                                $payrollPreview = [
                                                    'type' => 'staff',
                                                    'title' => $profile->user->fullName(),
                                                    'subtitle' => 'Payroll Record - '.($row['department'] ?: 'General'),
                                                    'avatar' => substr($profile->user->first_name, 0, 1).substr($profile->user->last_name, 0, 1),
                                                    'profileUrl' => $staffProfileUrl,
                                                    'ctaLabel' => 'View Full Profile',
                                                    'fields' => [
                                                        ['label' => 'Employee ID', 'value' => $profile->employee_no ?: 'Not set'],
                                                        ['label' => 'Role', 'value' => $profile->user->roleLabel()],
                                                        ['label' => 'Department', 'value' => $row['department'] ?: 'General'],
                                                        ['label' => 'Designation', 'value' => $profile->designation ?: 'Not assigned'],
                                                        ['label' => 'Gross Salary', 'value' => $profile->salary ? 'NGN '.number_format((float) $profile->salary, 2) : 'Not configured'],
                                                    ],
                                                ];
                                            @endphp
                                            <tr class="hover:bg-slate-50/80 transition duration-150">
                                                <td class="w-[35%]">
                                                    <div class="table-person">
                                                        <div class="table-avatar">{{ $payrollPreview['avatar'] }}</div>
                                                        <div class="table-person-text">
                                                            <strong>{{ $profile->user->fullName() }}</strong>
                                                            <span>{{ $profile->employee_no ?: 'No employee ID' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="w-[12%] px-6 py-3.5 text-xs font-semibold text-slate-600 uppercase whitespace-nowrap">{{ $profile->user->roleLabel() }}</td>
                                                <td class="w-[23%] px-6 py-3.5 text-xs font-semibold text-slate-500 whitespace-nowrap">{{ $profile->designation ?: 'Not assigned' }}</td>
                                                <td class="w-[15%] px-6 py-3.5 text-xs font-extrabold text-slate-800 whitespace-nowrap">
                                                    {{ $profile->salary ? 'NGN '.number_format((float) $profile->salary, 2) : 'Not configured' }}
                                                </td>
                                                <td class="w-[150px] px-6 py-3.5 text-right whitespace-nowrap">
                                                    <button type="button" class="table-view-btn" data-preview='@json($payrollPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </x-data-table>
                                </div>

                                <!-- Mobile View for Payroll Staff -->
                                <div class="mobile-record-list mt-4 space-y-3 md:hidden">
                                    @foreach ($row['profiles'] as $profile)
                                        @php
                                            $staffProfileUrl = route('admin.staff.show', ['staffProfile' => $profile] + array_filter(['search' => $search, 'department' => $departmentFilter, 'view' => $activeStaffView]));
                                            $payrollPreview = [
                                                'type' => 'staff',
                                                'title' => $profile->user->fullName(),
                                                'subtitle' => 'Payroll Record - '.($row['department'] ?: 'General'),
                                                'avatar' => substr($profile->user->first_name, 0, 1).substr($profile->user->last_name, 0, 1),
                                                'profileUrl' => $staffProfileUrl,
                                                'ctaLabel' => 'View Full Profile',
                                                'fields' => [
                                                    ['label' => 'Employee ID', 'value' => $profile->employee_no ?: 'Not set'],
                                                    ['label' => 'Role', 'value' => $profile->user->roleLabel()],
                                                    ['label' => 'Department', 'value' => $row['department'] ?: 'General'],
                                                    ['label' => 'Designation', 'value' => $profile->designation ?: 'Not assigned'],
                                                    ['label' => 'Gross Salary', 'value' => $profile->salary ? 'NGN '.number_format((float) $profile->salary, 2) : 'Not configured'],
                                                ],
                                            ];
                                        @endphp
                                        <article class="mobile-record-card">
                                            <div class="flex items-start justify-between border-b border-slate-100 pb-3 mb-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="table-avatar !h-9 !w-9 !text-xs">
                                                        {{ substr($profile->user->first_name, 0, 1) }}{{ substr($profile->user->last_name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="mobile-record-title">{{ $profile->user->fullName() }}</div>
                                                        <div class="text-[10px] text-slate-500 font-semibold mt-0.5">{{ $profile->user->roleLabel() }}</div>
                                                    </div>
                                                </div>
                                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-705">{{ $profile->employee_no ?: 'No ID' }}</span>
                                            </div>

                                            <div class="mobile-record-grid">
                                                <div class="mobile-record-item">
                                                    <span class="mobile-record-label">Designation</span>
                                                    <span class="mobile-record-value font-bold text-slate-800">{{ $profile->designation ?: 'Not assigned' }}</span>
                                                </div>
                                                <div class="mobile-record-item">
                                                    <span class="mobile-record-label">Gross Salary</span>
                                                    <span class="mobile-record-value font-semibold text-slate-800">{{ $profile->salary ? 'NGN '.number_format((float) $profile->salary, 2) : 'Not configured' }}</span>
                                                </div>
                                            </div>

                                            <div class="mobile-action-row border-t border-slate-100 pt-3 mt-4">
                                                <button
                                                    type="button"
                                                    class="table-view-btn w-full !text-center !py-2 !rounded-xl !bg-slate-100 hover:!bg-slate-200 text-slate-700 font-bold transition"
                                                    data-preview='@json($payrollPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'
                                                >
                                                    Quick View
                                                </button>
                                                <a
                                                    href="{{ $staffProfileUrl }}"
                                                    class="theme-button w-full !min-h-[2.45rem] !py-2 !px-4 !text-xs font-bold text-center !rounded-xl"
                                                >
                                                    Full Profile
                                                </a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <x-empty-state title="No payroll data found" description="Payroll search query returned no active salary configurations." icon="finance" />
                        @endforelse
                    </div>

                <!-- 3. CLASS ALLOCATION VIEW -->
                @elseif ($activeStaffView === 'class-allocation')
                    <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25_rgba(15,23,42,0.08)] mb-6">
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="display-font text-xl font-bold text-slate-900 leading-snug">Class teacher allocation</h2>
                                <p class="text-xs font-semibold text-slate-400 mt-1">Review assigned class teachers, capacities, and classrooms.</p>
                            </div>
                            <x-action-button variant="secondary" :href="route('admin.academics', ['section' => 'class-setup'])">Class Setup</x-action-button>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($classAllocationRows as $row)
                            <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-5 shadow-[0_10px_25_rgba(15,23,42,0.08)] hover:border-[#fbbf24] hover:shadow-[0_16px_35px_rgba(15,23,42,0.14)] hover:-translate-y-0.5 transition-all duration-200">
                                <div class="display-font text-lg font-bold text-slate-900 leading-snug">{{ $row['class']->display_name }}</div>
                                <div class="mt-1 text-xs font-semibold text-slate-400">Classroom: Room {{ $row['class']->room ?: 'Not set' }} &bull; Max Capacity: {{ $row['class']->capacity ?: 'Not configured' }}</div>
                                
                                <div class="mt-4 p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-2">Assigned Class Teacher</span>
                                    @if ($row['teacher'])
                                        <div class="font-bold text-slate-800 text-sm leading-tight">{{ $row['teacher']->fullName() }}</div>
                                        <div class="text-xs font-semibold text-slate-500 mt-1">{{ $row['teacher']->roleLabel() }} &bull; Dept: {{ $row['department'] ?: 'No department' }}</div>
                                        @if($row['designation'])
                                            <div class="text-[11px] font-semibold text-blue-600 mt-0.5">{{ $row['designation'] }}</div>
                                        @endif
                                    @else
                                        <div class="text-xs font-semibold text-rose-500 flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            No class teacher allocated yet.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    <x-modal name="register-staff-modal" :show="$errors->any()" maxWidth="2xl">
        <x-form-card 
            :action="route('admin.staff.store')" 
            method="POST" 
            enctype="multipart/form-data" 
            title="Register staff member" 
            description="Create staff profiles separate from student records. Maintain active departments, roles, qualifications, and payroll salary settings." 
            class="w-full"
            x-data="{
                firstName: '',
                middleName: '',
                lastName: '',
                employeeNo: '',
                password: '',
                generate() {
                    const stamp = String(Date.now()).slice(-6);
                    if (!this.employeeNo) this.employeeNo = `STF-${stamp}`;
                    if (!this.password) this.password = `${(this.firstName || 'STF').slice(0,3).toUpperCase()}@${stamp.slice(-5)}`;
                },
                regeneratePassword() {
                    const stamp = String(Date.now()).slice(-5);
                    this.password = `${(this.firstName || 'STF').slice(0,3).toUpperCase()}@${stamp}`;
                },
                regenerateId() {
                    this.employeeNo = `STF-${String(Date.now()).slice(-6)}`;
                }
            }">
            
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">First Name <span class="text-rose-500">*</span></label>
                    <input name="first_name" x-model="firstName" @input="generate()" placeholder="e.g. Samuel" class="theme-input w-full" required />
                    <x-input-error :messages="$errors->get('first_name')" />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Middle Name</label>
                    <input name="middle_name" x-model="middleName" placeholder="e.g. Kolade" class="theme-input w-full" />
                    <x-input-error :messages="$errors->get('middle_name')" />
                </div>
                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-bold text-slate-700">Last Name <span class="text-rose-500">*</span></label>
                    <input name="last_name" x-model="lastName" @input="generate()" placeholder="e.g. Adeniji" class="theme-input w-full" required />
                    <x-input-error :messages="$errors->get('last_name')" />
                </div>
                
                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-bold text-slate-700">Staff Email Address <span class="text-rose-500">*</span></label>
                    <input name="email" type="email" placeholder="e.g. teacher@belovedschools.com" class="theme-input w-full" required />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-bold text-slate-700">Staff Phone Number</label>
                    <div class="phone-field w-full relative" x-data="contactField({ target: 'staff-phone-create-modal' })">
                        <input id="staff-phone-create-modal" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="Phone" class="theme-input w-full" />
                        <button type="button" @click="pick()" :disabled="!supported" class="absolute right-2 top-2 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded px-2.5 py-1 text-xs font-bold transition disabled:opacity-50" title="Pick contact">Pick</button>
                    </div>
                    <x-input-error :messages="$errors->get('phone')" />
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-bold text-slate-700">Assign Portal Role <span class="text-rose-500">*</span></label>
                    <select name="role" class="theme-input w-full" required>
                        <option value="teacher">Teacher</option>
                        <option value="principal">Principal</option>
                        <option value="accountant">Accountant</option>
                        <option value="admin">Admin</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" />
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="text-xs font-bold text-slate-700">Employee Staff ID Number</label>
                    <div class="grid gap-2 grid-cols-[1fr,auto]">
                        <input name="employee_no" x-model="employeeNo" @focus="generate()" placeholder="Staff employee ID" class="theme-input" />
                        <button type="button" @click="regenerateId()" class="bg-slate-100 hover:bg-slate-200 border border-[#c8d6ea] text-slate-750 px-4 py-2.5 rounded-[12px] font-bold text-xs uppercase transition active:scale-[0.98]">Regenerate</button>
                    </div>
                    <x-input-error :messages="$errors->get('employee_no')" />
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="text-xs font-bold text-slate-700">Temporary Password</label>
                    <div class="grid gap-2 grid-cols-[1fr,auto]">
                        <input name="password" x-model="password" @focus="generate()" placeholder="Temporary password" class="theme-input" />
                        <button type="button" @click="regeneratePassword()" class="bg-slate-100 hover:bg-slate-200 border border-[#c8d6ea] text-slate-750 px-4 py-2.5 rounded-[12px] font-bold text-xs uppercase transition active:scale-[0.98]">Regenerate</button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Department</label>
                    <input name="department" placeholder="e.g. Academics, Finance" class="theme-input w-full" />
                    <x-input-error :messages="$errors->get('department')" />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Designation</label>
                    <input name="designation" placeholder="e.g. Head of Science" class="theme-input w-full" />
                    <x-input-error :messages="$errors->get('designation')" />
                </div>
                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-bold text-slate-700">Qualification</label>
                    <input name="qualification" placeholder="e.g. B.Ed, M.Sc Mathematics" class="theme-input w-full" />
                    <x-input-error :messages="$errors->get('qualification')" />
                </div>
                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-bold text-slate-700">Date of Hire</label>
                    <input name="hire_date" type="date" class="theme-input w-full" />
                    <x-input-error :messages="$errors->get('hire_date')" />
                </div>
                
                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-bold text-slate-700">Monthly Gross Salary (NGN)</label>
                    <input name="salary" type="number" step="0.01" min="0" placeholder="e.g. 150000.00" class="theme-input w-full" />
                    <x-input-error :messages="$errors->get('salary')" />
                </div>

                <!-- Photo Upload -->
                <div class="md:col-span-2 space-y-1.5 mt-2">
                    <label class="text-xs font-bold text-slate-700">Staff Passport Photo</label>
                    <label class="flex flex-col items-center justify-center border-2 border-dashed border-[#c8d6ea] hover:border-[#fbbf24] bg-slate-50/50 hover:bg-slate-50/20 px-6 py-5 rounded-[18px] cursor-pointer transition duration-200">
                        <x-app-icon name="avatar" class="w-8 h-8 text-slate-400 mb-2" />
                        <span class="text-xs font-bold text-slate-700">Click to upload image file</span>
                        <span class="text-[10px] font-semibold text-slate-400 mt-1">PNG, JPG or JPEG up to 2MB</span>
                        <input type="file" name="passport_photo" accept="image/*" class="hidden">
                    </label>
                </div>
            </div>

            <x-slot name="actions">
                <x-action-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'register-staff-modal')">Cancel</x-action-button>
                <x-action-button type="submit" variant="success">Create Staff Profile</x-action-button>
            </x-slot>
        </x-form-card>
    </x-modal>

    <x-entity-preview-modal />
</x-app-layout>
