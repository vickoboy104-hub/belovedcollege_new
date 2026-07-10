<x-portal-layout>
    <x-slot name="header">
        <x-page-header 
            title="Printable fee lists, fee items, receipts, and unpaid balances" 
            description="Access and manage the school fee catalog, print custom billing breakdowns, and review payment progression."
            eyebrow="Finance records"
        />
    </x-slot>

    @php
        $financeRecordsNavItems = [
            ['key' => 'printable-fee-list', 'label' => 'Printable List', 'href' => route('admin.finance.records', ['section' => 'printable-fee-list'])],
            ['key' => 'created-fee-items', 'label' => 'Fee Catalog', 'href' => route('admin.finance.records', ['section' => 'created-fee-items'])],
            ['key' => 'student-balances', 'label' => 'Student Balances', 'href' => route('admin.finance.records', ['section' => 'student-balances'])],
            ['key' => 'class-bills', 'label' => 'Class Bills', 'href' => route('admin.finance.records', ['section' => 'class-bills'])],
            ['key' => 'payment-summary', 'label' => 'Payment Summary', 'href' => route('admin.finance.records', ['section' => 'payment-summary'])],
            ['key' => 'overpayment-tracker', 'label' => 'Overpayments', 'href' => route('admin.finance.records', ['section' => 'overpayment-tracker'])],
            ['key' => 'payment-progression', 'label' => 'Progression', 'href' => route('admin.finance.records', ['section' => 'payment-progression'])],
            ['key' => 'recent-payments', 'label' => 'Recent Payments', 'href' => route('admin.finance.records', ['section' => 'recent-payments'])],
        ];
    @endphp

    <div x-data="{ activeSection: @js($activeFinanceRecordsSection) }" class="grid gap-8">
        <!-- Printable class fee list -->
        <div
            x-show="activeSection === 'printable-fee-list'"
            x-data="{
                catalog: @js($classFeeCatalog),
                selectedClassId: @js(optional($classes->first())->id),
                selectedIds: [],
                showMobileDetails: false,
                get selectedClass() {
                    return this.catalog.find((entry) => entry.id === this.selectedClassId) ?? { items: [], name: '' };
                },
                get total() {
                    return this.selectedClass.items
                        .filter((item) => this.selectedIds.includes(item.id))
                        .reduce((sum, item) => sum + Number(item.amount || 0), 0);
                },
                syncDefaults() {
                    this.selectedIds = this.selectedClass.items.map((item) => item.id);
                },
                toggleAll() {
                    if (this.selectedIds.length === this.selectedClass.items.length) {
                        this.selectedIds = [];
                        return;
                    }
                    this.syncDefaults();
                }
            }"
            x-init="syncDefaults()"
            class="space-y-6"
        >
            <x-dashboard-card 
                title="Printable class fee list" 
                description="Pick a class, tick the fee items to include, then open the dedicated printable page that only shows the selected list and total."
                icon="finance" 
                accent="blue"
            >
                <x-slot name="actions">
                    <div class="flex flex-wrap gap-2.5">
                        <button type="button" @click="toggleAll()" class="btn inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[12px] font-bold text-xs uppercase tracking-wider transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-[0.98] btn-secondary bg-white text-[#1d4ed8] border border-[#c8d6ea] hover:bg-slate-50 hover:border-[#b0c4de] focus:ring-[#1d4ed8]">
                            Select or clear all
                        </button>
                        <form method="GET" action="{{ route('admin.finance.printable-fee-list') }}" target="_blank" class="contents">
                            <input type="hidden" name="class_id" :value="selectedClassId">
                            <template x-for="feeItemId in selectedIds" :key="feeItemId">
                                <input type="hidden" name="fee_item_ids[]" :value="feeItemId">
                            </template>
                            <button type="submit" x-bind:disabled="selectedIds.length === 0" class="btn inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[12px] font-bold text-xs uppercase tracking-wider transition-all duration-200 focus:outline-none active:scale-[0.98] bg-[#fbbf24] text-[#071833] border border-[#fbbf24] hover:bg-[#fbbf24]/90 focus:ring-[#fbbf24] font-extrabold disabled:opacity-50 disabled:cursor-not-allowed">
                                Open printable page
                            </button>
                        </form>
                    </div>
                </x-slot>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mt-2">
                    @foreach ($classes as $class)
                        <button
                            type="button"
                            @click="selectedClassId = {{ $class->id }}; syncDefaults(); showMobileDetails = true"
                            class="rounded-xl border p-4 text-left transition duration-200 active:scale-[0.98]"
                            :class="selectedClassId === {{ $class->id }} ? 'border-transparent text-white shadow-md' : 'border-[#c8d6ea] bg-white text-slate-900 hover:border-blue-300'"
                            :style="selectedClassId === {{ $class->id }} ? 'background-color: var(--theme-primary, #071833);' : ''"
                        >
                            <div class="font-bold text-sm" :class="selectedClassId === {{ $class->id }} ? 'text-white' : 'text-slate-900'">{{ $class->display_name }}</div>
                            <div class="mt-1 text-xs opacity-80" :class="selectedClassId === {{ $class->id }} ? 'text-blue-100' : 'text-slate-500'">Open fee list &rarr;</div>
                        </button>
                    @endforeach
                </div>
            </x-dashboard-card>

            <div :class="showMobileDetails ? 'mobile-details-active' : 'mobile-details-inactive'" class="details-card-wrapper">
                <div class="card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)]">
                    <!-- Unified Close & Actions Header Row -->
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                        <button
                            type="button"
                            @click="showMobileDetails = false"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-xs text-slate-700 uppercase tracking-wider transition justify-center shadow-sm w-full sm:w-auto"
                        >
                            &larr; Back to Class List
                        </button>
                        
                        <div class="flex flex-wrap sm:flex-nowrap gap-2.5 w-full sm:w-auto">
                            <button type="button" @click="toggleAll()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-xs text-slate-700 uppercase tracking-wider transition shadow-sm w-full sm:w-auto shrink-0">
                                Select or clear all
                            </button>
                            <form method="GET" action="{{ route('admin.finance.printable-fee-list') }}" target="_blank" class="contents">
                                <input type="hidden" name="class_id" :value="selectedClassId">
                                <template x-for="feeItemId in selectedIds" :key="feeItemId">
                                    <input type="hidden" name="fee_item_ids[]" :value="feeItemId">
                                </template>
                                <button type="submit" :disabled="selectedIds.length === 0" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs uppercase tracking-wider transition shadow-sm w-full sm:w-auto shrink-0">
                                    Open printable page
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-[10px] font-extrabold uppercase tracking-[0.25em] text-blue-600">Fee schedule</div>
                            <h3 class="display-font mt-1.5 text-xl font-bold text-slate-900" x-text="selectedClass.name || 'Select a class'"></h3>
                        </div>
                        <div class="text-xs font-semibold text-slate-500 bg-slate-50 border border-slate-100 rounded-lg px-3 py-1.5">
                            Only checked items will appear in the printable page.
                        </div>
                    </div>

                    <div class="mt-6 space-y-3" x-show="selectedClass.items.length">
                        <template x-for="item in selectedClass.items" :key="item.id">
                            <label class="flex items-start gap-4 rounded-xl border border-slate-150 px-4 py-3.5 hover:bg-slate-50/40 hover:border-slate-300 transition duration-150 cursor-pointer">
                                <input type="checkbox" :value="item.id" x-model="selectedIds" class="mt-1 rounded border-[#c8d6ea] text-blue-600 focus:ring-blue-500" />
                                <div class="flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm" x-text="item.name"></div>
                                            <div class="mt-1 text-xs text-slate-500 font-medium">
                                                Scope: <span x-text="item.scope" class="font-semibold text-slate-600"></span>
                                                <span x-show="item.term"> | Term: <span x-text="item.term" class="font-semibold text-slate-600"></span></span>
                                                <span x-show="item.due_date"> | Due: <span x-text="item.due_date" class="font-semibold text-slate-600"></span></span>
                                            </div>
                                        </div>
                                        <div class="text-sm font-bold text-[#071833] sm:text-right" x-text="`NGN ${Number(item.amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></div>
                                    </div>
                                </div>
                            </label>
                        </template>
                    </div>
                    
                    <div x-show="!selectedClass.items.length" class="mt-6">
                        <x-empty-state 
                            title="No fee items found" 
                            description="There are currently no fee items linked to this class in the catalog." 
                            icon="finance" 
                        />
                    </div>

                    <div class="mt-8 rounded-xl border border-slate-200 bg-slate-50/50 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-slate-500">Selected total</div>
                            <div class="display-font mt-1.5 text-3xl font-black text-slate-950" x-text="`NGN ${total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></div>
                        </div>
                        <div class="text-xs text-slate-400 font-semibold" x-show="selectedIds.length > 0">
                            Including <span x-text="selectedIds.length" class="text-slate-700 font-bold"></span> of <span x-text="selectedClass.items.length" class="text-slate-700 font-bold"></span> items
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Created fee items -->
        <div x-show="activeSection === 'created-fee-items'" class="space-y-6">
            <x-dashboard-card 
                title="Created fee items" 
                description="Browse and manage all registered fee items. Removing a fee item will delete it from future templates but leave existing student invoices unaffected."
                icon="finance" 
                accent="blue"
            >
                <x-slot name="actions">
                    <div class="rounded-full bg-blue-50 border border-blue-100 px-4 py-1.5 text-xs font-bold text-blue-700">
                        {{ $feeItems->count() }} Item{{ $feeItems->count() === 1 ? '' : 's' }} Catalogued
                    </div>
                </x-slot>

                <div class="mt-4">
                    <x-data-table :headers="['Fee Item', 'Scope', 'Session / Term', 'Amount', 'Due Date', 'Status', 'Actions']">
                    @forelse ($feeItems as $item)
                        @php
                            $feeItemPreview = [
                                'type' => 'bill',
                                'title' => $item->name,
                                'subtitle' => 'Fee Item - '.($item->schoolClass->display_name ?? 'All Classes'),
                                'avatar' => 'FI',
                                'profileUrl' => route('admin.finance.records', ['section' => 'created-fee-items']),
                                'ctaLabel' => 'View Full Details',
                                'fields' => [
                                    ['label' => 'Scope', 'value' => $item->schoolClass->display_name ?? 'All Classes'],
                                    ['label' => 'Academic Session', 'value' => $item->academicSession->name ?? 'Any session'],
                                    ['label' => 'Term', 'value' => $item->term->name ?? 'Any term'],
                                    ['label' => 'Amount', 'value' => 'NGN '.number_format((float) $item->amount, 2)],
                                    ['label' => 'Due Date', 'value' => $item->due_date?->format('M j, Y') ?? 'No due date'],
                                    ['label' => 'Status', 'value' => $item->is_mandatory ? 'Mandatory' : 'Optional'],
                                    ['label' => 'Description', 'value' => $item->description ?: 'No description'],
                                ],
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div class="table-person">
                                    <div class="table-avatar">FI</div>
                                    <div class="table-person-text">
                                        <strong>{{ $item->name }}</strong>
                                        <span>{{ $item->description ?: 'No description' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->schoolClass->display_name ?? 'All Classes' }}</td>
                            <td>
                                <span class="table-text-clip">
                                    {{ $item->academicSession->name ?? 'Any session' }} | {{ $item->term->name ?? 'Any term' }}
                                </span>
                            </td>
                            <td class="font-black text-slate-950">NGN {{ number_format((float) $item->amount, 2) }}</td>
                            <td>{{ $item->due_date?->format('M j, Y') ?? 'No due date' }}</td>
                            <td>
                                <x-status-badge :status="$item->is_mandatory ? 'Active' : 'Pending'" :label="$item->is_mandatory ? 'Mandatory' : 'Optional'" />
                            </td>
                            <td>
                                <div class="table-action-group">
                                    <button type="button" class="table-view-btn" data-preview='@json($feeItemPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                    <form method="POST" action="{{ route('admin.fee-items.destroy', $item) }}" onsubmit="return confirm('Are you sure you want to delete this fee item?');" class="contents">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-delete-btn">
                                        Delete
                                    </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                            <x-empty-state 
                                title="No catalogued fee items" 
                                description="Create fee items using the Finance Desk to populate the school fee schedule." 
                                icon="finance" 
                            />
                            </td>
                        </tr>
                    @endforelse
                    </x-data-table>
                </div>
            </x-dashboard-card>
        </div>

        <!-- Students with unpaid or part-paid fees -->
        <div x-show="activeSection === 'student-balances'" class="space-y-6">
            <x-dashboard-card 
                title="Students with unpaid or part-paid fees" 
                description="Search for a student and review unpaid items, part-payments, and remaining balances."
                icon="student" 
                accent="blue"
            >
                <x-slot name="actions">
                    <form method="GET" action="{{ route('admin.finance.records', ['section' => 'student-balances']) }}" class="flex w-full max-w-xl flex-col gap-2.5 sm:flex-row">
                        <input name="student_search" value="{{ $studentSearch }}" placeholder="Name, admission no, or class..." class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        <x-action-button type="submit" variant="primary">Search</x-action-button>
                    </form>
                </x-slot>

                <div class="mt-4 desktop-only-table">
                    <x-data-table :headers="['Student', 'Unpaid Fees', 'Paid / Part-paid Fees', 'Outstanding Total', 'Actions']">
                        @forelse ($studentBalanceRows as $row)
                            @php
                                $student = $row['student'];
                                $unpaidSummary = collect($row['unpaid_items'])->map(fn ($item) => $item['label'].' ('.$item['invoice_no'].')')->join(', ');
                                $progressSummary = collect($row['progress_items'])->map(fn ($item) => $item['label'].' ('.$item['invoice_no'].')')->join(', ');
                                $balancePreview = [
                                    'type' => 'debtor',
                                    'title' => $student->user->fullName(),
                                    'subtitle' => 'Finance Balance - '.($student->schoolClass->display_name ?? 'No class'),
                                    'avatar' => substr($student->user->first_name, 0, 1).substr($student->user->last_name, 0, 1),
                                    'profileUrl' => route('admin.students.show', $student),
                                    'ctaLabel' => 'View Full Details',
                                    'fields' => [
                                        ['label' => 'Admission No', 'value' => $student->admission_no ?: 'Pending'],
                                        ['label' => 'Class', 'value' => $student->schoolClass->display_name ?? 'No class'],
                                        ['label' => 'Unpaid Fees', 'value' => count($row['unpaid_items']).' item(s)'],
                                        ['label' => 'Paid / Part-paid', 'value' => count($row['progress_items']).' item(s)'],
                                        ['label' => 'Outstanding Total', 'value' => 'NGN '.number_format((float) $row['outstanding_total'], 2)],
                                        ['label' => 'Paid Total', 'value' => 'NGN '.number_format((float) $row['paid_total'], 2)],
                                        ['label' => 'Unpaid Summary', 'value' => $unpaidSummary ?: 'No unpaid fees'],
                                        ['label' => 'Progress Summary', 'value' => $progressSummary ?: 'No paid or part-paid fees'],
                                    ],
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td>
                                    <div class="table-person">
                                        <div class="table-avatar">{{ $balancePreview['avatar'] }}</div>
                                        <div class="table-person-text">
                                            <strong>{{ $student->user->fullName() }}</strong>
                                            <span>{{ $student->admission_no ?: 'Pending' }} | {{ $student->schoolClass->display_name ?? 'No class' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="table-text-clip">{{ count($row['unpaid_items']) }} item{{ count($row['unpaid_items']) === 1 ? '' : 's' }} - {{ $unpaidSummary ?: 'No unpaid fee items' }}</span>
                                </td>
                                <td>
                                    <span class="table-text-clip">{{ count($row['progress_items']) }} item{{ count($row['progress_items']) === 1 ? '' : 's' }} - {{ $progressSummary ?: 'No paid fee items yet' }}</span>
                                </td>
                                <td class="font-black text-slate-900 text-sm">
                                    NGN {{ number_format((float) $row['outstanding_total'], 2) }}
                                </td>
                                <td>
                                    <button type="button" class="table-view-btn" data-preview='@json($balancePreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center">
                                    <x-empty-state 
                                        title="No debt records found" 
                                        description="No students match the selected outstanding balance criteria." 
                                        icon="student" 
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>
                </div>

                <!-- Responsive Mobile View -->
                <div class="mobile-record-list mt-6 space-y-4 md:hidden">
                    @forelse ($studentBalanceRows as $row)
                        <article class="card bg-white border border-[#c8d6ea] rounded-[18px] p-5 shadow-sm">
                            <div class="flex items-start justify-between border-b border-slate-100 pb-3 mb-4">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $row['student']->user->fullName() }}</div>
                                    <div class="text-xs text-slate-500 font-semibold mt-0.5">{{ $row['student']->admission_no }} | {{ $row['student']->schoolClass->display_name ?? 'No class' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Debt Total</div>
                                    <div class="font-black text-slate-900 mt-0.5">NGN {{ number_format((float) $row['outstanding_total'], 2) }}</div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                @if (count($row['unpaid_items']) > 0)
                                    <div>
                                        <div class="text-[10px] font-extrabold uppercase tracking-wider text-rose-500 mb-2">Unpaid items</div>
                                        <div class="space-y-2">
                                            @foreach ($row['unpaid_items'] as $item)
                                                <div class="rounded-xl border border-rose-100 bg-rose-50/40 p-3 text-xs">
                                                    <div class="font-bold text-slate-900">{{ $item['label'] }}</div>
                                                    <div class="mt-1 text-[10px] text-slate-500 font-semibold">{{ $item['invoice_no'] }}</div>
                                                    <div class="mt-2 font-bold text-rose-700">Balance: NGN {{ number_format((float) $item['balance'], 2) }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (count($row['progress_items']) > 0)
                                    <div>
                                        <div class="text-[10px] font-extrabold uppercase tracking-wider text-amber-500 mb-2">Part-payments</div>
                                        <div class="space-y-2">
                                            @foreach ($row['progress_items'] as $item)
                                                <div class="rounded-xl border border-amber-100 bg-amber-50/30 p-3 text-xs">
                                                    <div class="font-bold text-slate-900">{{ $item['label'] }}</div>
                                                    <div class="mt-1 text-[10px] text-slate-500 font-semibold">{{ $item['invoice_no'] }}</div>
                                                    <div class="mt-2 space-y-0.5 font-semibold text-slate-600">
                                                        <div>Paid: <span class="font-bold text-emerald-600">NGN {{ number_format((float) $item['amount_paid'], 2) }}</span></div>
                                                        <div>Remaining: <span class="font-bold text-amber-700">NGN {{ number_format((float) $item['balance'], 2) }}</span></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <x-empty-state 
                            title="No debt records found" 
                            description="No students match the selected outstanding balance criteria." 
                            icon="student" 
                        />
                    @endforelse
                </div>
            </x-dashboard-card>
        </div>

        <!-- Class billing summary -->
        <div x-show="activeSection === 'class-bills'" class="space-y-6">
            <x-dashboard-card 
                title="Class billing summary" 
                description="Monitor expected fees, collections, and overall debt exposure dynamically structured per school class."
                icon="finance" 
                accent="blue"
            >
                <x-slot name="actions">
                    <div class="rounded-full bg-slate-100 border border-slate-200 px-4 py-1.5 text-xs font-bold text-slate-700">
                        {{ $classBillingRows->count() }} Class{{ $classBillingRows->count() === 1 ? '' : 'es' }} Tracked
                    </div>
                </x-slot>

                <div class="mt-4 desktop-only-table">
                    <x-data-table :headers="['Class', 'Students', 'Expected', 'Collected', 'Outstanding', 'Rate', 'Actions']">
                        @forelse ($classBillingRows as $row)
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
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4.5">
                                    <div class="font-bold text-slate-900">{{ $row['class']->display_name }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 mt-1 flex items-center gap-1.5">
                                        <span>{{ $row['invoice_count'] }} Invoice{{ $row['invoice_count'] === 1 ? '' : 's' }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span class="text-rose-600 font-bold">{{ $row['students_with_debt'] }} Debtor{{ $row['students_with_debt'] === 1 ? '' : 's' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4.5 text-slate-600 font-semibold">{{ $row['student_count'] }}</td>
                                <td class="px-6 py-4.5 text-slate-600 font-semibold">NGN {{ number_format((float) $row['expected_total'], 2) }}</td>
                                <td class="px-6 py-4.5 text-emerald-600 font-bold">NGN {{ number_format((float) $row['collected_total'], 2) }}</td>
                                <td class="px-6 py-4.5 text-slate-900 font-black">NGN {{ number_format((float) $row['outstanding_total'], 2) }}</td>
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
                                <td colspan="7" class="px-6 py-10 text-center">
                                    <x-empty-state 
                                        title="No class billing data" 
                                        description="No class bills have been created or posted yet." 
                                        icon="finance" 
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>
                </div>

                <!-- Responsive Mobile View -->
                <div class="mobile-record-list mt-6 space-y-4 md:hidden">
                    @forelse ($classBillingRows as $row)
                        <article class="card bg-white border border-[#c8d6ea] rounded-[18px] p-5 shadow-sm">
                            <div class="flex items-start justify-between border-b border-slate-100 pb-3 mb-4">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $row['class']->display_name }}</div>
                                    <div class="text-xs text-slate-500 font-semibold mt-0.5">{{ $row['student_count'] }} Students | {{ $row['invoice_count'] }} Invoices</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] font-extrabold uppercase tracking-wider text-rose-500">Debtors</div>
                                    <div class="font-black text-rose-700 text-sm mt-0.5">{{ $row['students_with_debt'] }} Student{{ $row['students_with_debt'] === 1 ? '' : 's' }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mb-4 text-xs font-semibold text-slate-600">
                                <div>
                                    <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Expected</div>
                                    <div class="font-bold text-slate-900 mt-0.5">NGN {{ number_format((float) $row['expected_total'], 2) }}</div>
                                </div>
                                <div>
                                    <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Collected</div>
                                    <div class="font-bold text-emerald-600 mt-0.5">NGN {{ number_format((float) $row['collected_total'], 2) }}</div>
                                </div>
                                <div class="col-span-2 border-t border-slate-100 pt-2 flex items-center justify-between">
                                    <div>
                                        <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Outstanding</div>
                                        <div class="font-bold text-slate-900 mt-0.5">NGN {{ number_format((float) $row['outstanding_total'], 2) }}</div>
                                    </div>
                                    <div class="text-right font-black text-emerald-700 text-sm">
                                        {{ number_format((float) $row['collection_rate'], 1) }}% Collection
                                    </div>
                                </div>
                            </div>
                            <x-progress-bar :percentage="$row['collection_rate']" color="green" />
                        </article>
                    @empty
                        <x-empty-state 
                            title="No class billing data" 
                            description="No class bills have been created or posted yet." 
                            icon="finance" 
                        />
                    @endforelse
                </div>
            </x-dashboard-card>
        </div>

        <!-- Payment summary -->
        <div x-show="activeSection === 'payment-summary'" class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">Payment Channels & Providers Breakdown</h2>
                    <p class="text-sm text-slate-500 mt-1">Monitor daily collection patterns, gateway provider volume, and payment channels.</p>
                </div>
                <x-action-button href="{{ route('admin.finance', ['section' => 'record-payment']) }}" variant="secondary" icon="finance">
                    Record new payment
                </x-action-button>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Providers -->
                <x-dashboard-card title="Gateway Providers" icon="finance" accent="blue">
                    <div class="space-y-3 mt-1">
                        @foreach ($paymentSummary['providerBreakdown'] as $provider)
                            <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-bold text-slate-900 text-sm">{{ $provider['label'] }}</div>
                                    <div class="rounded-full bg-blue-50 border border-blue-100 px-2.5 py-0.5 text-[10px] font-extrabold text-blue-700 uppercase">
                                        {{ $provider['count'] }} Entry{{ $provider['count'] === 1 ? '' : 'ies' }}
                                    </div>
                                </div>
                                <div class="mt-3 text-lg font-black text-slate-950">NGN {{ number_format((float) $provider['total'], 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                </x-dashboard-card>

                <!-- Channels -->
                <x-dashboard-card title="Payment Channels" icon="finance" accent="green">
                    <div class="space-y-3 mt-1">
                        @forelse ($paymentSummary['channelBreakdown']->take(8) as $channel)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-150 bg-slate-50/50 p-4 shadow-sm">
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $channel['channel'] }}</div>
                                    <div class="text-[10px] text-slate-500 font-semibold mt-1">
                                        Logged: {{ $channel['count'] }} Payee{{ $channel['count'] === 1 ? '' : 's' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-extrabold text-slate-900">NGN {{ number_format((float) $channel['total'], 2) }}</div>
                                </div>
                            </div>
                        @empty
                            <x-empty-state 
                                title="No channels logged" 
                                description="No payment channels have been logged in the system yet." 
                                icon="finance" 
                            />
                        @endforelse
                    </div>
                </x-dashboard-card>

                <!-- Daily Collections -->
                <x-dashboard-card title="Daily Collections" icon="finance" accent="purple">
                    <div class="space-y-3 mt-1">
                        @forelse ($paymentSummary['dailyCollection'] as $day)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-150 bg-slate-50/50 p-4 shadow-sm">
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">
                                        {{ $day['day'] !== 'Unknown' ? \Carbon\Carbon::parse($day['day'])->format('M j, Y') : 'Unknown date' }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-semibold mt-1">
                                        Volume: {{ $day['count'] }} Transaction{{ $day['count'] === 1 ? '' : 's' }}
                                    </div>
                                </div>
                                <div class="text-right font-extrabold text-slate-900 text-sm">
                                    NGN {{ number_format((float) $day['total'], 2) }}
                                </div>
                            </div>
                        @empty
                            <x-empty-state 
                                title="No daily receipts" 
                                description="No collections have been recorded recently." 
                                icon="finance" 
                            />
                        @endforelse
                    </div>
                </x-dashboard-card>
            </div>
        </div>

        <!-- Overpayment tracker -->
        <div x-show="activeSection === 'overpayment-tracker'" class="space-y-6">
            <x-dashboard-card 
                title="Overpayment tracker" 
                description="Surfaces student payments that exceed the corresponding invoice amount due. Monitor excess collections here."
                icon="finance" 
                accent="green"
            >
                <x-slot name="actions">
                    <div class="rounded-full bg-emerald-50 border border-emerald-100 px-4 py-1.5 text-xs font-bold text-emerald-700">
                        {{ $overpaymentRows->count() }} Overpayments Logged
                    </div>
                </x-slot>

                <div class="mt-4">
                    <x-data-table :headers="['Student', 'Invoice', 'Amount Due', 'Amount Paid', 'Overpayment', 'Last Payment', 'Actions']">
                        @forelse ($overpaymentRows as $row)
                            @php
                                $overpaymentPreview = [
                                    'type' => 'payment',
                                    'title' => $row['student']->user->fullName(),
                                    'subtitle' => 'Overpayment - '.$row['invoice']->invoice_no,
                                    'avatar' => substr($row['student']->user->first_name, 0, 1).substr($row['student']->user->last_name, 0, 1),
                                    'profileUrl' => route('admin.students.show', $row['student']),
                                    'ctaLabel' => 'View Full Details',
                                    'fields' => [
                                        ['label' => 'Invoice No', 'value' => $row['invoice']->invoice_no],
                                        ['label' => 'Fee Item', 'value' => $row['invoice']->feeItem->name ?? 'Direct Invoice'],
                                        ['label' => 'Amount Due', 'value' => 'NGN '.number_format((float) $row['invoice']->amount_due, 2)],
                                        ['label' => 'Amount Paid', 'value' => 'NGN '.number_format((float) $row['invoice']->amount_paid, 2)],
                                        ['label' => 'Overpayment', 'value' => 'NGN '.number_format((float) $row['overpayment'], 2)],
                                        ['label' => 'Last Payment', 'value' => $row['last_payment_at']?->format('M j, Y g:i A') ?? 'Not Available'],
                                    ],
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td>
                                    <div class="table-person">
                                        <div class="table-avatar">{{ $overpaymentPreview['avatar'] }}</div>
                                        <div class="table-person-text">
                                            <strong>{{ $row['student']->user->fullName() }}</strong>
                                            <span>{{ $row['student']->admission_no ?: 'Pending' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4.5 text-slate-600 font-medium">
                                    <div class="font-bold text-slate-800">{{ $row['invoice']->invoice_no }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $row['invoice']->feeItem->name ?? 'Direct Invoice' }}</div>
                                </td>
                                <td class="px-6 py-4.5 text-slate-600 font-semibold">NGN {{ number_format((float) $row['invoice']->amount_due, 2) }}</td>
                                <td class="px-6 py-4.5 text-slate-600 font-semibold">NGN {{ number_format((float) $row['invoice']->amount_paid, 2) }}</td>
                                <td class="px-6 py-4.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border bg-emerald-50 text-emerald-700 border-emerald-250">
                                        NGN {{ number_format((float) $row['overpayment'], 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5 text-slate-500 font-medium text-xs">
                                    {{ $row['last_payment_at']?->format('M j, Y g:i A') ?? 'Not Available' }}
                                </td>
                                <td>
                                    <button type="button" class="table-view-btn" data-preview='@json($overpaymentPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center">
                                    <x-empty-state 
                                        title="No overpayments detected" 
                                        description="All posted student payments fall exactly within or under invoice totals." 
                                        icon="finance" 
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>
                </div>
            </x-dashboard-card>
        </div>

        <!-- Payment progression -->
        <div x-show="activeSection === 'payment-progression'" class="space-y-6">
            <x-dashboard-card 
                title="Payment progression" 
                description="Track invoices from partial installments up to final clearance. Monitor overall debt amortization."
                icon="finance" 
                accent="blue"
            >
                <x-slot name="actions">
                    <div class="rounded-full bg-blue-50 border border-blue-100 px-4 py-1.5 text-xs font-bold text-blue-700">
                        Tracking {{ $paymentProgressionRows->count() }} Invoices
                    </div>
                </x-slot>

                <div class="mt-4">
                    <x-data-table :headers="['Student', 'Invoice', 'Amount Due', 'Amount Paid', 'Balance', 'Progress', 'Last Activity', 'Actions']">
                        @forelse ($paymentProgressionRows as $row)
                            @php
                                $recentPaymentSummary = $row['recent_payments']->map(fn ($payment) => $payment->provider->label().' - NGN '.number_format((float) $payment->amount, 2))->join(', ');
                                $progressionPreview = [
                                    'type' => 'payment',
                                    'title' => $row['student']->user->fullName(),
                                    'subtitle' => 'Payment Progression - '.$row['invoice']->invoice_no,
                                    'avatar' => substr($row['student']->user->first_name, 0, 1).substr($row['student']->user->last_name, 0, 1),
                                    'profileUrl' => route('admin.students.show', $row['student']),
                                    'ctaLabel' => 'View Full Details',
                                    'fields' => [
                                        ['label' => 'Invoice No', 'value' => $row['invoice']->invoice_no],
                                        ['label' => 'Fee Item', 'value' => $row['invoice']->feeItem->name ?? 'Direct invoice'],
                                        ['label' => 'Invoice Status', 'value' => ucfirst((string) $row['invoice']->status)],
                                        ['label' => 'Amount Due', 'value' => 'NGN '.number_format((float) $row['invoice']->amount_due, 2)],
                                        ['label' => 'Amount Paid', 'value' => 'NGN '.number_format((float) $row['invoice']->amount_paid, 2)],
                                        ['label' => 'Remaining Balance', 'value' => 'NGN '.number_format((float) $row['invoice']->balance, 2)],
                                        ['label' => 'Progress', 'value' => number_format((float) $row['progress'], 1).'% cleared'],
                                        ['label' => 'Recent Payments', 'value' => $recentPaymentSummary ?: 'No logged payments'],
                                    ],
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <div class="table-person">
                                        <div class="table-avatar">{{ $progressionPreview['avatar'] }}</div>
                                        <div class="table-person-text">
                                            <strong>{{ $row['student']->user->fullName() }}</strong>
                                            <span>{{ $row['student']->admission_no ?: 'Pending' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-bold text-slate-800">{{ $row['invoice']->invoice_no }}</div>
                                    <div class="table-text-clip">{{ $row['invoice']->feeItem->name ?? 'Direct invoice' }}</div>
                                </td>
                                <td>NGN {{ number_format((float) $row['invoice']->amount_due, 2) }}</td>
                                <td class="font-bold text-emerald-600">NGN {{ number_format((float) $row['invoice']->amount_paid, 2) }}</td>
                                <td class="font-extrabold text-slate-900">NGN {{ number_format((float) $row['invoice']->balance, 2) }}</td>
                                <td>
                                    <span class="font-bold">{{ number_format((float) $row['progress'], 1) }}%</span>
                                </td>
                                <td>{{ $row['last_payment_at']?->format('M j, Y g:i A') ?? 'No logged payments' }}</td>
                                <td>
                                    <button type="button" class="table-view-btn" data-preview='@json($progressionPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-empty-state 
                                        title="No payment progression tracked" 
                                        description="No partial or installment invoices have been logged in the system yet." 
                                        icon="finance" 
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>
                </div>
            </x-dashboard-card>
        </div>

        <!-- Recent payments and receipts -->
        <div x-show="activeSection === 'recent-payments'" class="space-y-6"
            x-data="{
                selectedPayments: [],
                receiptsData: {{ $payments->map(fn($p) => ['id' => $p->id, 'html' => view('admin.finance._receipt-card', ['payment' => $p, 'schoolSettings' => $schoolSettings])->render()])->values() }},
                get selectedReceipts() {
                    return this.receiptsData.filter(r => this.selectedPayments.includes(r.id));
                },
                toggleAll(e) {
                    if (this.selectedPayments.length === this.receiptsData.length) {
                        this.selectedPayments = [];
                    } else {
                        this.selectedPayments = this.receiptsData.map(r => r.id);
                    }
                }
            }"
        >
            <x-dashboard-card 
                title="Recent payments and receipts" 
                description="Monitor all successfully processed fees, record physical payments, and view official school receipts."
                icon="finance" 
                accent="blue"
            >
                <x-slot name="actions">
                    <button 
                        type="button"
                        @click="$dispatch('open-print-settings', { receipts: selectedReceipts })"
                        :disabled="selectedPayments.length === 0"
                        class="btn btn-primary"
                    >
                        Print Settings
                    </button>
                </x-slot>

                <div class="mt-4 desktop-only-table">
                    <x-data-table :headers="['<input type=\'checkbox\' @change=\'toggleAll($event)\' />', 'Student', 'Provider', 'Amount', 'Receipt No.', 'Status', 'Actions']">
                        @forelse ($payments as $payment)
                            @php
                                $paymentPreview = [
                                    'type' => 'payment',
                                    'title' => $payment->student->user->fullName(),
                                    'subtitle' => 'Payment Receipt - '.($payment->receipt_no ?: $payment->reference),
                                    'avatar' => substr($payment->student->user->first_name, 0, 1).substr($payment->student->user->last_name, 0, 1),
                                    'profileUrl' => route('payments.receipt', $payment),
                                    'ctaLabel' => 'View Full Details',
                                    'fields' => [
                                        ['label' => 'Reference', 'value' => $payment->reference],
                                        ['label' => 'Receipt No', 'value' => $payment->receipt_no ?: 'Open Receipt'],
                                        ['label' => 'Provider', 'value' => $payment->provider->label()],
                                        ['label' => 'Channel', 'value' => $payment->channel ?: 'Not recorded'],
                                        ['label' => 'Amount', 'value' => 'NGN '.number_format((float) $payment->amount, 2)],
                                        ['label' => 'Status', 'value' => $payment->status->label()],
                                        ['label' => 'Paid At', 'value' => $payment->paid_at?->format('M j, Y g:i A') ?? 'Pending'],
                                    ],
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4.5">
                                    <input type="checkbox" x-model="selectedPayments" value="{{ $payment->id }}" />
                                </td>
                                <td>
                                    <div class="table-person">
                                        <div class="table-avatar">{{ $paymentPreview['avatar'] }}</div>
                                        <div class="table-person-text">
                                            <strong>{{ $payment->student->user->fullName() }}</strong>
                                            <span>Ref: {{ $payment->reference }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4.5 text-slate-600 font-bold text-xs uppercase tracking-wide">{{ $payment->provider->label() }}</td>
                                <td class="px-6 py-4.5 text-slate-900 font-black text-sm">NGN {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="px-6 py-4.5">{{ $payment->receipt_no ?: 'Open Receipt' }}</td>
                                <td class="px-6 py-4.5">
                                    <x-status-badge :status="$payment->status->label()" />
                                </td>
                                <td>
                                    <button type="button" class="table-view-btn" data-preview='@json($paymentPreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center">
                                    <x-empty-state 
                                        title="No payment records found" 
                                        description="No student receipts or payments have been created yet." 
                                        icon="finance" 
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>
                </div>

                <!-- Responsive Mobile View -->
                <div class="mobile-record-list mt-6 space-y-4 md:hidden">
                    @forelse ($payments as $payment)
                        <article class="card bg-white border border-[#c8d6ea] rounded-[18px] p-5 shadow-sm">
                            <div class="flex items-start justify-between border-b border-slate-100 pb-3 mb-4">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $payment->student->user->fullName() }}</div>
                                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5">Ref: {{ $payment->reference }}</div>
                                </div>
                                <x-status-badge :status="$payment->status->label()" />
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs font-semibold text-slate-600 mb-4">
                                <div>
                                    <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Gateway Provider</div>
                                    <div class="font-bold text-slate-900 mt-0.5">{{ $payment->provider->label() }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Amount</div>
                                    <div class="font-black text-slate-950 mt-0.5">NGN {{ number_format((float) $payment->amount, 2) }}</div>
                                </div>
                            </div>
                            
                            <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-[#c8d6ea] hover:bg-slate-50 font-bold text-xs text-blue-600 transition">
                                <x-app-icon name="reports" class="h-3.5 w-3.5" />
                                <span>{{ $payment->receipt_no ?: 'Open Receipt' }}</span>
                            </a>
                        </article>
                    @empty
                        <x-empty-state 
                            title="No payment records found" 
                            description="No student receipts or payments have been created yet." 
                            icon="finance" 
                        />
                    @endforelse
                </div>
            </x-dashboard-card>
        </div>
        <x-entity-preview-modal />
    </div>
</x-portal-layout>
