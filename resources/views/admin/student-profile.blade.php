<x-app-layout>
    @php
        $reportTerm = $terms->firstWhere('is_current', true) ?? $terms->first();
    @endphp
    
    <x-slot name="header">
        <x-page-header title="Edit Student Profile" eyebrow="Student Profile Workspace" description="Manage full records, credentials, deactivations, and settings for {{ $student->user->fullName() }}.">
            <x-slot name="actions">
                <div class="flex flex-wrap items-center gap-3">
                    @if ($reportTerm)
                        <x-action-button variant="accent" :href="route('admin.reports.show', ['student' => $student, 'section' => 'overview', 'term_id' => $reportTerm->id])" class="!rounded-xl text-xs font-bold py-2.5">
                            Open Result Center
                        </x-action-button>
                    @endif
                    <x-action-button variant="secondary" :href="route('admin.students.record', $student)" target="_blank" class="!rounded-xl text-xs font-bold py-2.5">
                        Print Dossier
                    </x-action-button>
                    <x-action-button variant="secondary" :href="route('admin.students.index', $filters)" class="!rounded-xl text-xs font-bold py-2.5">
                        Back to Students
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
            :name="$student->user->fullName()" 
            role="STUDENT" 
            :id="$student->admission_no"
            :avatar="$student->passport_photo ? asset('storage/' . $student->passport_photo) : null" 
            :classDetails="$student->schoolClass->display_name ?? 'Class not assigned'" 
            :status="ucfirst($student->status ?? $student->user->status ?? 'active')"
        />
    </div>

    <div class="grid gap-8 xl:grid-cols-[0.85fr,1.15fr]">
        <!-- Left Side: Actions and Credentials Overview -->
        <div class="space-y-8">
            <!-- Record Details -->
            <x-dashboard-card title="Record Status & Details" subtitle="System records and linked accounts overview.">
                <div class="space-y-4 mt-4">
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60 flex flex-col">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Account Status</span>
                        <div class="mt-2.5 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-800">Current Status</span>
                            <x-status-badge :status="ucfirst($student->status ?? $student->user->status ?? 'active')" />
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60 flex flex-col">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Parent Account Mappings</span>
                        <div class="mt-2.5 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-800">{{ $student->parent->name ?? 'No linked parent account' }}</span>
                            @if($student->parent)
                                <span class="text-xs font-semibold text-slate-500">{{ $student->parent->email }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60 flex flex-col">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Academic Session</span>
                        <div class="mt-2.5 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-800">{{ $student->academicSession->name ?? 'Not assigned' }}</span>
                            <span class="text-[10px] font-extrabold uppercase tracking-widest bg-blue-50 text-blue-600 px-2 py-0.5 border border-blue-100 rounded">Session</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/60 flex flex-col">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Admin-Visible Password</span>
                        @if ($student->user->temp_password_plaintext)
                            <div class="mt-3 font-mono text-sm font-extrabold text-blue-600 bg-blue-50/50 border border-blue-100 rounded-lg p-3 text-center">
                                {{ $student->user->temp_password_plaintext }}
                            </div>
                            <div class="mt-1.5 text-[10px] font-semibold text-slate-400 text-center">
                                Generated {{ optional($student->user->temp_password_generated_at)->format('F j, Y g:i A') ?: 'recently' }}
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
                    <form method="POST" action="{{ route('admin.students.password.reset', $student) }}">
                        @csrf
                        <input type="hidden" name="redirect_to" value="profile">
                        @foreach ($filters as $key => $value)
                            <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                        @endforeach
                        <x-action-button variant="accent" type="submit" class="w-full justify-center !rounded-xl py-3 font-bold text-sm shadow-sm" onclick="return confirm('Generate a new temporary password for this student? The old password will stop working.')">
                            Generate Temporary Password
                        </x-action-button>
                    </form>

                    <form method="POST" action="{{ route('admin.students.deactivate', $student) }}">
                        @csrf
                        @method('PATCH')
                        @foreach ($filters as $key => $value)
                            <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                        @endforeach
                        <x-action-button variant="secondary" type="submit" class="w-full justify-center !rounded-xl py-3 font-bold text-sm border-slate-300 hover:bg-slate-50" onclick="return confirm('Deactivate this student account?')">
                            Toggle Deactivation
                        </x-action-button>
                    </form>

                    <form method="POST" action="{{ route('admin.students.destroy', $student) }}" class="pt-2 border-t border-slate-100">
                        @csrf
                        @method('DELETE')
                        @foreach ($filters as $key => $value)
                            <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                        @endforeach
                        <x-action-button variant="danger" type="submit" class="w-full justify-center !rounded-xl py-3 font-bold text-sm" onclick="return confirm('Delete this student record permanently? This will remove linked portal access and related records.')">
                            Delete Student Permanently
                        </x-action-button>
                    </form>
                </div>
            </x-dashboard-card>
        </div>

        <!-- Right Side: Edit Form Card -->
        <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)]" x-data="{ tab: 'identity' }">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h3 class="display-font text-lg font-bold text-slate-900 leading-snug">Edit Student Profile Details</h3>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Configure full demographic, health, and guardian options.</p>
                </div>
                <div class="flex flex-wrap gap-1.5 bg-slate-100/80 p-1 rounded-xl shrink-0">
                    <button type="button" @click="tab = 'identity'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150" :class="tab === 'identity' ? 'bg-[#071833] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">Identity</button>
                    <button type="button" @click="tab = 'guardian'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150" :class="tab === 'guardian' ? 'bg-[#071833] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">Guardian</button>
                    <button type="button" @click="tab = 'health'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150" :class="tab === 'health' ? 'bg-[#071833] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">Health</button>
                    <button type="button" @click="tab = 'background'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150" :class="tab === 'background' ? 'bg-[#071833] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">Background</button>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PATCH')
                @foreach ($filters as $key => $value)
                    <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                @endforeach

                <!-- Tab 1: Identity Info -->
                <div x-show="tab === 'identity'" x-transition.opacity class="grid gap-5 md:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">First Name</label>
                        <input name="first_name" value="{{ old('first_name', $student->user->first_name) }}" class="theme-input" required />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Middle Name</label>
                        <input name="middle_name" value="{{ old('middle_name', $student->user->middle_name) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Last Name</label>
                        <input name="last_name" value="{{ old('last_name', $student->user->last_name) }}" class="theme-input" required />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Student Email</label>
                        <input name="email" type="email" value="{{ old('email', $student->user->email) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Phone</label>
                        <div class="phone-field flex relative items-center">
                            <input id="student-phone-profile" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('phone', $student->user->phone) }}" class="theme-input w-full pr-14" />
                            <button type="button" class="contact-picker-button absolute right-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded text-[10px] font-bold" x-data="contactField({ target: 'student-phone-profile' })" x-show="supported" x-cloak @click="pick()">Pick</button>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Class Allocation</label>
                        <select name="school_class_id" class="theme-input">
                            <option value="">Select class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('school_class_id', $student->school_class_id) == $class->id)>{{ $class->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Admission No</label>
                        <input name="admission_no" value="{{ old('admission_no', $student->admission_no) }}" class="theme-input" required />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Student ID Code</label>
                        <input name="student_id_no" value="{{ old('student_id_no', $student->student_id_no) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Account Status</label>
                        <select name="status" class="theme-input">
                            @foreach (['active', 'inactive'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $student->status ?? $student->user->status) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Gender</label>
                        <input name="gender" value="{{ old('gender', $student->gender) }}" class="theme-input" placeholder="e.g. Male, Female" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Date of Birth</label>
                        <input name="date_of_birth" type="date" value="{{ old('date_of_birth', optional($student->date_of_birth)->format('Y-m-d')) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Place of Birth</label>
                        <input name="place_of_birth" value="{{ old('place_of_birth', $student->place_of_birth) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Nationality</label>
                        <input name="nationality" value="{{ old('nationality', $student->nationality) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">LGA</label>
                        <input name="lga" value="{{ old('lga', $student->lga) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">State of Origin</label>
                        <input name="state_of_origin" value="{{ old('state_of_origin', $student->state_of_origin) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Religion</label>
                        <input name="religion" value="{{ old('religion', $student->religion) }}" class="theme-input" />
                    </div>
                    
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Passport Photo</label>
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center gap-4">
                            @if($student->passport_photo)
                                <img src="{{ asset('storage/' . $student->passport_photo) }}" class="w-12 h-12 rounded-lg object-cover border" />
                            @endif
                            <div class="flex-1">
                                <input type="file" name="passport_photo" accept="image/*" class="block w-full text-xs font-semibold text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-extrabold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300" />
                                <span class="text-[10px] text-slate-400 mt-1 block">Maximum size 2MB. Dynamic extensions supported.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Guardian Info -->
                <div x-show="tab === 'guardian'" x-transition.opacity class="grid gap-5 md:grid-cols-2" style="display: none;">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Guardian Name</label>
                        <input name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}" class="theme-input" placeholder="e.g. Mr. John Doe" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Guardian Phone</label>
                        <input name="guardian_phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('guardian_phone', $student->guardian_phone) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Parent Account Name</label>
                        <input name="parent_name" value="{{ old('parent_name', $student->parent->name ?? '') }}" class="theme-input" placeholder="Linked parent profile name" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Parent Account Email</label>
                        <input name="parent_email" type="email" value="{{ old('parent_email', $student->parent->email ?? '') }}" class="theme-input" placeholder="Required for linking accounts" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Parent Account Phone</label>
                        <input name="parent_phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('parent_phone', $student->parent->phone ?? '') }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Parent's Occupation</label>
                        <input name="parents_occupation" value="{{ old('parents_occupation', $student->parents_occupation) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Office / Residence Phone</label>
                        <input name="office_residence_phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('office_residence_phone', $student->office_residence_phone) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Home Address</label>
                        <textarea name="address" rows="3" class="theme-input">{{ old('address', $student->address) }}</textarea>
                    </div>
                </div>

                <!-- Tab 3: Health Info -->
                <div x-show="tab === 'health'" x-transition.opacity class="grid gap-5 md:grid-cols-2" style="display: none;">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Doctor Name</label>
                        <input name="doctor_name" value="{{ old('doctor_name', $student->doctor_name) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Doctor Phone</label>
                        <input name="doctor_phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('doctor_phone', $student->doctor_phone) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Doctor Address / Clinic</label>
                        <input name="doctor_address" value="{{ old('doctor_address', $student->doctor_address) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Physical & General Roster Notes</label>
                        <textarea name="physical_notes" rows="3" class="theme-input" placeholder="Enter posture, sight, walking traits...">{{ old('physical_notes', $student->physical_notes) }}</textarea>
                    </div>
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Allergies & Critical Medical Notes</label>
                        <textarea name="medical_notes" rows="3" class="theme-input" placeholder="Enter chronic conditions, drug sensitivities, blood type...">{{ old('medical_notes', $student->medical_notes) }}</textarea>
                    </div>
                </div>

                <!-- Tab 4: Background Info -->
                <div x-show="tab === 'background'" x-transition.opacity class="grid gap-5 md:grid-cols-2" style="display: none;">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Previous School Attended</label>
                        <input name="previous_school" value="{{ old('previous_school', $student->previous_school) }}" class="theme-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Previous Class Attained</label>
                        <input name="previous_class" value="{{ old('previous_class', $student->previous_class) }}" class="theme-input" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5 mt-6">
                    <x-action-button variant="secondary" type="button" :href="route('admin.students.index', $filters)" class="!rounded-xl py-2.5 text-xs font-bold">
                        Cancel
                    </x-action-button>
                    <x-action-button variant="primary" type="submit" class="!rounded-xl py-2.5 text-xs font-bold">
                        Save Student Changes
                    </x-action-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
