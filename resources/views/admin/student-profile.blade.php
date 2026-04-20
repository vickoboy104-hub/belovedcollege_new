<x-app-layout>
    @php($reportTerm = $terms->firstWhere('is_current', true) ?? $terms->first())
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Student profile</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $student->user->fullName() }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $student->admission_no }} | {{ $student->schoolClass->display_name ?? 'Class not assigned' }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if ($reportTerm)
                    <a href="{{ route('admin.reports.show', ['student' => $student, 'section' => 'overview', 'term_id' => $reportTerm->id]) }}" class="theme-button">Open result center</a>
                @endif
                <a href="{{ route('admin.students.record', $student) }}" target="_blank" class="theme-button-secondary">Print student dossier</a>
                <a href="{{ route('admin.students.index', $filters) }}" class="theme-button-secondary">Back to students</a>
            </div>
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
                    <div class="mt-2 font-semibold text-slate-900">{{ ucfirst($student->status ?? $student->user->status ?? 'active') }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Parent account</div>
                    <div class="mt-2 font-semibold text-slate-900">{{ $student->parent->name ?? 'No linked parent account' }}</div>
                    <div class="mt-1 text-xs text-slate-500">{{ $student->parent->email ?? 'No parent email' }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Academic session</div>
                    <div class="mt-2 font-semibold text-slate-900">{{ $student->academicSession->name ?? 'Not assigned' }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Admin-visible password</div>
                    @if ($student->user->temp_password_plaintext)
                        <div class="mt-2 font-semibold text-slate-900">{{ $student->user->temp_password_plaintext }}</div>
                        <div class="mt-1 text-xs text-slate-500">Generated {{ optional($student->user->temp_password_generated_at)->format('F j, Y g:i A') ?: 'recently' }}</div>
                    @else
                        <div class="mt-2 text-sm text-slate-600">No admin-visible password yet. Existing passwords cannot be read back after hashing.</div>
                    @endif
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <form method="POST" action="{{ route('admin.students.password.reset', $student) }}">
                    @csrf
                    <input type="hidden" name="redirect_to" value="profile">
                    @foreach ($filters as $key => $value)
                        <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="theme-button w-full" onclick="return confirm('Generate a new temporary password for this student? The old password will stop working.')">Generate temporary password</button>
                </form>

                <form method="POST" action="{{ route('admin.students.deactivate', $student) }}">
                    @csrf
                    @method('PATCH')
                    @foreach ($filters as $key => $value)
                        <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="theme-button-secondary w-full" onclick="return confirm('Deactivate this student account?')">Deactivate student</button>
                </form>

                <form method="POST" action="{{ route('admin.students.destroy', $student) }}">
                    @csrf
                    @method('DELETE')
                    @foreach ($filters as $key => $value)
                        <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="w-full rounded-full border border-rose-300 px-5 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50" onclick="return confirm('Delete this student record permanently? This will remove linked portal access and related records.')">Delete student</button>
                </form>
            </div>
        </section>

        <section class="section-card" x-data="{ tab: 'identity' }">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <h2 class="display-font text-2xl font-bold text-slate-950">Edit full student profile</h2>
                <div class="section-toolbar">
                    <button type="button" @click="tab = 'identity'" class="theme-button-secondary" :class="{ 'theme-inline-button-active text-white border-transparent': tab === 'identity' }">Identity</button>
                    <button type="button" @click="tab = 'guardian'" class="theme-button-secondary" :class="{ 'theme-inline-button-active text-white border-transparent': tab === 'guardian' }">Guardian</button>
                    <button type="button" @click="tab = 'health'" class="theme-button-secondary" :class="{ 'theme-inline-button-active text-white border-transparent': tab === 'health' }">Health</button>
                    <button type="button" @click="tab = 'background'" class="theme-button-secondary" :class="{ 'theme-inline-button-active text-white border-transparent': tab === 'background' }">Background</button>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf
                @method('PATCH')
                @foreach ($filters as $key => $value)
                    <input type="hidden" name="{{ 'redirect_'.$key }}" value="{{ $value }}">
                @endforeach

                <div x-show="tab === 'identity'" x-transition.opacity class="grid gap-4 md:grid-cols-2">
                    <input name="first_name" value="{{ old('first_name', $student->user->first_name) }}" placeholder="First name" class="theme-input" required />
                    <input name="middle_name" value="{{ old('middle_name', $student->user->middle_name) }}" placeholder="Middle name" class="theme-input" />
                    <input name="last_name" value="{{ old('last_name', $student->user->last_name) }}" placeholder="Last name" class="theme-input" required />
                    <input name="email" type="email" value="{{ old('email', $student->user->email) }}" placeholder="Student email" class="theme-input" />
                    <div class="phone-field">
                        <input id="student-phone-profile" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('phone', $student->user->phone) }}" placeholder="Phone" class="theme-input" />
                        <button type="button" class="contact-picker-button" x-data="contactField({ target: 'student-phone-profile' })" x-show="supported" x-cloak @click="pick()">Pick</button>
                    </div>
                    <select name="school_class_id" class="theme-input">
                        <option value="">Select class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('school_class_id', $student->school_class_id) == $class->id)>{{ $class->display_name }}</option>
                        @endforeach
                    </select>
                    <input name="admission_no" value="{{ old('admission_no', $student->admission_no) }}" placeholder="Admission number" class="theme-input" required />
                    <input name="student_id_no" value="{{ old('student_id_no', $student->student_id_no) }}" placeholder="Student ID" class="theme-input" />
                    <select name="status" class="theme-input">
                        @foreach (['active', 'inactive'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $student->status ?? $student->user->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <input name="gender" value="{{ old('gender', $student->gender) }}" placeholder="Gender" class="theme-input" />
                    <input name="date_of_birth" type="date" value="{{ old('date_of_birth', optional($student->date_of_birth)->format('Y-m-d')) }}" class="theme-input" />
                    <input name="place_of_birth" value="{{ old('place_of_birth', $student->place_of_birth) }}" placeholder="Place of birth" class="theme-input" />
                    <input name="nationality" value="{{ old('nationality', $student->nationality) }}" placeholder="Nationality" class="theme-input" />
                    <input name="lga" value="{{ old('lga', $student->lga) }}" placeholder="LGA" class="theme-input" />
                    <input name="state_of_origin" value="{{ old('state_of_origin', $student->state_of_origin) }}" placeholder="State of origin" class="theme-input" />
                    <input name="religion" value="{{ old('religion', $student->religion) }}" placeholder="Religion" class="theme-input" />
                    <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600 md:col-span-2">
                        <span class="mb-2 block font-semibold text-slate-900">Replace passport photo</span>
                        <input type="file" name="passport_photo" accept="image/*" class="block w-full text-sm">
                    </label>
                </div>

                <div x-show="tab === 'guardian'" x-transition.opacity class="grid gap-4 md:grid-cols-2" style="display: none;">
                    <input name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}" placeholder="Guardian name" class="theme-input" />
                    <input name="guardian_phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('guardian_phone', $student->guardian_phone) }}" placeholder="Guardian phone" class="theme-input" />
                    <input name="parent_name" value="{{ old('parent_name', $student->parent->name ?? '') }}" placeholder="Parent account name" class="theme-input" />
                    <input name="parent_email" type="email" value="{{ old('parent_email', $student->parent->email ?? '') }}" placeholder="Parent account email" class="theme-input" />
                    <input name="parent_phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('parent_phone', $student->parent->phone ?? '') }}" placeholder="Parent account phone" class="theme-input" />
                    <input name="parents_occupation" value="{{ old('parents_occupation', $student->parents_occupation) }}" placeholder="Parents occupation" class="theme-input" />
                    <input name="office_residence_phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('office_residence_phone', $student->office_residence_phone) }}" placeholder="Office or residence phone" class="theme-input" />
                    <textarea name="address" rows="4" placeholder="Home address" class="theme-input md:col-span-2">{{ old('address', $student->address) }}</textarea>
                </div>

                <div x-show="tab === 'health'" x-transition.opacity class="grid gap-4 md:grid-cols-2" style="display: none;">
                    <input name="doctor_name" value="{{ old('doctor_name', $student->doctor_name) }}" placeholder="Doctor name" class="theme-input" />
                    <input name="doctor_phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('doctor_phone', $student->doctor_phone) }}" placeholder="Doctor phone" class="theme-input" />
                    <input name="doctor_address" value="{{ old('doctor_address', $student->doctor_address) }}" placeholder="Doctor address" class="theme-input md:col-span-2" />
                    <textarea name="physical_notes" rows="4" placeholder="Physical notes" class="theme-input md:col-span-2">{{ old('physical_notes', $student->physical_notes) }}</textarea>
                    <textarea name="medical_notes" rows="4" placeholder="Medical notes" class="theme-input md:col-span-2">{{ old('medical_notes', $student->medical_notes) }}</textarea>
                </div>

                <div x-show="tab === 'background'" x-transition.opacity class="grid gap-4 md:grid-cols-2" style="display: none;">
                    <input name="previous_school" value="{{ old('previous_school', $student->previous_school) }}" placeholder="Previous school" class="theme-input" />
                    <input name="previous_class" value="{{ old('previous_class', $student->previous_class) }}" placeholder="Previous class" class="theme-input" />
                </div>

                <div class="form-actions">
                    <button type="submit" class="theme-button">Save student changes</button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
