<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Administration</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">Staff management</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">This workspace now includes school-office views for payroll and class allocation in addition to the staff directory.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
                <div class="rounded-[1.5rem] border border-white/30 bg-white/70 px-4 py-4 text-center shadow-sm backdrop-blur">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Staff</div>
                    <div class="display-font mt-2 text-2xl font-bold text-slate-950">{{ $staffWorkspaceStats['staff_count'] }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-white/30 bg-white/70 px-4 py-4 text-center shadow-sm backdrop-blur">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Active</div>
                    <div class="display-font mt-2 text-2xl font-bold text-slate-950">{{ $staffWorkspaceStats['active_count'] }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-white/30 bg-white/70 px-4 py-4 text-center shadow-sm backdrop-blur">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">On Payroll</div>
                    <div class="display-font mt-2 text-2xl font-bold text-slate-950">{{ $staffWorkspaceStats['salary_count'] }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-white/30 bg-white/70 px-4 py-4 text-center shadow-sm backdrop-blur lg:col-span-2">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Monthly Gross Payroll</div>
                    <div class="display-font mt-2 text-2xl font-bold text-slate-950">NGN {{ number_format((float) $staffWorkspaceStats['monthly_total'], 2) }}</div>
                </div>
                <div class="rounded-[1.5rem] border border-white/30 bg-white/70 px-4 py-4 text-center shadow-sm backdrop-blur">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Class Teachers</div>
                    <div class="display-font mt-2 text-2xl font-bold text-slate-950">{{ $staffWorkspaceStats['class_teachers'] }}</div>
                </div>
            </div>
        </div>
    </x-slot>

    @if (session('generated_credentials'))
        @php($credentials = session('generated_credentials'))
        <div class="mb-8 rounded-[2rem] border border-emerald-200 bg-emerald-50 px-6 py-5 text-sm text-emerald-900">
            <div class="font-semibold">Generated {{ $credentials['audience'] }} credentials</div>
            <div class="mt-2">{{ $credentials['name'] }} | Login ID: {{ $credentials['identifier'] }} | Email: {{ $credentials['email'] }} | Password: {{ $credentials['password'] }}</div>
        </div>
    @endif

    <div class="grid gap-8">
        <x-section-nav :items="$staffOfficeNavItems" :active="$activeStaffView" />

        <section class="section-card">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">
                        @if ($activeStaffView === 'payroll')
                            Payroll overview
                        @elseif ($activeStaffView === 'class-allocation')
                            Class allocation
                        @else
                            Staff directory
                        @endif
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">Filter staff by name, email, staff ID, role, designation, or department.</p>
                </div>
                @if ($activeStaffView !== 'class-allocation')
                    <form method="GET" action="{{ route('admin.staff.index') }}" class="flex w-full max-w-3xl flex-col gap-3 lg:flex-row">
                        <input type="hidden" name="view" value="{{ $activeStaffView }}" />
                        <input name="search" value="{{ $search }}" placeholder="Search staff records" class="theme-input flex-1" />
                        <select name="department" class="theme-input lg:w-56">
                            <option value="">All departments</option>
                            @foreach ($departmentOptions as $department)
                                <option value="{{ $department }}" @selected($departmentFilter === $department)>{{ $department }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="theme-button">Search</button>
                        <a href="{{ route('admin.staff.index', ['view' => $activeStaffView]) }}" class="theme-button-secondary text-center">Reset</a>
                    </form>
                @endif
            </div>
        </section>

        <div class="grid gap-8 xl:grid-cols-[0.9fr,1.1fr]">
            <section
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
                }"
                class="section-card order-2 xl:order-1"
            >
                <h2 class="display-font text-2xl font-bold text-slate-950">Register staff</h2>
                <p class="mt-2 text-sm text-slate-500">Create staff records separately from students and keep role, department, and payroll data organized.</p>

                <form method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <input name="first_name" x-model="firstName" @input="generate()" placeholder="First name" class="theme-input" required />
                        <input name="middle_name" x-model="middleName" placeholder="Middle name" class="theme-input" />
                        <input name="last_name" x-model="lastName" @input="generate()" placeholder="Last name" class="theme-input" required />
                        <input name="email" type="email" placeholder="Staff email" class="theme-input" required />
                        <div class="phone-field" x-data="contactField({ target: 'staff-phone-create' })">
                            <input id="staff-phone-create" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="Phone" class="theme-input" />
                            <button type="button" @click="pick()" :disabled="!supported" class="contact-picker-button" title="Pick contact">Pick</button>
                        </div>
                        <select name="role" class="theme-input" required>
                            <option value="teacher">Teacher</option>
                            <option value="principal">Principal</option>
                            <option value="accountant">Accountant</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div class="md:col-span-2 grid gap-3 md:grid-cols-[1fr,auto]">
                            <input name="employee_no" x-model="employeeNo" @focus="generate()" placeholder="Staff ID or application number" class="theme-input" />
                            <button type="button" @click="regenerateId()" class="theme-button-secondary">Regenerate ID</button>
                        </div>
                        <div class="md:col-span-2 grid gap-3 md:grid-cols-[1fr,auto]">
                            <input name="password" x-model="password" @focus="generate()" placeholder="Temporary password" class="theme-input" />
                            <button type="button" @click="regeneratePassword()" class="theme-button-secondary">Regenerate Password</button>
                        </div>
                        <input name="department" placeholder="Department" class="theme-input" />
                        <input name="designation" placeholder="Designation" class="theme-input" />
                        <input name="qualification" placeholder="Qualification" class="theme-input" />
                        <input name="hire_date" type="date" class="theme-input" />
                        <input name="salary" type="number" step="0.01" min="0" placeholder="Monthly salary" class="theme-input md:col-span-2" />
                        <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600 md:col-span-2">
                            <span class="mb-2 block font-semibold text-slate-900">Passport photo</span>
                            <input type="file" name="passport_photo" accept="image/*" class="block w-full text-sm">
                        </label>
                    </div>
                    <button type="submit" class="theme-button">Create staff</button>
                </form>
            </section>

            <section class="section-card order-1 xl:order-2">
                @if ($activeStaffView === 'directory')
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="display-font text-2xl font-bold text-slate-950">Staff directory by department</h2>
                            <p class="mt-2 text-sm text-slate-500">All staff records are grouped by department. Open any record to edit role, class teacher status, and payroll information.</p>
                        </div>
                        <div class="rounded-3xl bg-slate-50 px-5 py-4 text-left sm:text-right">
                            <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Visible staff</div>
                            <div class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $staff->count() }}</div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @forelse ($departmentDirectory as $department)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="font-semibold text-slate-900">{{ $department['name'] }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $department['count'] }} staff member{{ $department['count'] === 1 ? '' : 's' }}</div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No staff records match this search yet.</div>
                        @endforelse
                    </div>

                    <div class="mt-8 space-y-6">
                        @forelse ($staffGroups as $department => $profiles)
                            <div class="rounded-[2rem] border border-slate-200 p-5">
                                <div>
                                    <h3 class="display-font text-xl font-bold text-slate-950">{{ $department }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ $profiles->count() }} registered staff member{{ $profiles->count() === 1 ? '' : 's' }}</p>
                                </div>

                                <div class="mt-5 desktop-table table-wrap">
                                    <table class="min-w-full text-left text-sm">
                                        <thead class="text-slate-500">
                                            <tr>
                                                <th class="pb-3">Staff</th>
                                                <th class="pb-3">Role</th>
                                                <th class="pb-3">Staff ID</th>
                                                <th class="pb-3">Designation</th>
                                                <th class="pb-3">Salary</th>
                                                <th class="pb-3">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200">
                                            @foreach ($profiles as $profile)
                                                <tr>
                                                    <td class="py-4">
                                                        <div class="font-semibold text-slate-900">{{ $profile->user->fullName() }}</div>
                                                        <div class="text-xs text-slate-500">{{ $profile->user->email }}</div>
                                                    </td>
                                                    <td class="py-4 text-slate-600">
                                                        {{ $profile->user->roleLabel() }}
                                                        @if ($profile->user->managedClasses->isNotEmpty())
                                                            <div class="mt-1 text-xs text-slate-500">Class teacher: {{ $profile->user->managedClasses->pluck('display_name')->join(', ') }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="py-4 text-slate-600">{{ $profile->employee_no }}</td>
                                                    <td class="py-4 text-slate-600">{{ $profile->designation ?: 'Not assigned' }}</td>
                                                    <td class="py-4 text-slate-600">{{ $profile->salary ? 'NGN '.number_format((float) $profile->salary, 2) : 'Not set' }}</td>
                                                    <td class="py-4">
                                                        <a href="{{ route('admin.staff.show', ['staffProfile' => $profile] + array_filter(['search' => $search, 'department' => $departmentFilter, 'view' => $activeStaffView])) }}" class="theme-button-secondary">View / Edit</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-[2rem] border border-dashed border-slate-300 px-6 py-8 text-sm text-slate-500">No staff found for this search.</div>
                        @endforelse
                    </div>
                @elseif ($activeStaffView === 'payroll')
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="display-font text-2xl font-bold text-slate-950">Payroll desk</h2>
                            <p class="mt-2 text-sm text-slate-500">Salary setup and department-level payroll totals now sit inside staff management the way a school office would expect.</p>
                        </div>
                        <div class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">{{ $payrollRows->count() }} department{{ $payrollRows->count() === 1 ? '' : 's' }}</div>
                    </div>

                    <div class="mt-6 space-y-5">
                        @forelse ($payrollRows as $row)
                            <article class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $row['department'] }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $row['staff_count'] }} staff member{{ $row['staff_count'] === 1 ? '' : 's' }} | {{ $row['staff_with_salary'] }} with salary setup</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Monthly total</div>
                                        <div class="display-font mt-2 text-2xl font-bold text-slate-950">NGN {{ number_format((float) $row['monthly_total'], 2) }}</div>
                                        <div class="mt-1 text-sm text-slate-500">Average NGN {{ number_format((float) $row['average_salary'], 2) }}</div>
                                    </div>
                                </div>

                                <div class="mt-5 desktop-table table-wrap">
                                    <table class="min-w-full text-left text-sm">
                                        <thead class="text-slate-500">
                                            <tr>
                                                <th class="pb-3">Staff</th>
                                                <th class="pb-3">Role</th>
                                                <th class="pb-3">Designation</th>
                                                <th class="pb-3">Salary</th>
                                                <th class="pb-3">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200">
                                            @foreach ($row['profiles'] as $profile)
                                                <tr>
                                                    <td class="py-4">
                                                        <div class="font-semibold text-slate-900">{{ $profile->user->fullName() }}</div>
                                                        <div class="text-xs text-slate-500">{{ $profile->employee_no }}</div>
                                                    </td>
                                                    <td class="py-4 text-slate-600">{{ $profile->user->roleLabel() }}</td>
                                                    <td class="py-4 text-slate-600">{{ $profile->designation ?: 'Not assigned' }}</td>
                                                    <td class="py-4 text-slate-600">{{ $profile->salary ? 'NGN '.number_format((float) $profile->salary, 2) : 'Not set' }}</td>
                                                    <td class="py-4">
                                                        <a href="{{ route('admin.staff.show', ['staffProfile' => $profile] + array_filter(['search' => $search, 'department' => $departmentFilter, 'view' => $activeStaffView])) }}" class="theme-button-secondary">Open profile</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[2rem] border border-dashed border-slate-300 px-6 py-8 text-sm text-slate-500">No payroll records match this search.</div>
                        @endforelse
                    </div>
                @elseif ($activeStaffView === 'class-allocation')
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="display-font text-2xl font-bold text-slate-950">Class teacher allocation</h2>
                            <p class="mt-2 text-sm text-slate-500">This gives you a school-style class allocation board so every class can be reviewed alongside its assigned teacher.</p>
                        </div>
                        <a href="{{ route('admin.academics', ['section' => 'class-setup']) }}" class="theme-button-secondary">Open class setup</a>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @foreach ($classAllocationRows as $row)
                            <article class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                                <div class="display-font text-xl font-bold text-slate-950">{{ $row['class']->display_name }}</div>
                                <div class="mt-3 text-sm text-slate-600">Room {{ $row['class']->room ?: 'Not set' }} | Capacity {{ $row['class']->capacity ?: 'Not set' }}</div>
                                <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Assigned teacher</div>
                                    @if ($row['teacher'])
                                        <div class="mt-2 font-semibold text-slate-900">{{ $row['teacher']->fullName() }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $row['teacher']->roleLabel() }} | {{ $row['department'] ?: 'No department' }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $row['designation'] ?: 'No designation' }}</div>
                                    @else
                                        <div class="mt-2 text-sm text-slate-600">No class teacher assigned yet.</div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
