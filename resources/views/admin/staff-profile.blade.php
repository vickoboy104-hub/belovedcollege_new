<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Staff profile</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $staffProfile->user->fullName() }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $staffProfile->employee_no }} | {{ $staffProfile->department ?: 'General department' }}</p>
            </div>
            <a href="{{ route('admin.staff.index', $filters) }}" class="theme-button-secondary">Back to staff</a>
        </div>
</x-slot>

    @if (session('generated_credentials'))
        @php($credentials = session('generated_credentials'))
        <div class="mb-8 rounded-[2rem] border border-emerald-200 bg-emerald-50 px-6 py-5 text-sm text-emerald-900">
            <div class="font-semibold">Temporary {{ $credentials['audience'] }} password ready</div>
            <div class="mt-2">{{ $credentials['name'] }} | Login ID: {{ $credentials['identifier'] }} | Email: {{ $credentials['email'] }} | Password: {{ $credentials['password'] }}</div>
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-[0.85fr,1.15fr]">
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Record actions</h2>
            <div class="mt-6 space-y-4 text-sm text-slate-600">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Status</div>
                    <div class="mt-2 font-semibold text-slate-900">{{ ucfirst($staffProfile->status ?? $staffProfile->user->status ?? 'active') }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Role</div>
                    <div class="mt-2 font-semibold text-slate-900">{{ $staffProfile->user->roleLabel() }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Designation</div>
                    <div class="mt-2 font-semibold text-slate-900">{{ $staffProfile->designation ?: 'Not assigned' }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Class teacher account</div>
                    <div class="mt-2 font-semibold text-slate-900">
                        {{ $staffProfile->user->managedClasses->isNotEmpty() ? $staffProfile->user->managedClasses->pluck('display_name')->join(', ') : 'Not assigned to any class yet' }}
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Admin-visible password</div>
                    @if ($staffProfile->user->temp_password_plaintext)
                        <div class="mt-2 font-semibold text-slate-900">{{ $staffProfile->user->temp_password_plaintext }}</div>
                        <div class="mt-1 text-xs text-slate-500">Generated {{ optional($staffProfile->user->temp_password_generated_at)->format('F j, Y g:i A') ?: 'recently' }}</div>
                    @else
                        <div class="mt-2 text-sm text-slate-600">No admin-visible password yet. Existing passwords cannot be read back after hashing.</div>
                    @endif
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <form method="POST" action="{{ route('admin.staff.password.reset', $staffProfile) }}">
                    @csrf
                    <input type="hidden" name="redirect_to" value="profile">
                    @foreach ($filters as $key => $value)
                        <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="theme-button w-full" onclick="return confirm('Generate a new temporary password for this staff member? The old password will stop working.')">Generate temporary password</button>
                </form>

                <form method="POST" action="{{ route('admin.staff.deactivate', $staffProfile) }}">
                    @csrf
                    @method('PATCH')
                    @foreach ($filters as $key => $value)
                        <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="theme-button-secondary w-full" onclick="return confirm('Deactivate this staff account?')">Deactivate staff</button>
                </form>

                <form method="POST" action="{{ route('admin.staff.destroy', $staffProfile) }}">
                    @csrf
                    @method('DELETE')
                    @foreach ($filters as $key => $value)
                        <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="w-full rounded-full border border-rose-300 px-5 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50" onclick="return confirm('Delete this staff record permanently? Academic or operational records linked to this user may also be removed.')">Delete staff</button>
                </form>
            </div>
        </section>

        <section class="section-card" x-data="{ tab: 'identity' }">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <h2 class="display-font text-2xl font-bold text-slate-950">Edit full staff profile</h2>
                <div class="section-toolbar">
                    <button type="button" @click="tab = 'identity'" class="theme-button-secondary" :class="{ 'theme-inline-button-active text-white border-transparent': tab === 'identity' }">Identity</button>
                    <button type="button" @click="tab = 'employment'" class="theme-button-secondary" :class="{ 'theme-inline-button-active text-white border-transparent': tab === 'employment' }">Employment</button>
                    <button type="button" @click="tab = 'access'" class="theme-button-secondary" :class="{ 'theme-inline-button-active text-white border-transparent': tab === 'access' }">Access</button>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.staff.update', $staffProfile) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf
                @method('PATCH')
                @foreach ($filters as $key => $value)
                    <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                @endforeach

                <div x-show="tab === 'identity'" x-transition.opacity class="grid gap-4 md:grid-cols-2">
                    <input name="first_name" value="{{ old('first_name', $staffProfile->user->first_name) }}" placeholder="First name" class="theme-input" required />
                    <input name="middle_name" value="{{ old('middle_name', $staffProfile->user->middle_name) }}" placeholder="Middle name" class="theme-input" />
                    <input name="last_name" value="{{ old('last_name', $staffProfile->user->last_name) }}" placeholder="Last name" class="theme-input" required />
                    <input name="email" type="email" value="{{ old('email', $staffProfile->user->email) }}" placeholder="Email" class="theme-input" required />
                    <div class="phone-field">
                        <input id="staff-phone-profile" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('phone', $staffProfile->user->phone) }}" placeholder="Phone" class="theme-input" />
                        <button type="button" class="contact-picker-button" x-data="contactField({ target: 'staff-phone-profile' })" x-show="supported" x-cloak @click="pick()">Pick</button>
                    </div>
                    <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600 md:col-span-2">
                        <span class="mb-2 block font-semibold text-slate-900">Replace passport photo</span>
                        <input type="file" name="passport_photo" accept="image/*" class="block w-full text-sm">
                    </label>
                </div>

                <div x-show="tab === 'employment'" x-transition.opacity class="grid gap-4 md:grid-cols-2" style="display: none;">
                    <input name="employee_no" value="{{ old('employee_no', $staffProfile->employee_no) }}" placeholder="Staff ID" class="theme-input" required />
                    <input name="department" value="{{ old('department', $staffProfile->department) }}" placeholder="Department" class="theme-input" />
                    <input name="designation" value="{{ old('designation', $staffProfile->designation) }}" placeholder="Designation" class="theme-input" />
                    <input name="qualification" value="{{ old('qualification', $staffProfile->qualification) }}" placeholder="Qualification" class="theme-input" />
                    <input name="hire_date" type="date" value="{{ old('hire_date', optional($staffProfile->hire_date)->format('Y-m-d')) }}" class="theme-input" />
                </div>

                <div x-show="tab === 'access'" x-transition.opacity class="grid gap-4 md:grid-cols-2" style="display: none;">
                    <select name="role" class="theme-input" required>
                        @foreach (['teacher' => 'Teacher', 'principal' => 'Principal', 'accountant' => 'Accountant', 'admin' => 'Admin'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $staffProfile->user->role instanceof \App\Enums\UserRole ? $staffProfile->user->role->value : $staffProfile->user->role) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="theme-input">
                        @foreach (['active', 'inactive'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $staffProfile->status ?? $staffProfile->user->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="theme-button">Save staff changes</button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
