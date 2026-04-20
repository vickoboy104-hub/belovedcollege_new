<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Administration</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">Staff management</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.people') }}" class="theme-button-secondary">People hub</a>
                <a href="{{ route('admin.students.index') }}" class="theme-button-secondary">Go to students</a>
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

    <section class="section-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Search staff</h2>
                <p class="mt-2 text-sm text-slate-500">Filter staff by name, email, staff ID, role, or department.</p>
            </div>
            <form method="GET" action="{{ route('admin.staff.index') }}" class="flex w-full max-w-3xl flex-col gap-3 lg:flex-row">
                <input name="search" value="{{ $search }}" placeholder="Search by name, email, role, or staff ID" class="theme-input flex-1" />
                <select name="department" class="theme-input lg:w-56">
                    <option value="">All departments</option>
                    @foreach ($departmentOptions as $department)
                        <option value="{{ $department }}" @selected($departmentFilter === $department)>{{ $department }}</option>
                    @endforeach
                </select>
                <button type="submit" class="theme-button">Search</button>
                <a href="{{ route('admin.staff.index') }}" class="theme-button-secondary text-center">Reset</a>
            </form>
        </div>
    </section>

    <div class="mt-8 grid gap-8 xl:grid-cols-[0.9fr,1.1fr]">
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
            class="section-card"
        >
            <h2 class="display-font text-2xl font-bold text-slate-950">Register staff</h2>
            <p class="mt-2 text-sm text-slate-500">Create staff records separately from students and manage role-based access cleanly.</p>

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
                    <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600 md:col-span-2">
                        <span class="mb-2 block font-semibold text-slate-900">Passport photo</span>
                        <input type="file" name="passport_photo" accept="image/*" class="block w-full text-sm">
                    </label>
                </div>
                <button type="submit" class="theme-button">Create staff</button>
            </form>
        </section>

        <section class="section-card">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">Staff directory by department</h2>
                    <p class="mt-2 text-sm text-slate-500">All staff records are grouped by department. Open any record to edit role and profile data.</p>
                </div>
                <div class="rounded-3xl bg-slate-50 px-5 py-4 text-right">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Total staff</div>
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
                                        <th class="pb-3">Phone</th>
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
                                            <td class="py-4 text-slate-600">{{ $profile->user->phone ?: 'No phone' }}</td>
                                            <td class="py-4">
                                                <a href="{{ route('admin.staff.show', ['staffProfile' => $profile] + array_filter(['search' => $search, 'department' => $departmentFilter])) }}" class="theme-button-secondary">View / Edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5 mobile-record-list">
                            @foreach ($profiles as $profile)
                                <article class="mobile-record-card">
                                    <div class="mobile-record-title">{{ $profile->user->fullName() }}</div>
                                    <div class="mobile-record-subtitle">{{ $profile->user->email }}</div>
                                    <div class="mobile-record-grid">
                                        <div class="mobile-record-item">
                                            <div class="mobile-record-label">Role</div>
                                            <div class="mobile-record-value">
                                                {{ $profile->user->roleLabel() }}
                                                @if ($profile->user->managedClasses->isNotEmpty())
                                                    <div class="mt-1 text-xs text-slate-500">Class teacher: {{ $profile->user->managedClasses->pluck('display_name')->join(', ') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mobile-record-item">
                                            <div class="mobile-record-label">Staff ID</div>
                                            <div class="mobile-record-value">{{ $profile->employee_no }}</div>
                                        </div>
                                        <div class="mobile-record-item">
                                            <div class="mobile-record-label">Designation</div>
                                            <div class="mobile-record-value">{{ $profile->designation ?: 'Not assigned' }}</div>
                                        </div>
                                        <div class="mobile-record-item">
                                            <div class="mobile-record-label">Phone</div>
                                            <div class="mobile-record-value">{{ $profile->user->phone ?: 'No phone' }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-action-row">
                                        <a href="{{ route('admin.staff.show', ['staffProfile' => $profile] + array_filter(['search' => $search, 'department' => $departmentFilter])) }}" class="theme-button-secondary">View / Edit</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-[2rem] border border-dashed border-slate-300 px-6 py-8 text-sm text-slate-500">No staff found for this search.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
