<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Administration</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">Student management</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.people') }}" class="theme-button-secondary">People hub</a>
                <a href="{{ route('admin.staff.index') }}" class="theme-button-secondary">Go to staff</a>
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

    <x-section-nav :items="$classNavItems" :active="$activeStudentClassPage" />

    <section class="section-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">{{ $pageTitle }}</h2>
                <p class="mt-2 text-sm text-slate-500">Search students on this page by name, email, admission number, or student ID.</p>
            </div>
            <form method="GET" action="{{ $activeStudentClassPage === 'all' ? route('admin.students.index') : route('admin.students.index', ['classSlug' => $activeStudentClassPage]) }}" class="flex w-full max-w-3xl flex-col gap-3 lg:flex-row">
                <input name="search" value="{{ $search }}" placeholder="Search by name, email, admission number, or student ID" class="theme-input flex-1" />
                <button type="submit" class="theme-button">Search</button>
                <a href="{{ $activeStudentClassPage === 'all' ? route('admin.students.index') : route('admin.students.index', ['classSlug' => $activeStudentClassPage]) }}" class="theme-button-secondary text-center">Reset</a>
            </form>
        </div>
    </section>

    <div class="mt-8 grid gap-8 xl:grid-cols-[0.9fr,1.1fr]">
        <section
            x-data="{
                firstName: '',
                middleName: '',
                lastName: '',
                admissionNo: '',
                password: '',
                generate() {
                    const stamp = String(Date.now()).slice(-6);
                    if (!this.admissionNo) this.admissionNo = `ADM-${stamp}`;
                    if (!this.password) this.password = `${(this.firstName || 'STU').slice(0,3).toUpperCase()}@${stamp.slice(-5)}`;
                },
                regeneratePassword() {
                    const stamp = String(Date.now()).slice(-5);
                    this.password = `${(this.firstName || 'STU').slice(0,3).toUpperCase()}@${stamp}`;
                },
                regenerateId() {
                    this.admissionNo = `ADM-${String(Date.now()).slice(-6)}`;
                }
            }"
            class="section-card"
        >
            <h2 class="display-font text-2xl font-bold text-slate-950">Register student</h2>
            <p class="mt-2 text-sm text-slate-500">Create a new student record and capture identity, guardian, and admission details in one place.</p>

            <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <input name="first_name" x-model="firstName" @input="generate()" placeholder="First name" class="theme-input" required />
                    <input name="middle_name" x-model="middleName" placeholder="Middle name" class="theme-input" />
                    <input name="last_name" x-model="lastName" @input="generate()" placeholder="Last name" class="theme-input" required />
                    <input name="email" type="email" placeholder="Student email (optional)" class="theme-input" />
                    <div class="phone-field" x-data="contactField({ target: 'student-phone-create' })">
                        <input id="student-phone-create" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="Phone" class="theme-input" />
                        <button type="button" @click="pick()" :disabled="!supported" class="contact-picker-button" title="Pick contact">Pick</button>
                    </div>
                    <select name="school_class_id" class="theme-input">
                        <option value="">Select class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                        @endforeach
                    </select>
                    <div class="md:col-span-2 grid gap-3 md:grid-cols-[1fr,auto]">
                        <input name="admission_no" x-model="admissionNo" @focus="generate()" placeholder="Admission number or student ID" class="theme-input" />
                        <button type="button" @click="regenerateId()" class="theme-button-secondary">Regenerate ID</button>
                    </div>
                    <div class="md:col-span-2 grid gap-3 md:grid-cols-[1fr,auto]">
                        <input name="password" x-model="password" @focus="generate()" placeholder="Temporary password" class="theme-input" />
                        <button type="button" @click="regeneratePassword()" class="theme-button-secondary">Regenerate Password</button>
                    </div>
                    <input name="gender" placeholder="Gender" class="theme-input" />
                    <input name="date_of_birth" type="date" class="theme-input" />
                    <input name="place_of_birth" placeholder="Place of birth" class="theme-input" />
                    <input name="nationality" placeholder="Nationality" class="theme-input" />
                    <input name="lga" placeholder="LGA" class="theme-input" />
                    <input name="state_of_origin" placeholder="State of origin" class="theme-input" />
                    <input name="religion" placeholder="Religion" class="theme-input" />
                    <input name="previous_school" placeholder="Last school attended" class="theme-input" />
                    <input name="previous_class" placeholder="Last class attended" class="theme-input" />
                    <input name="parent_name" placeholder="Parent or guardian name" class="theme-input" />
                    <input name="parent_email" type="email" placeholder="Parent email" class="theme-input" />
                    <input name="parent_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Parent phone" class="theme-input" />
                    <input name="guardian_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Guardian phone" class="theme-input" />
                    <input name="parents_occupation" placeholder="Parents occupation" class="theme-input" />
                    <input name="office_residence_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Office or residence phone" class="theme-input" />
                    <input name="doctor_name" placeholder="Family doctor name" class="theme-input" />
                    <input name="doctor_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Family doctor phone" class="theme-input" />
                    <input name="doctor_address" placeholder="Family doctor address" class="theme-input md:col-span-2" />
                    <textarea name="address" rows="3" placeholder="Home address" class="theme-input md:col-span-2"></textarea>
                    <textarea name="physical_notes" rows="3" placeholder="Physical information or special notes" class="theme-input md:col-span-2"></textarea>
                    <textarea name="medical_notes" rows="3" placeholder="Medical notes or challenges" class="theme-input md:col-span-2"></textarea>
                    <label class="rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600 md:col-span-2">
                        <span class="mb-2 block font-semibold text-slate-900">Passport photo</span>
                        <input type="file" name="passport_photo" accept="image/*" class="block w-full text-sm">
                    </label>
                </div>
                <button type="submit" class="theme-button">Create student</button>
            </form>
        </section>

        <section class="section-card">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">Student directory by class</h2>
                    <p class="mt-2 text-sm text-slate-500">Every registered student is grouped under the current class record. Open any student to edit the full profile.</p>
                </div>
                <div class="rounded-3xl bg-slate-50 px-5 py-4 text-right">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Total students</div>
                    <div class="display-font mt-2 text-3xl font-bold text-slate-950">{{ $students->count() }}</div>
                </div>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($classDirectory as $class)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <div class="font-semibold text-slate-900">{{ $class['name'] }}</div>
                        <div class="mt-1 text-sm text-slate-500">{{ $class['count'] }} student{{ $class['count'] === 1 ? '' : 's' }}</div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No student records match this search yet.</div>
                @endforelse
            </div>

            <div class="mt-8 space-y-6">
                @forelse ($studentGroups as $className => $group)
                    <div class="rounded-[2rem] border border-slate-200 p-5">
                        <div>
                            <h3 class="display-font text-xl font-bold text-slate-950">{{ $className }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $group->count() }} registered student{{ $group->count() === 1 ? '' : 's' }}</p>
                        </div>

                        <div class="mt-5 desktop-table table-wrap">
                            <table class="min-w-full text-left text-sm">
                                <thead class="text-slate-500">
                                    <tr>
                                        <th class="pb-3">Student</th>
                                        <th class="pb-3">Admission No</th>
                                        <th class="pb-3">Student ID</th>
                                        <th class="pb-3">Guardian</th>
                                        <th class="pb-3">Phone</th>
                                        <th class="pb-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach ($group as $student)
                                        <tr>
                                            <td class="py-4">
                                                <div class="font-semibold text-slate-900">{{ $student->user->fullName() }}</div>
                                                <div class="text-xs text-slate-500">{{ $student->user->email }}</div>
                                            </td>
                                            <td class="py-4 text-slate-600">{{ $student->admission_no }}</td>
                                            <td class="py-4 text-slate-600">{{ $student->student_id_no ?: 'Not set' }}</td>
                                            <td class="py-4 text-slate-600">{{ $student->guardian_name ?: ($student->parent->name ?? 'No guardian') }}</td>
                                            <td class="py-4 text-slate-600">{{ $student->guardian_phone ?: ($student->parent->phone ?? 'No phone') }}</td>
                                            <td class="py-4">
                                                <a href="{{ route('admin.students.show', ['student' => $student] + array_filter(['search' => $search, 'classSlug' => $activeStudentClassPage])) }}" class="theme-button-secondary">View / Edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5 mobile-record-list">
                            @foreach ($group as $student)
                                <article class="mobile-record-card">
                                    <div class="mobile-record-title">{{ $student->user->fullName() }}</div>
                                    <div class="mobile-record-subtitle">{{ $student->user->email }}</div>
                                    <div class="mobile-record-grid">
                                        <div class="mobile-record-item">
                                            <div class="mobile-record-label">Admission No</div>
                                            <div class="mobile-record-value">{{ $student->admission_no }}</div>
                                        </div>
                                        <div class="mobile-record-item">
                                            <div class="mobile-record-label">Student ID</div>
                                            <div class="mobile-record-value">{{ $student->student_id_no ?: 'Not set' }}</div>
                                        </div>
                                        <div class="mobile-record-item">
                                            <div class="mobile-record-label">Guardian</div>
                                            <div class="mobile-record-value">{{ $student->guardian_name ?: ($student->parent->name ?? 'No guardian') }}</div>
                                        </div>
                                        <div class="mobile-record-item">
                                            <div class="mobile-record-label">Phone</div>
                                            <div class="mobile-record-value">{{ $student->guardian_phone ?: ($student->parent->phone ?? 'No phone') }}</div>
                                        </div>
                                    </div>
                                    <div class="mobile-action-row">
                                        <a href="{{ route('admin.students.show', ['student' => $student] + array_filter(['search' => $search, 'classSlug' => $activeStudentClassPage])) }}" class="theme-button-secondary">View / Edit</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-[2rem] border border-dashed border-slate-300 px-6 py-8 text-sm text-slate-500">No students found for this search.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
