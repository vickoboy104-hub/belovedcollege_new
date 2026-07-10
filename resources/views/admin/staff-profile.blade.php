<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Staff Profile" eyebrow="Staff Profile Workspace" description="Manage full records, credentials, deactivations, and settings for {{ $staffProfile->user->fullName() }}.">
            <x-slot name="actions">
                <div class="flex flex-wrap items-center gap-3">
                    <x-action-button variant="secondary" :href="route('admin.staff.index', $filters)" class="!rounded-xl text-xs font-bold py-2.5">
                        Back to Staff
                    </x-action-button>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if (session('generated_credentials'))
        @php
            $credentials = session('generated_credentials');
        @endphp
        <div class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-6 text-sm text-emerald-900 shadow-sm animate-pulse">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="font-extrabold uppercase tracking-wider text-xs text-emerald-800">Temporary {{ $credentials['audience'] }} password ready</span>
            </div>
            <div class="mt-3 bg-white/60 rounded-xl p-4 border border-emerald-100 font-mono text-xs flex flex-col gap-1.5 text-slate-800">
                <div><span class="font-semibold text-slate-500">Account Name:</span> {{ $credentials['name'] }}</div>
                <div><span class="font-semibold text-slate-500">Login ID:</span> {{ $credentials['identifier'] }}</div>
                <div><span class="font-semibold text-slate-500">Email Address:</span> {{ $credentials['email'] }}</div>
                <div><span class="font-semibold text-emerald-700">Plaintext Password:</span> <strong class="bg-emerald-100/80 px-2 py-0.5 rounded text-emerald-900">{{ $credentials['password'] }}</strong></div>
            </div>
        </div>
    @endif

    <!-- Profile Hero Card -->
    <div class="mb-8">
        <x-profile-hero 
            :name="$staffProfile->user->fullName()" 
            role="{{ strtoupper($staffProfile->user->roleLabel()) }}" 
            :id="$staffProfile->employee_no"
            :avatar="$staffProfile->passport_photo ? asset('storage/' . $staffProfile->passport_photo) : null" 
            :classDetails="$staffProfile->department ?: 'General Department'" 
            :status="ucfirst($staffProfile->status ?? $staffProfile->user->status ?? 'active')"
        />
    </div>

    <div class="grid gap-8 xl:grid-cols-[0.85fr,1.15fr]">
        <!-- Left Side: Actions and Credentials Overview -->
        <div class="space-y-8">
            <!-- Record Details -->
            <x-dashboard-card title="Record Status & Details" subtitle="System records and payroll details.">
                <div class="space-y-4 mt-4">
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60 flex flex-col">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Account Status</span>
                        <div class="mt-2.5 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-800">Current Status</span>
                            <x-status-badge :status="ucfirst($staffProfile->status ?? $staffProfile->user->status ?? 'active')" />
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60 flex flex-col">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Designation</span>
                        <div class="mt-2.5 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-800">{{ $staffProfile->designation ?: 'Not assigned' }}</span>
                            <span class="text-[10px] font-extrabold uppercase tracking-widest bg-blue-50 text-blue-600 px-2 py-0.5 border border-blue-100 rounded">{{ $staffProfile->user->roleLabel() }}</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60 flex flex-col">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Monthly Remuneration</span>
                        <div class="mt-2.5 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-800">{{ $staffProfile->salary ? 'NGN '.number_format((float) $staffProfile->salary, 2) : 'Not set yet' }}</span>
                            <span class="text-xs font-semibold text-slate-500">Payroll</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60 flex flex-col">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Managed Classes</span>
                        <div class="mt-2.5 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-800">
                                {{ $staffProfile->user->managedClasses->isNotEmpty() ? $staffProfile->user->managedClasses->pluck('display_name')->join(', ') : 'None' }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">Class Teacher</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60 flex flex-col">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Admin-Visible Password</span>
                        @if ($staffProfile->user->temp_password_plaintext)
                            <div class="mt-3 font-mono text-sm font-extrabold text-blue-600 bg-blue-50/50 border border-blue-100 rounded-lg p-3 text-center">
                                {{ $staffProfile->user->temp_password_plaintext }}
                            </div>
                            <div class="mt-1.5 text-[10px] font-semibold text-slate-400 text-center">
                                Generated {{ optional($staffProfile->user->temp_password_generated_at)->format('F j, Y g:i A') ?: 'recently' }}
                            </div>
                        @else
                            <div class="mt-2 text-xs font-semibold text-slate-500">
                                No temporary plain password available. Passwords cannot be read back after hashing.
                            </div>
                        @endif
                    </div>
                </div>
            </x-dashboard-card>

            <!-- Danger Zone & Operations -->
            <x-dashboard-card title="Operational Controls" subtitle="Security updates and profile lifecycle operations.">
                <div class="mt-4 space-y-4">
                    <form method="POST" action="{{ route('admin.staff.password.reset', $staffProfile) }}">
                        @csrf
                        <input type="hidden" name="redirect_to" value="profile">
                        @foreach ($filters as $key => $value)
                            <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                        @endforeach
                        <x-action-button variant="accent" type="submit" class="w-full justify-center !rounded-xl py-3 font-bold text-sm shadow-sm" onclick="return confirm('Generate a new temporary password for this staff member? The old password will stop working.')">
                            Generate Temporary Password
                        </x-action-button>
                    </form>

                    <form method="POST" action="{{ route('admin.staff.deactivate', $staffProfile) }}">
                        @csrf
                        @method('PATCH')
                        @foreach ($filters as $key => $value)
                            <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                        @endforeach
                        <x-action-button variant="secondary" type="submit" class="w-full justify-center !rounded-xl py-3 font-bold text-sm border-slate-300 hover:bg-slate-50" onclick="return confirm('Deactivate this staff account?')">
                            Toggle Deactivation
                        </x-action-button>
                    </form>

                    <form method="POST" action="{{ route('admin.staff.destroy', $staffProfile) }}" class="pt-2 border-t border-slate-100">
                        @csrf
                        @method('DELETE')
                        @foreach ($filters as $key => $value)
                            <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                        @endforeach
                        <x-action-button variant="danger" type="submit" class="w-full justify-center !rounded-xl py-3 font-bold text-sm" onclick="return confirm('Delete this staff record permanently? Academic or operational records linked to this user may also be removed.')">
                            Delete Staff Permanently
                        </x-action-button>
                    </form>
                </div>
            </x-dashboard-card>
        </div>

        <!-- Right Side: Edit Form Card -->
        <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)]" x-data="{ tab: 'identity' }">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h3 class="display-font text-lg font-bold text-slate-900 leading-snug">Edit Staff Profile Details</h3>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Configure identity, roles, qualifications, and system privileges.</p>
                </div>
                <div class="flex flex-wrap gap-1.5 bg-slate-100/80 p-1 rounded-xl shrink-0">
                    <button type="button" @click="tab = 'identity'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150" :class="tab === 'identity' ? 'bg-[#071833] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">Identity</button>
                    <button type="button" @click="tab = 'employment'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150" :class="tab === 'employment' ? 'bg-[#071833] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">Employment</button>
                    <button type="button" @click="tab = 'access'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150" :class="tab === 'access' ? 'bg-[#071833] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">Access Role</button>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.staff.update', $staffProfile) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PATCH')
                @foreach ($filters as $key => $value)
                    <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                @endforeach

                <!-- Tab 1: Identity Info -->
                <div x-show="tab === 'identity'" x-transition.opacity class="grid gap-5 md:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">First Name</label>
                        <input name="first_name" value="{{ old('first_name', $staffProfile->user->first_name) }}" class="theme-input" required />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Middle Name</label>
                        <input name="middle_name" value="{{ old('middle_name', $staffProfile->user->middle_name) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Last Name</label>
                        <input name="last_name" value="{{ old('last_name', $staffProfile->user->last_name) }}" class="theme-input" required />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Email Address</label>
                        <input name="email" type="email" value="{{ old('email', $staffProfile->user->email) }}" class="theme-input" required />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Phone</label>
                        <div class="phone-field flex relative items-center">
                            <input id="staff-phone-profile" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('phone', $staffProfile->user->phone) }}" class="theme-input w-full pr-14" />
                            <button type="button" class="contact-picker-button absolute right-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded text-[10px] font-bold" x-data="contactField({ target: 'staff-phone-profile' })" x-show="supported" x-cloak @click="pick()">Pick</button>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Passport Photo</label>
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center gap-4">
                            @if($staffProfile->passport_photo)
                                <img src="{{ asset('storage/' . $staffProfile->passport_photo) }}" class="w-12 h-12 rounded-lg object-cover border" />
                            @endif
                            <div class="flex-1">
                                <input type="file" name="passport_photo" accept="image/*" class="block w-full text-xs font-semibold text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-extrabold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300" />
                                <span class="text-[10px] text-slate-400 mt-1 block">Maximum size 2MB. Dynamic extensions supported.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Employment Info -->
                <div x-show="tab === 'employment'" x-transition.opacity class="grid gap-5 md:grid-cols-2" style="display: none;">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Employee ID / Number</label>
                        <input name="employee_no" value="{{ old('employee_no', $staffProfile->employee_no) }}" class="theme-input" required />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Department</label>
                        <input name="department" value="{{ old('department', $staffProfile->department) }}" class="theme-input" placeholder="e.g. Science Department" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Designation / Role Title</label>
                        <input name="designation" value="{{ old('designation', $staffProfile->designation) }}" class="theme-input" placeholder="e.g. Senior Tutor" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Educational Qualification</label>
                        <input name="qualification" value="{{ old('qualification', $staffProfile->qualification) }}" class="theme-input" placeholder="e.g. B.Sc. Ed, M.Ed" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Hire Date</label>
                        <input name="hire_date" type="date" value="{{ old('hire_date', optional($staffProfile->hire_date)->format('Y-m-d')) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Monthly Salary (NGN)</label>
                        <input name="salary" type="number" step="0.01" min="0" value="{{ old('salary', $staffProfile->salary) }}" class="theme-input" />
                    </div>
                </div>

                <!-- Tab 3: Access Role Info -->
                <div x-show="tab === 'access'" x-transition.opacity class="grid gap-5 md:grid-cols-2" style="display: none;">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">System Privilege / Role</label>
                        <select name="role" class="theme-input" required>
                            @foreach (['teacher' => 'Teacher', 'principal' => 'Principal', 'accountant' => 'Accountant', 'admin' => 'Admin'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('role', $staffProfile->user->role instanceof \App\Enums\UserRole ? $staffProfile->user->role->value : $staffProfile->user->role) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Account Status</label>
                        <select name="status" class="theme-input">
                            @foreach (['active', 'inactive'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $staffProfile->status ?? $staffProfile->user->status) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5 mt-6">
                    <x-action-button variant="secondary" type="button" :href="route('admin.staff.index', $filters)" class="!rounded-xl py-2.5 text-xs font-bold">
                        Cancel
                    </x-action-button>
                    <x-action-button variant="primary" type="submit" class="!rounded-xl py-2.5 text-xs font-bold">
                        Save Staff Changes
                    </x-action-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
