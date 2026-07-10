<x-app-layout>
    @php
        $activeClassRoute = route('admin.students.index');
        $resetRoute = route('admin.students.index', ['view' => $activeStudentView]);
        $toolbarTitle = match ($activeStudentView) {
            'new-students' => 'New Intake',
            'inactive' => 'Inactive Students',
            'siblings' => 'Sibling Families',
            'debtors' => 'Student Debtors',
            'class-bills' => 'Class Billing Summary',
            default => 'Student Directory',
        };
        $toolbarDescription = $activeStudentView === 'directory'
            ? 'Search by name, admission number, student ID, class, guardian, or phone number.'
            : 'Search and review the selected student-management workspace.';
        $studentPageUrl = function (int $page) use ($activeStudentClassPage, $activeStudentView, $search, $statusFilter, $billingStatusFilter) {
            $query = array_filter([
                'view' => $activeStudentView,
                'search' => $search,
                'status' => $statusFilter,
                'billing_status' => $billingStatusFilter,
                'page' => $page,
            ], fn ($value) => $value !== null && $value !== '');

            return $activeStudentClassPage === 'all'
                ? route('admin.students.index', $query)
                : route('admin.students.index', ['classSlug' => $activeStudentClassPage] + $query);
        };
    @endphp

    <div
        class="admin-page-content admin-page"
        x-data="{
            registrationOpen: {{ (isset($errors) && $errors->any()) || request()->boolean('register') ? 'true' : 'false' }},
            firstName: '',
            middleName: '',
            lastName: '',
            admissionNo: '',
            password: '',
            generate() {
                const stamp = String(Date.now()).slice(-6);
                if (!this.admissionNo) this.admissionNo = `ADM-${stamp}`;
                if (!this.password) this.password = `${(this.firstName || 'STU').slice(0, 3).toUpperCase()}@${stamp.slice(-5)}`;
            },
            regeneratePassword() {
                const stamp = String(Date.now()).slice(-5);
                this.password = `${(this.firstName || 'STU').slice(0, 3).toUpperCase()}@${stamp}`;
            },
            regenerateId() {
                this.admissionNo = `ADM-${String(Date.now()).slice(-6)}`;
            }
        }"
        x-on:keydown.escape.window="registrationOpen = false"
    >
        @if (session('generated_credentials'))
            @php
                $credentials = session('generated_credentials');
            @endphp
            <section class="admin-panel p-5">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Generated {{ $credentials['audience'] }} credentials</h2>
                        <p class="section-description">Share these temporary login details securely with the student or guardian.</p>
                    </div>
                    <x-status-badge status="Active" label="Created" />
                </div>
                <div class="admin-card-subtle mt-4 p-4 text-sm font-semibold text-slate-700">
                    <span class="font-extrabold text-slate-900">Name:</span> {{ $credentials['name'] }}
                    <span class="mx-2 text-slate-300">|</span>
                    <span class="font-extrabold text-slate-900">Login ID:</span> {{ $credentials['identifier'] }}
                    <span class="mx-2 text-slate-300">|</span>
                    <span class="font-extrabold text-slate-900">Email:</span> {{ $credentials['email'] }}
                    <span class="mx-2 text-slate-300">|</span>
                    <span class="font-extrabold text-slate-900">Password:</span> {{ $credentials['password'] }}
                </div>
            </section>
        @endif

        <section>
            <x-page-header
                title="Student Management"
                eyebrow="Administration"
                description="Manage student records, class placement, guardians, and billing status."
            >
                <x-slot name="actions">
                    <x-action-button type="button" variant="secondary" icon="download" onclick="window.print()">Export</x-action-button>
                    <x-action-button type="button" variant="primary" icon="plus" x-on:click="registrationOpen = true">Register Student</x-action-button>
                </x-slot>
            </x-page-header>
        </section>

        <section class="metrics-grid">
            <x-stat-card label="Total Students" :value="$studentWorkspaceStats['total']" accent="blue" icon="student" />
            <x-stat-card label="Active" :value="$studentWorkspaceStats['active']" accent="green" icon="student" />
            <x-stat-card label="New Intake" :value="$studentWorkspaceStats['new']" accent="blue" icon="plus" />
            <x-stat-card label="Inactive" :value="$studentWorkspaceStats['inactive']" accent="red" icon="student" />
            <x-stat-card label="Debtors" :value="$studentWorkspaceStats['debtors']" accent="gold" icon="finance" />
            <x-stat-card label="Sibling Families" :value="$studentWorkspaceStats['sibling_families']" accent="purple" icon="parents" />
        </section>

        <section class="admin-workspace-card">
            @if ($activeStudentView !== 'class-bills')
                <x-filter-card
                    :action="$activeClassRoute"
                    method="GET"
                    :title="$toolbarTitle"
                    :description="$toolbarDescription"
                >
                    <input type="hidden" name="view" value="{{ $activeStudentView }}" />
                    <input name="search" value="{{ $search }}" placeholder="Search by name, admission number, student ID, guardian, or phone" class="theme-input" />
                    <select name="classSlug" aria-label="Class" class="theme-input">
                        <option value="" @selected($activeStudentClassPage === 'all')>All classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->slug }}" @selected($activeStudentClassPage === $class->slug)>
                                {{ $class->display_name }}
                            </option>
                        @endforeach
                        <option value="unassigned" @selected($activeStudentClassPage === 'unassigned')>Unassigned</option>
                    </select>
                    <select name="status" aria-label="Status" class="theme-input">
                        <option value="">Status</option>
                        <option value="active" @selected($statusFilter === 'active')>Active</option>
                        <option value="inactive" @selected($statusFilter === 'inactive')>Inactive</option>
                    </select>
                    <select name="billing_status" aria-label="Billing Status" class="theme-input">
                        <option value="">Billing Status</option>
                        <option value="clear" @selected($billingStatusFilter === 'clear')>Clear</option>
                        <option value="debtors" @selected($billingStatusFilter === 'debtors')>Debtors</option>
                        <option value="overpaid" @selected($billingStatusFilter === 'overpaid')>Overpaid</option>
                    </select>
                    <div class="admin-actions">
                        <x-action-button type="submit" variant="primary">Apply</x-action-button>
                        <x-action-button variant="secondary" :href="$resetRoute">Reset</x-action-button>
                    </div>
                </x-filter-card>
            @else
                <div class="admin-toolbar-card">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">Class Billing Summary</h2>
                            <p class="section-description">Review class-level billing collections, total exposure, and current collection ratios.</p>
                        </div>
                        <x-action-button variant="secondary" :href="route('admin.finance.records', ['section' => 'class-bills'])">Open Finance Records</x-action-button>
                    </div>
                </div>
            @endif

            <div class="student-directory-shell">
                @if ($activeStudentView === 'directory')
                    <div>
                        <div class="section-header mb-4">
                            <div>
                                <h2 class="section-title">Student Directory</h2>
                                <p class="section-description">Search, filter, and manage registered students.</p>
                            </div>
                        </div>

                        <div class="desktop-only-table">
                            <x-data-table :headers="['Student', 'Admission No', 'Student ID', 'Class', 'Guardian', 'Phone', 'Status', 'Actions']">
                                @forelse ($students as $student)
                                    @php
                                        $studentStatus = $student->status ?? $student->user->status ?? 'active';
                                        $studentBalance = (float) $student->feeInvoices->sum('balance');
                                        $billingStatus = $studentBalance > 0 ? 'Owing NGN '.number_format($studentBalance, 2) : 'Cleared';
                                        $studentState = array_filter([
                                            'search' => $search,
                                            'classSlug' => $activeStudentClassPage !== 'all' ? $activeStudentClassPage : null,
                                            'view' => $activeStudentView,
                                            'status' => $statusFilter,
                                            'billing_status' => $billingStatusFilter,
                                        ], fn ($value) => $value !== null && $value !== '');
                                        $studentPreview = [
                                            'type' => 'student',
                                            'title' => $student->user->fullName(),
                                            'subtitle' => ucfirst($studentStatus).' Student • '.($student->schoolClass->display_name ?? 'Unassigned'),
                                            'avatar' => substr($student->user->first_name, 0, 1).substr($student->user->last_name, 0, 1),
                                            'profileUrl' => route('admin.students.show', ['student' => $student] + $studentState),
                                            'ctaLabel' => 'View Full Profile',
                                            'fields' => [
                                                ['label' => 'Admission No', 'value' => $student->admission_no ?: 'Pending'],
                                                ['label' => 'Student ID', 'value' => $student->student_id_no ?: 'Not set'],
                                                ['label' => 'Class', 'value' => $student->schoolClass->display_name ?? 'Unassigned'],
                                                ['label' => 'Guardian', 'value' => $student->guardian_name ?: ($student->parent?->fullName() ?? $student->parent?->name ?? 'No guardian')],
                                                ['label' => 'Phone', 'value' => $student->guardian_phone ?: ($student->parent?->phone ?? $student->user->phone ?? 'No phone')],
                                                ['label' => 'Email', 'value' => $student->user->email ?: 'No email'],
                                                ['label' => 'Billing Status', 'value' => $billingStatus],
                                            ],
                                        ];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="table-person">
                                                <div class="table-avatar">
                                                    {{ substr($student->user->first_name, 0, 1) }}{{ substr($student->user->last_name, 0, 1) }}
                                                </div>
                                                <div class="table-person-text">
                                                    <strong>{{ $student->user->fullName() }}</strong>
                                                    <span>{{ $student->user->email ?: 'No email' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="font-mono font-bold whitespace-nowrap">{{ $student->admission_no ?: 'Pending' }}</td>
                                        <td class="font-semibold text-slate-600 whitespace-nowrap">{{ $student->student_id_no ?: 'Not set' }}</td>
                                        <td class="font-semibold text-slate-700 whitespace-nowrap">{{ $student->schoolClass->display_name ?? 'Unassigned' }}</td>
                                        <td class="font-semibold text-slate-700 whitespace-nowrap">{{ $student->guardian_name ?: ($student->parent?->fullName() ?? $student->parent?->name ?? 'No guardian') }}</td>
                                        <td class="font-semibold text-slate-600 whitespace-nowrap">{{ $student->guardian_phone ?: ($student->parent?->phone ?? $student->user->phone ?? 'No phone') }}</td>
                                        <td>
                                            <x-status-badge :status="$studentStatus" />
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="table-view-btn"
                                                data-preview='@json($studentPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'
                                            >
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                <tr>
                                    <td colspan="8">
                                            <x-empty-state title="No students found" description="Adjust the search, class, or status filters to broaden the directory." icon="student" />
                                        </td>
                                    </tr>
                                @endforelse
                                <x-slot name="paginationSlot">
                                    <div class="admin-table-footer">
                                        <div class="admin-table-count">
                                            Showing {{ $studentShowingFrom }}-{{ $studentShowingTo }} of {{ $studentTotalCount }} students
                                        </div>
                                        <div class="admin-pagination">
                                            <a class="admin-page-btn {{ $studentPage <= 1 ? 'is-disabled' : '' }}" href="{{ $studentPage <= 1 ? '#' : $studentPageUrl($studentPage - 1) }}">Previous</a>
                                            @for ($page = 1; $page <= $studentPageCount; $page++)
                                                <a class="admin-page-btn {{ $page === $studentPage ? 'is-active' : '' }}" href="{{ $studentPageUrl($page) }}">{{ $page }}</a>
                                            @endfor
                                            <a class="admin-page-btn {{ $studentPage >= $studentPageCount ? 'is-disabled' : '' }}" href="{{ $studentPage >= $studentPageCount ? '#' : $studentPageUrl($studentPage + 1) }}">Next</a>
                                        </div>
                                    </div>
                                </x-slot>
                            </x-data-table>
                        </div>

                        <!-- Responsive Mobile View -->
                        <div class="mobile-record-list mt-6 space-y-4 md:hidden">
                            @forelse ($students as $student)
                                @php
                                    $studentStatus = $student->status ?? $student->user->status ?? 'active';
                                    $studentBalance = (float) $student->feeInvoices->sum('balance');
                                    $billingStatus = $studentBalance > 0 ? 'Owing NGN '.number_format($studentBalance, 2) : 'Cleared';
                                    $studentState = array_filter([
                                        'search' => $search,
                                        'classSlug' => $activeStudentClassPage !== 'all' ? $activeStudentClassPage : null,
                                        'view' => $activeStudentView,
                                        'status' => $statusFilter,
                                        'billing_status' => $billingStatusFilter,
                                    ], fn ($value) => $value !== null && $value !== '');
                                    $studentPreview = [
                                        'type' => 'student',
                                        'title' => $student->user->fullName(),
                                        'subtitle' => ucfirst($studentStatus).' Student • '.($student->schoolClass->display_name ?? 'Unassigned'),
                                        'avatar' => substr($student->user->first_name, 0, 1).substr($student->user->last_name, 0, 1),
                                        'profileUrl' => route('admin.students.show', ['student' => $student] + $studentState),
                                        'ctaLabel' => 'View Full Profile',
                                        'fields' => [
                                            ['label' => 'Admission No', 'value' => $student->admission_no ?: 'Pending'],
                                            ['label' => 'Student ID', 'value' => $student->student_id_no ?: 'Not set'],
                                            ['label' => 'Class', 'value' => $student->schoolClass->display_name ?? 'Unassigned'],
                                            ['label' => 'Guardian', 'value' => $student->guardian_name ?: ($student->parent?->fullName() ?? $student->parent?->name ?? 'No guardian')],
                                            ['label' => 'Phone', 'value' => $student->guardian_phone ?: ($student->parent?->phone ?? $student->user->phone ?? 'No phone')],
                                            ['label' => 'Email', 'value' => $student->user->email ?: 'No email'],
                                            ['label' => 'Billing Status', 'value' => $billingStatus],
                                        ],
                                    ];
                                @endphp
                                <article class="mobile-record-card">
                                    <div class="flex items-start justify-between border-b border-slate-100 pb-3 mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="table-avatar !h-9 !w-9 !text-xs">
                                                {{ substr($student->user->first_name, 0, 1) }}{{ substr($student->user->last_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="mobile-record-title">{{ $student->user->fullName() }}</div>
                                                <div class="text-[10px] text-slate-550 font-bold mt-0.5">{{ $student->schoolClass->display_name ?? 'Unassigned' }}</div>
                                            </div>
                                        </div>
                                        <x-status-badge :status="$studentStatus" class="scale-90 origin-right" />
                                    </div>
                                    
                                    <div class="mobile-record-grid">
                                        <div class="mobile-record-item">
                                            <span class="mobile-record-label">Admission No</span>
                                            <span class="mobile-record-value font-mono font-bold">{{ $student->admission_no ?: 'Pending' }}</span>
                                        </div>
                                        <div class="mobile-record-item">
                                            <span class="mobile-record-label">Guardian</span>
                                            <span class="mobile-record-value">{{ $student->guardian_name ?: ($student->parent?->fullName() ?? $student->parent?->name ?? 'No guardian') }}</span>
                                        </div>
                                        <div class="mobile-record-item">
                                            <span class="mobile-record-label">Phone</span>
                                            <span class="mobile-record-value">{{ $student->guardian_phone ?: ($student->parent?->phone ?? $student->user->phone ?? 'No phone') }}</span>
                                        </div>
                                        <div class="mobile-record-item">
                                            <span class="mobile-record-label">Billing</span>
                                            <span class="mobile-record-value font-bold {{ $studentBalance > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $billingStatus }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mobile-action-row border-t border-slate-100 pt-3 mt-4">
                                        <button
                                            type="button"
                                            class="table-view-btn w-full !text-center !py-2 !rounded-xl !bg-slate-100 hover:!bg-slate-205 text-slate-700 font-bold transition"
                                            data-preview='@json($studentPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'
                                        >
                                            Quick View
                                        </button>
                                        <a
                                            href="{{ route('admin.students.show', ['student' => $student] + $studentState) }}"
                                            class="theme-button w-full !min-h-[2.45rem] !py-2 !px-4 !text-xs font-bold text-center !rounded-xl"
                                        >
                                            Full Profile
                                        </a>
                                    </div>
                                </article>
                            @empty
                                <x-empty-state title="No students found" description="Adjust filters or search query." icon="student" />
                            @endforelse
                        </div>

                        <!-- Mobile Pagination -->
                        @if ($studentPageCount > 1)
                            <div class="admin-table-footer mt-4 mobile-only p-4 bg-white border border-[#c8d6ea] rounded-2xl shadow-sm">
                                <div class="admin-pagination w-full justify-between flex gap-2">
                                    <a class="admin-page-btn !py-2 !px-4 {{ $studentPage <= 1 ? 'is-disabled' : '' }}" href="{{ $studentPage <= 1 ? '#' : $studentPageUrl($studentPage - 1) }}">Previous</a>
                                    <span class="text-xs font-bold text-slate-500 self-center">Page {{ $studentPage }} of {{ $studentPageCount }}</span>
                                    <a class="admin-page-btn !py-2 !px-4 {{ $studentPage >= $studentPageCount ? 'is-disabled' : '' }}" href="{{ $studentPage >= $studentPageCount ? '#' : $studentPageUrl($studentPage + 1) }}">Next</a>
                                </div>
                            </div>
                        @endif
                    </div>

                @elseif ($activeStudentView === 'new-students')
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">New Student Intake</h2>
                            <p class="section-description">Recently admitted students for the current school cycle.</p>
                        </div>
                    </div>

                    <x-data-table :headers="['Student', 'Admission No', 'Class', 'Guardian', 'Enrolled', 'Status', 'Actions']">
                        @forelse ($newStudents as $student)
                            @php
                                $newPreview = [
                                    'type' => 'student',
                                    'title' => $student->user->fullName(),
                                    'subtitle' => 'New Intake • '.($student->schoolClass->display_name ?? 'Class pending'),
                                    'avatar' => substr($student->user->first_name, 0, 1).substr($student->user->last_name, 0, 1),
                                    'profileUrl' => route('admin.students.show', ['student' => $student, 'view' => $activeStudentView] + array_filter(['search' => $search])),
                                    'ctaLabel' => 'View Full Profile',
                                    'fields' => [
                                        ['label' => 'Admission No', 'value' => $student->admission_no ?: 'Pending'],
                                        ['label' => 'Student ID', 'value' => $student->student_id_no ?: 'Not set'],
                                        ['label' => 'Class', 'value' => $student->schoolClass->display_name ?? 'Class pending'],
                                        ['label' => 'Guardian', 'value' => $student->parent?->fullName() ?? $student->parent?->name ?? 'Not linked yet'],
                                        ['label' => 'Email', 'value' => $student->user->email ?: 'No email'],
                                        ['label' => 'Enrolled', 'value' => optional($student->enrolled_at ?? $student->created_at)->format('M j, Y') ?: 'Recently'],
                                    ],
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <div class="table-person">
                                        <div class="table-avatar">{{ substr($student->user->first_name, 0, 1) }}{{ substr($student->user->last_name, 0, 1) }}</div>
                                        <div class="table-person-text">
                                            <strong>{{ $student->user->fullName() }}</strong>
                                            <span>{{ $student->user->email ?: 'No email' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-mono font-bold">{{ $student->admission_no ?: 'Pending' }}</td>
                                <td>{{ $student->schoolClass->display_name ?? 'Class pending' }}</td>
                                <td><span class="table-text-clip">{{ $student->parent?->fullName() ?? $student->parent?->name ?? 'Not linked yet' }}</span></td>
                                <td>{{ optional($student->enrolled_at ?? $student->created_at)->format('M j, Y') ?: 'Recently' }}</td>
                                <td><x-status-badge status="Active" /></td>
                                <td><button type="button" class="table-view-btn" data-preview='@json($newPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state title="No new intake students found" description="Newly admitted students will appear here after registration." icon="student" />
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>

                @elseif ($activeStudentView === 'inactive')
                    <div class="section-header mb-4">
                        <div>
                            <h2 class="section-title">Inactive Student Records</h2>
                            <p class="section-description">Controlled access to deactivated student records.</p>
                        </div>
                    </div>

                    <x-data-table :headers="['Student', 'Admission No', 'Class', 'Guardian', 'Status', 'Actions']">
                        @forelse ($inactiveStudents as $student)
                            @php
                                $inactivePreview = [
                                    'type' => 'student',
                                    'title' => $student->user->fullName(),
                                    'subtitle' => 'Inactive Student • '.($student->schoolClass->display_name ?? 'Unassigned'),
                                    'avatar' => substr($student->user->first_name, 0, 1).substr($student->user->last_name, 0, 1),
                                    'profileUrl' => route('admin.students.show', ['student' => $student, 'view' => $activeStudentView] + array_filter(['search' => $search])),
                                    'ctaLabel' => 'View Full Profile',
                                    'fields' => [
                                        ['label' => 'Admission No', 'value' => $student->admission_no ?: 'Pending'],
                                        ['label' => 'Student ID', 'value' => $student->student_id_no ?: 'Not set'],
                                        ['label' => 'Class', 'value' => $student->schoolClass->display_name ?? 'Unassigned'],
                                        ['label' => 'Guardian', 'value' => $student->parent?->fullName() ?? $student->parent?->name ?? 'No parent'],
                                        ['label' => 'Phone', 'value' => $student->parent?->phone ?? $student->user->phone ?? 'No phone'],
                                        ['label' => 'Email', 'value' => $student->user->email ?: 'No email'],
                                    ],
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <div class="table-person">
                                        <div class="table-avatar">{{ substr($student->user->first_name, 0, 1) }}{{ substr($student->user->last_name, 0, 1) }}</div>
                                        <div class="table-person-text">
                                            <strong>{{ $student->user->fullName() }}</strong>
                                            <span>{{ $student->user->email ?: 'No email' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-mono font-bold whitespace-nowrap">{{ $student->admission_no }}</td>
                                <td class="font-semibold whitespace-nowrap">{{ $student->schoolClass->display_name ?? 'Unassigned' }}</td>
                                <td class="font-semibold whitespace-nowrap">{{ $student->parent?->fullName() ?? $student->parent?->name ?? 'No parent' }}</td>
                                <td><x-status-badge status="Inactive" /></td>
                                <td>
                                    <button type="button" class="table-view-btn" data-preview='@json($inactivePreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state title="No inactive students found" description="There are no inactive or deactivated student records on file." icon="student" />
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>

                @elseif ($activeStudentView === 'siblings')
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">Sibling Families</h2>
                            <p class="section-description">Families with more than one child linked to the same parent account.</p>
                        </div>
                    </div>

                    <x-data-table :headers="['Parent / Guardian', 'Children', 'Classes', 'Family Size', 'Status', 'Actions']">
                        @forelse ($siblingRows as $row)
                            @php
                                $firstChild = $row['students']->first();
                                $siblingPreview = [
                                    'type' => 'parent',
                                    'title' => $row['parent']?->fullName() ?? $row['parent']?->name ?? 'Parent account not named',
                                    'subtitle' => 'Sibling Family • '.$row['family_size'].' children',
                                    'avatar' => collect(explode(' ', $row['parent']?->fullName() ?? $row['parent']?->name ?? 'Parent'))->filter()->map(fn ($part) => substr($part, 0, 1))->take(2)->join(''),
                                    'profileUrl' => $firstChild ? route('admin.students.show', ['student' => $firstChild, 'view' => $activeStudentView] + array_filter(['search' => $search])) : route('admin.parents.index'),
                                    'ctaLabel' => 'View Student Links',
                                    'fields' => [
                                        ['label' => 'Parent Email', 'value' => $row['parent']?->email ?? 'No email'],
                                        ['label' => 'Parent Phone', 'value' => $row['parent']?->phone ?? 'No phone'],
                                        ['label' => 'Children', 'value' => $row['students']->pluck('user.name')->join(', ')],
                                        ['label' => 'Classes', 'value' => $row['class_names']->join(', ')],
                                    ],
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <div class="table-person">
                                        <div class="table-avatar">{{ $siblingPreview['avatar'] ?: 'PA' }}</div>
                                        <div class="table-person-text">
                                            <strong>{{ $siblingPreview['title'] }}</strong>
                                            <span>{{ $row['parent']?->email ?? 'No email' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="table-text-clip">{{ $row['students']->pluck('user.name')->join(', ') }}</span></td>
                                <td><span class="table-text-clip">{{ $row['class_names']->join(', ') }}</span></td>
                                <td>{{ $row['family_size'] }}</td>
                                <td><x-status-badge status="Active" /></td>
                                <td><button type="button" class="table-view-btn" data-preview='@json($siblingPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-empty-state title="No sibling families found" description="Search returned no parent accounts with multiple registered children." icon="parents" />
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>

                @elseif ($activeStudentView === 'debtors')
                    <div class="section-header mb-4">
                        <div>
                            <h2 class="section-title">Student Debtors</h2>
                            <p class="section-description">Outstanding fees shown directly in student management for quick follow-up.</p>
                        </div>
                    </div>

                    <x-data-table :headers="['Student', 'Outstanding', 'Paid', 'Unpaid Items', 'Actions']">
                        @forelse ($studentDebtorRows as $row)
                            @php
                                $debtorStudent = $row['student'];
                                $debtorItems = $row['items']->map(fn ($invoice) => ($invoice->feeItem->name ?? 'Direct invoice').' ('.$invoice->invoice_no.')')->join(', ');
                                $debtorPreview = [
                                    'type' => 'debtor',
                                    'title' => $debtorStudent->user->fullName(),
                                    'subtitle' => 'Student Debtor - '.($debtorStudent->schoolClass->display_name ?? 'Unassigned'),
                                    'avatar' => substr($debtorStudent->user->first_name, 0, 1).substr($debtorStudent->user->last_name, 0, 1),
                                    'profileUrl' => route('admin.students.show', ['student' => $debtorStudent, 'view' => $activeStudentView] + array_filter(['search' => $search])),
                                    'ctaLabel' => 'View Full Details',
                                    'fields' => [
                                        ['label' => 'Admission No', 'value' => $debtorStudent->admission_no ?: 'Pending'],
                                        ['label' => 'Class', 'value' => $debtorStudent->schoolClass->display_name ?? 'Unassigned'],
                                        ['label' => 'Outstanding', 'value' => 'NGN '.number_format((float) $row['outstanding_total'], 2)],
                                        ['label' => 'Paid', 'value' => 'NGN '.number_format((float) $row['paid_total'], 2)],
                                        ['label' => 'Unpaid Items', 'value' => $row['items']->count().' item(s)'],
                                        ['label' => 'Item Summary', 'value' => $debtorItems ?: 'No unpaid item summary'],
                                    ],
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <div class="table-person">
                                        <div class="table-avatar">{{ substr($debtorStudent->user->first_name, 0, 1) }}{{ substr($debtorStudent->user->last_name, 0, 1) }}</div>
                                        <div class="table-person-text">
                                            <strong>{{ $debtorStudent->user->fullName() }}</strong>
                                            <span>{{ $debtorStudent->admission_no ?: 'Pending' }} | {{ $debtorStudent->schoolClass->display_name ?? 'Unassigned' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-extrabold text-rose-600 whitespace-nowrap">NGN {{ number_format((float) $row['outstanding_total'], 2) }}</td>
                                <td class="font-bold text-emerald-600 whitespace-nowrap">NGN {{ number_format((float) $row['paid_total'], 2) }}</td>
                                <td>
                                    <span class="table-text-clip">
                                        {{ $row['items']->count() }} unpaid item{{ $row['items']->count() === 1 ? '' : 's' }} - {{ $row['items']->take(2)->map(fn ($invoice) => $invoice->feeItem->name ?? 'Direct invoice')->join(', ') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="table-view-btn" data-preview='@json($debtorPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-empty-state title="No student debtors found" description="There are no students with active debt outstanding in this workspace." icon="finance" />
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>

                @elseif ($activeStudentView === 'class-bills')
                    <x-data-table :headers="['Class', 'Students', 'Expected', 'Collected', 'Outstanding', 'Rate', 'Actions']">
                        @forelse ($studentClassBillingRows as $row)
                            @php
                                $classBillPreview = [
                                    'type' => 'class',
                                    'title' => $row['class']->display_name,
                                    'subtitle' => 'Class Billing - '.number_format((float) $row['collection_rate'], 1).'% collected',
                                    'avatar' => 'CL',
                                    'profileUrl' => route('admin.finance.records', ['section' => 'class-bills']),
                                    'ctaLabel' => 'View Full Details',
                                    'fields' => [
                                        ['label' => 'Students', 'value' => $row['student_count']],
                                        ['label' => 'Invoices', 'value' => $row['invoice_count']],
                                        ['label' => 'Expected', 'value' => 'NGN '.number_format((float) $row['expected_total'], 2)],
                                        ['label' => 'Collected', 'value' => 'NGN '.number_format((float) $row['collected_total'], 2)],
                                        ['label' => 'Outstanding', 'value' => 'NGN '.number_format((float) $row['outstanding_total'], 2)],
                                        ['label' => 'Debtors', 'value' => $row['students_with_debt'].' student(s)'],
                                    ],
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-bold text-slate-900">{{ $row['class']->display_name }}</div>
                                    <div class="text-xs font-semibold text-slate-500">{{ $row['invoice_count'] }} invoice{{ $row['invoice_count'] === 1 ? '' : 's' }} | {{ $row['students_with_debt'] }} debtor{{ $row['students_with_debt'] === 1 ? '' : 's' }}</div>
                                </td>
                                <td class="font-bold">{{ $row['student_count'] }}</td>
                                <td class="font-semibold whitespace-nowrap">NGN {{ number_format((float) $row['expected_total'], 2) }}</td>
                                <td class="font-bold text-emerald-600 whitespace-nowrap">NGN {{ number_format((float) $row['collected_total'], 2) }}</td>
                                <td class="font-extrabold text-rose-600 whitespace-nowrap">NGN {{ number_format((float) $row['outstanding_total'], 2) }}</td>
                                <td>
                                    <div class="rate-cell">
                                        <span class="font-bold">{{ number_format((float) $row['collection_rate'], 1) }}% collected</span>
                                        <div class="rate-bar">
                                            <div class="rate-bar-fill" style="width: {{ min(100, $row['collection_rate']) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="table-view-btn" data-preview='@json($classBillPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state title="No class billing data" description="There is no active invoice or fee data on file for classes in the current term." icon="finance" />
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>
                @endif
            </div>
        </section>

        <x-entity-preview-modal />

        <div
            x-cloak
            x-show="registrationOpen"
            x-transition.opacity
            class="admin-drawer-shell"
            role="dialog"
            aria-modal="true"
            aria-label="Register new student"
        >
            <button type="button" class="admin-drawer-backdrop" x-on:click="registrationOpen = false" aria-label="Close student registration"></button>
            <aside
                class="admin-drawer-panel"
                x-show="registrationOpen"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-180"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
            >
                <div class="admin-drawer-header">
                    <div>
                        <p class="eyebrow">Student Intake</p>
                        <h2 class="section-title mt-1">Register New Student</h2>
                        <p class="section-description">Capture personal, admission, guardian, login, and health details in a full-width form.</p>
                    </div>
                    <button type="button" class="admin-icon-button" x-on:click="registrationOpen = false" aria-label="Close drawer">
                        <x-app-icon name="x" />
                    </button>
                </div>

                <div class="admin-drawer-body">
                    <x-form-card :action="route('admin.students.store')" method="POST" enctype="multipart/form-data">
                        <div class="admin-card-subtle p-5">
                            <div class="section-header">
                                <div>
                                    <h3 class="section-title">Personal Information</h3>
                                    <p class="section-description">Basic identity and student contact details.</p>
                                </div>
                            </div>
                            <div class="form-grid mt-4">
                                <div class="form-group">
                                    <label>First Name <span class="text-rose-500">*</span></label>
                                    <input name="first_name" x-model="firstName" x-on:input="generate()" placeholder="e.g. Daniel" class="theme-input" required />
                                </div>
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <input name="middle_name" x-model="middleName" placeholder="e.g. Adeyemi" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Last Name <span class="text-rose-500">*</span></label>
                                    <input name="last_name" x-model="lastName" x-on:input="generate()" placeholder="e.g. Okafor" class="theme-input" required />
                                </div>
                                <div class="form-group">
                                    <label>Student Email Address</label>
                                    <input name="email" type="email" placeholder="student@belovedschools.com" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Student Phone Number</label>
                                    <input id="student-phone-create" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="Phone" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Gender</label>
                                    <input name="gender" placeholder="e.g. Male" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input name="date_of_birth" type="date" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Place of Birth</label>
                                    <input name="place_of_birth" placeholder="e.g. Lagos" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Nationality</label>
                                    <input name="nationality" placeholder="e.g. Nigerian" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>State of Origin</label>
                                    <input name="state_of_origin" placeholder="e.g. Lagos State" class="theme-input" />
                                </div>
                            </div>
                        </div>

                        <div class="admin-card-subtle p-5">
                            <div class="section-header">
                                <div>
                                    <h3 class="section-title">Class & Admission</h3>
                                    <p class="section-description">Assign class placement, official admission number, and student ID.</p>
                                </div>
                            </div>
                            <div class="form-grid mt-4">
                                <div class="form-group">
                                    <label>Assign School Class</label>
                                    <select name="school_class_id" class="theme-input">
                                        <option value="">Select class</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Student ID Number</label>
                                    <input name="student_id_no" placeholder="Optional student ID" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Admission Number</label>
                                    <div class="grid gap-2 sm:grid-cols-[1fr,auto]">
                                        <input name="admission_no" x-model="admissionNo" x-on:focus="generate()" placeholder="Admission ID" class="theme-input" />
                                        <x-action-button type="button" variant="secondary" x-on:click="regenerateId()">Regenerate</x-action-button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Temporary Password</label>
                                    <div class="grid gap-2 sm:grid-cols-[1fr,auto]">
                                        <input name="password" x-model="password" x-on:focus="generate()" placeholder="Temporary password" class="theme-input" />
                                        <x-action-button type="button" variant="secondary" x-on:click="regeneratePassword()">Regenerate</x-action-button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Last School Attended</label>
                                    <input name="previous_school" placeholder="e.g. ABC Academy" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Last Class Attended</label>
                                    <input name="previous_class" placeholder="e.g. JSS 3" class="theme-input" />
                                </div>
                            </div>
                        </div>

                        <div class="admin-card-subtle p-5">
                            <div class="section-header">
                                <div>
                                    <h3 class="section-title">Guardian Information</h3>
                                    <p class="section-description">Parent portal mapping and emergency contact details.</p>
                                </div>
                            </div>
                            <div class="form-grid mt-4">
                                <div class="form-group">
                                    <label>Parent / Guardian Name</label>
                                    <input name="parent_name" placeholder="e.g. Charles Okafor" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Parent Email Address</label>
                                    <input name="parent_email" type="email" placeholder="parent@example.com" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Parent Phone Number</label>
                                    <input name="parent_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Parent phone" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Guardian Phone</label>
                                    <input name="guardian_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Guardian phone" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Guardian Name</label>
                                    <input name="guardian_name" placeholder="If different from parent" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Parents Occupation</label>
                                    <input name="parents_occupation" placeholder="e.g. Business Administrator" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Office / Residence Phone</label>
                                    <input name="office_residence_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Office phone" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>LGA</label>
                                    <input name="lga" placeholder="e.g. Ikeja" class="theme-input" />
                                </div>
                                <div class="form-group sm:col-span-2">
                                    <label>Home Address</label>
                                    <textarea name="address" rows="3" placeholder="Home residential address details..." class="theme-input"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="admin-card-subtle p-5">
                            <div class="section-header">
                                <div>
                                    <h3 class="section-title">Medical & Status</h3>
                                    <p class="section-description">Health notes and upload requirements for the student record.</p>
                                </div>
                            </div>
                            <div class="form-grid mt-4">
                                <div class="form-group">
                                    <label>Religion</label>
                                    <input name="religion" placeholder="e.g. Christian" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Family Doctor Name</label>
                                    <input name="doctor_name" placeholder="e.g. Dr. Johnson" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Family Doctor Phone</label>
                                    <input name="doctor_phone" type="tel" inputmode="tel" placeholder="Doctor phone" class="theme-input" />
                                </div>
                                <div class="form-group">
                                    <label>Family Doctor Address</label>
                                    <input name="doctor_address" placeholder="Doctor clinic address" class="theme-input" />
                                </div>
                                <div class="form-group sm:col-span-2">
                                    <label>Physical Information Notes</label>
                                    <textarea name="physical_notes" rows="3" placeholder="Special physical information, height, marks, etc." class="theme-input"></textarea>
                                </div>
                                <div class="form-group sm:col-span-2">
                                    <label>Medical Notes & Challenges</label>
                                    <textarea name="medical_notes" rows="3" placeholder="Allergies, chronic conditions, regular prescriptions..." class="theme-input"></textarea>
                                </div>
                                <div class="form-group sm:col-span-2">
                                    <label>Student Passport Photo</label>
                                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white px-6 py-6 text-center transition hover:border-blue-300 hover:bg-blue-50/40">
                                        <x-app-icon name="avatar" class="h-8 w-8 text-slate-500" />
                                        <span class="mt-2 text-sm font-extrabold text-slate-800">Click to upload image file</span>
                                        <span class="mt-1 text-xs font-semibold text-slate-500">PNG, JPG or JPEG up to 50MB</span>
                                        <input type="file" name="passport_photo" accept="image/*" class="hidden">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <x-slot name="actions">
                            <x-action-button type="button" variant="secondary" x-on:click="registrationOpen = false">Cancel</x-action-button>
                            <x-action-button type="submit" variant="success">Create Student Record</x-action-button>
                        </x-slot>
                    </x-form-card>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
