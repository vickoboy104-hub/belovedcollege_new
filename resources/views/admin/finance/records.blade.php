<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Finance records</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">Printable fee lists, fee items, receipts, and unpaid balances</h1>
            </div>
            @include('admin.finance._switcher')
        </div>
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
        <x-section-nav :items="$financeRecordsNavItems" :active="$activeFinanceRecordsSection" />

        <section
        class="section-card"
        x-show="activeSection === 'printable-fee-list'"
        x-data="{
            catalog: @js($classFeeCatalog),
            selectedClassId: @js(optional($classes->first())->id),
            selectedIds: [],
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
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Printable class fee list</h2>
                <p class="mt-2 text-sm text-slate-500">Pick a class, tick the fee items to include, then open the dedicated printable page that only shows the selected list and total.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" @click="toggleAll()" class="theme-button-secondary">Select or clear all</button>
                <form method="GET" action="{{ route('admin.finance.printable-fee-list') }}" target="_blank" class="contents">
                    <input type="hidden" name="class_id" :value="selectedClassId">
                    <template x-for="feeItemId in selectedIds" :key="feeItemId">
                        <input type="hidden" name="fee_item_ids[]" :value="feeItemId">
                    </template>
                    <button type="submit" class="theme-button" :disabled="selectedIds.length === 0">Open printable page</button>
                </form>
            </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($classes as $class)
                <button
                    type="button"
                    @click="selectedClassId = {{ $class->id }}; syncDefaults()"
                    class="rounded-2xl border px-4 py-4 text-left transition"
                    :class="selectedClassId === {{ $class->id }} ? 'border-transparent text-white shadow-lg' : 'border-slate-200 bg-white text-slate-900'"
                    style="background-color: transparent;"
                    :style="selectedClassId === {{ $class->id }} ? 'background-color: var(--theme-primary);' : ''"
                >
                    <div class="font-semibold">{{ $class->display_name }}</div>
                    <div class="mt-1 text-xs opacity-80">Open fee list</div>
                </button>
            @endforeach
        </div>

        <div class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-6">
            <div class="flex flex-col gap-2 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Fee schedule</div>
                    <h3 class="display-font mt-2 text-2xl font-bold text-slate-950" x-text="selectedClass.name || 'Select a class'"></h3>
                </div>
                <div class="text-sm text-slate-500">Only checked items will appear in the printable page.</div>
            </div>

            <div class="mt-6 space-y-3" x-show="selectedClass.items.length">
                <template x-for="item in selectedClass.items" :key="item.id">
                    <label class="flex items-start gap-4 rounded-2xl border border-slate-200 px-4 py-4">
                        <input type="checkbox" :value="item.id" x-model="selectedIds" class="mt-1 rounded border-slate-300" />
                        <div class="flex-1">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="font-semibold text-slate-900" x-text="item.name"></div>
                                    <div class="mt-1 text-sm text-slate-500">
                                        <span x-text="item.scope"></span>
                                        <span x-show="item.term"> | <span x-text="item.term"></span></span>
                                        <span x-show="item.due_date"> | Due <span x-text="item.due_date"></span></span>
                                    </div>
                                </div>
                                <div class="text-sm font-bold text-slate-900" x-text="`NGN ${Number(item.amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></div>
                            </div>
                        </div>
                    </label>
                </template>
            </div>
            <div x-show="!selectedClass.items.length" class="mt-6 rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No fee items are linked to this class yet.</div>

            <div class="mt-8 rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Selected total</div>
                <div class="display-font mt-3 text-3xl font-bold text-slate-950" x-text="`NGN ${total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></div>
            </div>
        </div>
    </section>

    <div class="grid gap-8 xl:grid-cols-[0.9fr,1.1fr]" x-show="['created-fee-items', 'student-balances'].includes(activeSection)">
        <section class="section-card" x-show="activeSection === 'created-fee-items'">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">Created fee items</h2>
                    <p class="mt-2 text-sm text-slate-500">Delete any fee item you no longer want. Existing linked invoices will remain, but the fee item itself is removed.</p>
                </div>
                <div class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                    {{ $feeItems->count() }} item{{ $feeItems->count() === 1 ? '' : 's' }}
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($feeItems as $item)
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $item->schoolClass->display_name ?? 'All classes' }} @if($item->term) | {{ $item->term->name }} @endif</div>
                                <div class="mt-2 text-sm font-bold text-slate-900">NGN {{ number_format((float) $item->amount, 2) }}</div>
                            </div>
                            <form method="POST" action="{{ route('admin.fee-items.destroy', $item) }}" onsubmit="return confirm('Delete this fee item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-full border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No fee items yet.</div>
                @endforelse
            </div>
        </section>

        <section class="section-card" x-show="activeSection === 'student-balances'">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="display-font text-2xl font-bold text-slate-950">Students with unpaid or part-paid fees</h2>
                    <p class="mt-2 text-sm text-slate-500">Search for a student and review unpaid items, part-payments, and remaining balances.</p>
                </div>
                <form method="GET" action="{{ route('admin.finance.records', ['section' => 'student-balances']) }}" class="flex w-full max-w-xl flex-col gap-3 sm:flex-row">
                    <input name="student_search" value="{{ $studentSearch }}" placeholder="Search by student name, admission number, email, or class" class="theme-input" />
                    <button type="submit" class="theme-button">Search</button>
                </form>
            </div>

            <div class="desktop-table table-wrap mt-5">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-slate-500">
                        <tr>
                            <th class="pb-3">Student</th>
                            <th class="pb-3">Unpaid fees</th>
                            <th class="pb-3">Paid / part-paid fees</th>
                            <th class="pb-3">Outstanding total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($studentBalanceRows as $row)
                            <tr>
                                <td class="py-4 align-top">
                                    <div class="font-semibold text-slate-900">{{ $row['student']->user->fullName() }}</div>
                                    <div class="text-xs text-slate-500">{{ $row['student']->admission_no }} | {{ $row['student']->schoolClass->display_name ?? 'No class' }}</div>
                                </td>
                                <td class="py-4 align-top">
                                    <div class="space-y-2">
                                        @forelse ($row['unpaid_items'] as $item)
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3">
                                                <div class="font-semibold text-slate-900">{{ $item['label'] }}</div>
                                                <div class="mt-1 text-xs text-slate-500">{{ $item['invoice_no'] }} | {{ ucfirst($item['status']) }}</div>
                                                <div class="mt-2 text-sm text-slate-700">Balance: NGN {{ number_format((float) $item['balance'], 2) }}</div>
                                            </div>
                                        @empty
                                            <div class="text-sm text-slate-500">No unpaid fee items.</div>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-4 align-top">
                                    <div class="space-y-2">
                                        @forelse ($row['progress_items'] as $item)
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3">
                                                <div class="font-semibold text-slate-900">{{ $item['label'] }}</div>
                                                <div class="mt-1 text-xs text-slate-500">{{ $item['invoice_no'] }} | {{ ucfirst($item['status']) }}</div>
                                                <div class="mt-2 text-sm text-slate-700">Paid: NGN {{ number_format((float) $item['amount_paid'], 2) }}</div>
                                                <div class="text-sm text-slate-700">Remaining: NGN {{ number_format((float) $item['balance'], 2) }}</div>
                                            </div>
                                        @empty
                                            <div class="text-sm text-slate-500">No paid fee items yet.</div>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-4 align-top font-semibold text-slate-900">NGN {{ number_format((float) $row['outstanding_total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-slate-500">No unpaid or part-paid fee records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-record-list mt-5">
                @forelse ($studentBalanceRows as $row)
                    <article class="mobile-record-card">
                        <div class="mobile-record-title">{{ $row['student']->user->fullName() }}</div>
                        <div class="mobile-record-subtitle">{{ $row['student']->admission_no }} | {{ $row['student']->schoolClass->display_name ?? 'No class' }}</div>
                        <div class="mobile-record-grid mt-4">
                            <div class="mobile-record-item">
                                <div class="mobile-record-label">Outstanding total</div>
                                <div class="mobile-record-value">NGN {{ number_format((float) $row['outstanding_total'], 2) }}</div>
                            </div>
                            <div class="mobile-record-item md:col-span-2">
                                <div class="mobile-record-label">Unpaid fees</div>
                                <div class="space-y-2">
                                    @forelse ($row['unpaid_items'] as $item)
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-700">
                                            <div class="font-semibold text-slate-900">{{ $item['label'] }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $item['invoice_no'] }} | {{ ucfirst($item['status']) }}</div>
                                            <div class="mt-2">Balance: NGN {{ number_format((float) $item['balance'], 2) }}</div>
                                        </div>
                                    @empty
                                        <div class="text-sm text-slate-500">No unpaid fee items.</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="mobile-record-item md:col-span-2">
                                <div class="mobile-record-label">Paid / part-paid</div>
                                <div class="space-y-2">
                                    @forelse ($row['progress_items'] as $item)
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-700">
                                            <div class="font-semibold text-slate-900">{{ $item['label'] }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $item['invoice_no'] }} | {{ ucfirst($item['status']) }}</div>
                                            <div class="mt-2">Paid: NGN {{ number_format((float) $item['amount_paid'], 2) }}</div>
                                            <div>Remaining: NGN {{ number_format((float) $item['balance'], 2) }}</div>
                                        </div>
                                    @empty
                                        <div class="text-sm text-slate-500">No paid fee items yet.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <article class="mobile-record-card">
                        <div class="mobile-record-value text-slate-500">No unpaid or part-paid fee records found.</div>
                    </article>
                @endforelse
            </div>
        </section>
    </div>

    <section class="section-card" x-show="activeSection === 'class-bills'">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Class billing summary</h2>
                <p class="mt-2 text-sm text-slate-500">See expected fees, collections, and debt exposure for each class the way a school office would monitor billing health.</p>
            </div>
            <div class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                {{ $classBillingRows->count() }} class{{ $classBillingRows->count() === 1 ? '' : 'es' }}
            </div>
        </div>

        <div class="desktop-table table-wrap mt-5">
            <table class="min-w-full text-left text-sm">
                <thead class="text-slate-500">
                    <tr>
                        <th class="pb-3">Class</th>
                        <th class="pb-3">Students</th>
                        <th class="pb-3">Expected</th>
                        <th class="pb-3">Collected</th>
                        <th class="pb-3">Outstanding</th>
                        <th class="pb-3">Collection rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($classBillingRows as $row)
                        <tr>
                            <td class="py-4">
                                <div class="font-semibold text-slate-900">{{ $row['class']->display_name }}</div>
                                <div class="text-xs text-slate-500">{{ $row['invoice_count'] }} invoice{{ $row['invoice_count'] === 1 ? '' : 's' }} | {{ $row['students_with_debt'] }} debtor{{ $row['students_with_debt'] === 1 ? '' : 's' }}</div>
                            </td>
                            <td class="py-4 text-slate-600">{{ $row['student_count'] }}</td>
                            <td class="py-4 text-slate-600">NGN {{ number_format((float) $row['expected_total'], 2) }}</td>
                            <td class="py-4 text-slate-600">NGN {{ number_format((float) $row['collected_total'], 2) }}</td>
                            <td class="py-4 font-semibold text-slate-900">NGN {{ number_format((float) $row['outstanding_total'], 2) }}</td>
                            <td class="py-4 text-slate-600">{{ number_format((float) $row['collection_rate'], 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-slate-500">No class billing data yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mobile-record-list mt-5">
            @forelse ($classBillingRows as $row)
                <article class="mobile-record-card">
                    <div class="mobile-record-title">{{ $row['class']->display_name }}</div>
                    <div class="mobile-record-subtitle">{{ $row['invoice_count'] }} invoice{{ $row['invoice_count'] === 1 ? '' : 's' }} | {{ $row['students_with_debt'] }} debtor{{ $row['students_with_debt'] === 1 ? '' : 's' }}</div>
                    <div class="mobile-record-grid mt-4">
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Students</div>
                            <div class="mobile-record-value">{{ $row['student_count'] }}</div>
                        </div>
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Rate</div>
                            <div class="mobile-record-value">{{ number_format((float) $row['collection_rate'], 1) }}%</div>
                        </div>
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Expected</div>
                            <div class="mobile-record-value">NGN {{ number_format((float) $row['expected_total'], 2) }}</div>
                        </div>
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Collected</div>
                            <div class="mobile-record-value">NGN {{ number_format((float) $row['collected_total'], 2) }}</div>
                        </div>
                        <div class="mobile-record-item md:col-span-2">
                            <div class="mobile-record-label">Outstanding</div>
                            <div class="mobile-record-value">NGN {{ number_format((float) $row['outstanding_total'], 2) }}</div>
                        </div>
                    </div>
                </article>
            @empty
                <article class="mobile-record-card">
                    <div class="mobile-record-value text-slate-500">No class billing data yet.</div>
                </article>
            @endforelse
        </div>
    </section>

    <section class="section-card" x-show="activeSection === 'payment-summary'">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Payment summary</h2>
                <p class="mt-2 text-sm text-slate-500">Track how payments are coming in across providers, office channels, and recent collection days.</p>
            </div>
            <a href="{{ route('admin.finance', ['section' => 'record-payment']) }}" class="theme-button-secondary">Record new payment</a>
        </div>

        <div class="mt-6 grid gap-5 xl:grid-cols-3">
            <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 xl:col-span-1">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Providers</div>
                <div class="mt-4 space-y-3">
                    @foreach ($paymentSummary['providerBreakdown'] as $provider)
                        <div class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-4">
                            <div class="font-semibold text-slate-900">{{ $provider['label'] }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ $provider['count'] }} payment{{ $provider['count'] === 1 ? '' : 's' }}</div>
                            <div class="mt-2 text-sm font-bold text-slate-900">NGN {{ number_format((float) $provider['total'], 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 xl:col-span-1">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Channels</div>
                <div class="mt-4 space-y-3">
                    @forelse ($paymentSummary['channelBreakdown']->take(8) as $channel)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/90 px-4 py-4">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $channel['channel'] }}</div>
                                <div class="text-sm text-slate-500">{{ $channel['count'] }} entry{{ $channel['count'] === 1 ? '' : 'ies' }}</div>
                            </div>
                            <div class="text-sm font-bold text-slate-900">NGN {{ number_format((float) $channel['total'], 2) }}</div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No payment channels recorded yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5 xl:col-span-1">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Recent collection days</div>
                <div class="mt-4 space-y-3">
                    @forelse ($paymentSummary['dailyCollection'] as $day)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/90 px-4 py-4">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $day['day'] !== 'Unknown' ? \Carbon\Carbon::parse($day['day'])->format('M j, Y') : 'Unknown date' }}</div>
                                <div class="text-sm text-slate-500">{{ $day['count'] }} payment{{ $day['count'] === 1 ? '' : 's' }}</div>
                            </div>
                            <div class="text-sm font-bold text-slate-900">NGN {{ number_format((float) $day['total'], 2) }}</div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No recent collections yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="section-card" x-show="activeSection === 'overpayment-tracker'">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Overpayment tracker</h2>
                <p class="mt-2 text-sm text-slate-500">When office staff records more than the current invoice balance, the excess is surfaced here instead of being silently ignored.</p>
            </div>
            <div class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">{{ $overpaymentRows->count() }} overpaid invoice{{ $overpaymentRows->count() === 1 ? '' : 's' }}</div>
        </div>

        <div class="desktop-table table-wrap mt-5">
            <table class="min-w-full text-left text-sm">
                <thead class="text-slate-500">
                    <tr>
                        <th class="pb-3">Student</th>
                        <th class="pb-3">Invoice</th>
                        <th class="pb-3">Amount due</th>
                        <th class="pb-3">Amount paid</th>
                        <th class="pb-3">Overpayment</th>
                        <th class="pb-3">Last payment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($overpaymentRows as $row)
                        <tr>
                            <td class="py-4">
                                <div class="font-semibold text-slate-900">{{ $row['student']->user->fullName() }}</div>
                                <div class="text-xs text-slate-500">{{ $row['student']->admission_no }}</div>
                            </td>
                            <td class="py-4 text-slate-600">
                                <div>{{ $row['invoice']->invoice_no }}</div>
                                <div class="text-xs text-slate-500">{{ $row['invoice']->feeItem->name ?? 'Direct invoice' }}</div>
                            </td>
                            <td class="py-4 text-slate-600">NGN {{ number_format((float) $row['invoice']->amount_due, 2) }}</td>
                            <td class="py-4 text-slate-600">NGN {{ number_format((float) $row['invoice']->amount_paid, 2) }}</td>
                            <td class="py-4 font-semibold text-emerald-700">NGN {{ number_format((float) $row['overpayment'], 2) }}</td>
                            <td class="py-4 text-slate-600">{{ $row['last_payment_at']?->format('M j, Y g:i A') ?? 'Not available' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-slate-500">No overpayments have been recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="section-card" x-show="activeSection === 'payment-progression'">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="display-font text-2xl font-bold text-slate-950">Payment progression</h2>
                <p class="mt-2 text-sm text-slate-500">Track each invoice from first payment to final settlement and spot part-paid items before they become long-term debt.</p>
            </div>
            <div class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">{{ $paymentProgressionRows->count() }} tracked invoice{{ $paymentProgressionRows->count() === 1 ? '' : 's' }}</div>
        </div>

        <div class="mt-6 space-y-5">
            @forelse ($paymentProgressionRows as $row)
                <article class="rounded-[1.75rem] border border-slate-200 px-5 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="font-semibold text-slate-900">{{ $row['student']->user->fullName() }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ $row['invoice']->invoice_no }} | {{ $row['invoice']->feeItem->name ?? 'Direct invoice' }}</div>
                            <div class="mt-2 text-xs uppercase tracking-[0.2em] text-slate-500">Last payment {{ $row['last_payment_at']?->format('M j, Y g:i A') ?? 'not recorded' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="display-font text-2xl font-bold text-slate-950">{{ number_format((float) $row['progress'], 1) }}%</div>
                            <div class="mt-1 text-sm text-slate-500">NGN {{ number_format((float) $row['invoice']->amount_paid, 2) }} of NGN {{ number_format((float) $row['invoice']->amount_due, 2) }}</div>
                            @if ($row['overpayment'] > 0)
                                <div class="mt-1 text-sm font-semibold text-emerald-700">Overpaid by NGN {{ number_format((float) $row['overpayment'], 2) }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full" style="width: {{ min(100, max(0, (float) $row['progress'])) }}%; background: linear-gradient(135deg, var(--theme-primary), var(--theme-accent));"></div>
                    </div>

                    <div class="mt-5 grid gap-3 md:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Status</div>
                            <div class="mt-2 font-semibold text-slate-900">{{ ucfirst($row['invoice']->status) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Balance</div>
                            <div class="mt-2 font-semibold text-slate-900">NGN {{ number_format((float) $row['invoice']->balance, 2) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Payments posted</div>
                            <div class="mt-2 font-semibold text-slate-900">{{ $row['invoice']->payments->count() }}</div>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($row['recent_payments'] as $payment)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/90 px-4 py-4">
                                <div>
                                    <div class="font-semibold text-slate-900">{{ $payment->provider->label() }}</div>
                                    <div class="text-sm text-slate-500">{{ $payment->reference }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-slate-900">NGN {{ number_format((float) $payment->amount, 2) }}</div>
                                    <div class="text-xs text-slate-500">{{ $payment->paid_at?->format('M j, Y g:i A') ?? 'Pending' }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No payment entries are linked to this invoice yet.</div>
                        @endforelse
                    </div>
                </article>
            @empty
                <div class="rounded-[2rem] border border-dashed border-slate-300 px-6 py-8 text-sm text-slate-500">No invoice progression data is available yet.</div>
            @endforelse
        </div>
    </section>

    <section class="section-card" x-show="activeSection === 'recent-payments'">
        <h2 class="display-font text-2xl font-bold text-slate-950">Recent payments and receipts</h2>
        <div class="desktop-table table-wrap mt-5">
            <table class="min-w-full text-left text-sm">
                <thead class="text-slate-500">
                    <tr>
                        <th class="pb-3">Student</th>
                        <th class="pb-3">Provider</th>
                        <th class="pb-3">Amount</th>
                        <th class="pb-3">Receipt</th>
                        <th class="pb-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="py-4">
                                <div class="font-semibold text-slate-900">{{ $payment->student->user->fullName() }}</div>
                                <div class="text-xs text-slate-500">{{ $payment->reference }}</div>
                            </td>
                            <td class="py-4 text-slate-600">{{ $payment->provider->label() }}</td>
                            <td class="py-4 text-slate-600">NGN {{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="py-4">
                                <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="font-semibold text-[color:var(--theme-primary)]">
                                    {{ $payment->receipt_no ?: 'View receipt' }}
                                </a>
                            </td>
                            <td class="py-4 text-slate-600">{{ $payment->status->label() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-slate-500">No payments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mobile-record-list mt-5">
            @forelse ($payments as $payment)
                <article class="mobile-record-card">
                    <div class="mobile-record-title">{{ $payment->student->user->fullName() }}</div>
                    <div class="mobile-record-subtitle">{{ $payment->reference }}</div>
                    <div class="mobile-record-grid mt-4">
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Provider</div>
                            <div class="mobile-record-value">{{ $payment->provider->label() }}</div>
                        </div>
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Amount</div>
                            <div class="mobile-record-value">NGN {{ number_format((float) $payment->amount, 2) }}</div>
                        </div>
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Status</div>
                            <div class="mobile-record-value">{{ $payment->status->label() }}</div>
                        </div>
                    </div>
                    <div class="mobile-action-row mt-4">
                        <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="theme-button-secondary">View receipt</a>
                    </div>
                </article>
            @empty
                <article class="mobile-record-card">
                    <div class="mobile-record-value text-slate-500">No payments yet.</div>
                </article>
            @endforelse
        </div>
    </section>
    </div>
</x-app-layout>
