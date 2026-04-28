<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Finance desk</p>
                <h1 class="display-font mt-2 text-3xl font-bold text-slate-950">Create fee items, issue invoices, and record payments</h1>
            </div>
            @include('admin.finance._switcher')
        </div>
    </x-slot>

    @php
        $financeDeskNavItems = [
            ['key' => 'create-fee-item', 'label' => 'Fee Items', 'href' => route('admin.finance', ['section' => 'create-fee-item'])],
            ['key' => 'generate-invoice', 'label' => 'Invoices', 'href' => route('admin.finance', ['section' => 'generate-invoice'])],
            ['key' => 'record-payment', 'label' => 'Record Payment', 'href' => route('admin.finance', ['section' => 'record-payment'])],
            ['key' => 'finance-overview', 'label' => 'Overview', 'href' => route('admin.finance', ['section' => 'finance-overview'])],
            ['key' => 'recent-invoices', 'label' => 'Recent Invoices', 'href' => route('admin.finance', ['section' => 'recent-invoices'])],
        ];
        $bankAccounts = collect(range(1, 3))
            ->map(fn (int $index) => [
                'bank' => $schoolSettings["bank_name_{$index}"] ?? null,
                'account_name' => $schoolSettings["account_name_{$index}"] ?? null,
                'account_number' => $schoolSettings["account_number_{$index}"] ?? null,
            ])
            ->filter(fn (array $account) => filled($account['bank']) || filled($account['account_name']) || filled($account['account_number']))
            ->values();
    @endphp

    <div x-data="{ activeSection: @js($activeFinanceSection) }" class="grid gap-8">
        <x-section-nav :items="$financeDeskNavItems" :active="$activeFinanceSection" />

        <div class="grid gap-8 xl:grid-cols-2" x-show="['create-fee-item', 'generate-invoice'].includes(activeSection)">
        <section class="section-card" x-show="activeSection === 'create-fee-item'">
            <h2 class="display-font text-2xl font-bold text-slate-950">Create fee item</h2>
            <p class="mt-2 text-sm text-slate-500">Use class-linked fee items so every newly registered student can inherit the right term fees.</p>
            <form method="POST" action="{{ route('admin.fee-items.store') }}" class="mt-6 grid gap-4 md:grid-cols-2">
                @csrf
                <input name="name" placeholder="Tuition Fee" class="theme-input" required />
                <input name="amount" type="number" step="0.01" placeholder="Amount" class="theme-input" required />
                <select name="academic_session_id" class="theme-input">
                    <option value="">Session</option>
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
                <select name="term_id" class="theme-input">
                    <option value="">Term</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                    @endforeach
                </select>
                <select name="school_class_id" class="theme-input">
                    <option value="">Class scope</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
                <input name="due_date" type="date" class="theme-input" />
                <textarea name="description" rows="3" placeholder="Description" class="theme-input md:col-span-2"></textarea>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-600 md:col-span-2">
                    <input type="checkbox" name="is_mandatory" value="1" checked class="rounded border-slate-300" />
                    Mandatory fee item
                </label>
                <button type="submit" class="theme-button md:col-span-2">Create fee item</button>
            </form>
        </section>

        <section class="section-card" x-show="activeSection === 'generate-invoice'">
            <h2 class="display-font text-2xl font-bold text-slate-950">Generate invoice</h2>
            <p class="mt-2 text-sm text-slate-500">Apply an invoice to one student or an entire class. Existing linked fee invoices are skipped automatically so duplicates do not stack.</p>
            <form method="POST" action="{{ route('admin.invoices.store') }}" class="mt-6 grid gap-4 md:grid-cols-2">
                @csrf
                <select name="fee_item_id" class="theme-input">
                    <option value="">Link fee item</option>
                    @foreach ($feeItems as $feeItem)
                        <option value="{{ $feeItem->id }}">{{ $feeItem->name }} - NGN {{ number_format((float) $feeItem->amount, 2) }}</option>
                    @endforeach
                </select>
                <input name="amount_due" type="number" step="0.01" placeholder="Override amount" class="theme-input" />
                <select name="student_id" class="theme-input">
                    <option value="">Single student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->user->fullName() }} - {{ $student->admission_no }}</option>
                    @endforeach
                </select>
                <select name="school_class_id" class="theme-input">
                    <option value="">Or apply to whole class</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
                <input name="due_date" type="date" class="theme-input md:col-span-2" />
                <textarea name="notes" rows="3" placeholder="Invoice note" class="theme-input md:col-span-2"></textarea>
                <button type="submit" class="theme-button md:col-span-2">Generate invoice</button>
            </form>
        </section>
        </div>

    <div class="grid gap-8 xl:grid-cols-[1.1fr,0.9fr]" x-show="['record-payment', 'finance-overview'].includes(activeSection)">
        <section class="section-card" x-show="activeSection === 'record-payment'">
            <h2 class="display-font text-2xl font-bold text-slate-950">Record direct school payment</h2>
            <p class="mt-2 text-sm text-slate-500">Use this when payment happens physically at school. The student balance updates immediately, receipts stay printable, and any excess above the live invoice balance is now tracked as an overpayment.</p>
            <form method="POST" action="{{ route('admin.manual-payments.store') }}" class="mt-6 grid gap-4 md:grid-cols-2">
                @csrf
                <select name="fee_invoice_id" class="theme-input md:col-span-2" required>
                    <option value="">Select invoice</option>
                    @foreach ($invoices as $invoice)
                        <option value="{{ $invoice->id }}">{{ $invoice->student->user->fullName() }} | {{ $invoice->invoice_no }} | Balance: NGN {{ number_format((float) $invoice->balance, 2) }}</option>
                    @endforeach
                </select>
                <input name="amount" type="number" step="0.01" placeholder="Amount paid" class="theme-input" required />
                <select name="provider" class="theme-input" required>
                    <option value="manual">Manual office payment</option>
                    <option value="paystack">Paystack-assisted</option>
                    <option value="palmpay">PalmPay-assisted</option>
                </select>
                <input name="channel" placeholder="Cash, transfer, POS, teller, or branch note" class="theme-input md:col-span-2" />
                <textarea name="note" rows="3" placeholder="Payment note" class="theme-input md:col-span-2"></textarea>
                <button type="submit" class="theme-button md:col-span-2">Record payment</button>
            </form>

            @if ($bankAccounts->isNotEmpty() || filled($schoolSettings['payment_instruction'] ?? null))
                <div class="mt-6 rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">School payment details</div>
                    <div class="mt-4 space-y-3">
                        @foreach ($bankAccounts as $account)
                            <div class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-4">
                                <div class="font-semibold text-slate-900">{{ $account['bank'] ?: 'School bank account' }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $account['account_name'] ?: 'Account name pending' }}</div>
                                <div class="mt-1 text-sm font-bold text-slate-900">{{ $account['account_number'] ?: 'Account number pending' }}</div>
                            </div>
                        @endforeach
                    </div>
                    @if (filled($schoolSettings['payment_instruction'] ?? null))
                        <p class="mt-4 text-sm leading-7 text-slate-600">{{ $schoolSettings['payment_instruction'] }}</p>
                    @endif
                </div>
            @endif
        </section>

        <section class="section-card" x-show="activeSection === 'finance-overview'">
            <h2 class="display-font text-2xl font-bold text-slate-950">Overview</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-sm uppercase tracking-[0.22em] text-slate-500">Outstanding invoices</div>
                    <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $financeOverview['outstandingInvoiceCount'] }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-sm uppercase tracking-[0.22em] text-slate-500">Payments recorded</div>
                    <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $financeOverview['paymentCount'] }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-sm uppercase tracking-[0.22em] text-slate-500">Outstanding total</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">NGN {{ number_format((float) $financeOverview['outstandingTotal'], 2) }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-sm uppercase tracking-[0.22em] text-slate-500">Created fee items</div>
                    <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $financeOverview['feeItemCount'] }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-sm uppercase tracking-[0.22em] text-slate-500">Total billed</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">NGN {{ number_format((float) $financeOverview['totalBilled'], 2) }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-sm uppercase tracking-[0.22em] text-slate-500">Total collected</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">NGN {{ number_format((float) $financeOverview['totalCollected'], 2) }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-sm uppercase tracking-[0.22em] text-slate-500">Tracked overpayments</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">NGN {{ number_format((float) $financeOverview['overpaymentTotal'], 2) }}</div>
                    <div class="mt-2 text-sm text-slate-500">{{ $financeOverview['overpaidStudentCount'] }} student{{ $financeOverview['overpaidStudentCount'] === 1 ? '' : 's' }} with excess payment</div>
                </div>
            </div>
            <div class="mt-6 grid gap-5 xl:grid-cols-[1.05fr,0.95fr]">
                <div class="rounded-[1.75rem] border border-slate-200 bg-white/80 px-5 py-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Collection health</div>
                            <div class="display-font mt-2 text-3xl font-bold text-slate-950">{{ number_format((float) $financeOverview['collectionRate'], 1) }}%</div>
                        </div>
                        <div class="text-sm text-slate-600">{{ $financeOverview['studentDebtorCount'] }} student{{ $financeOverview['studentDebtorCount'] === 1 ? '' : 's' }} still owe fees.</div>
                    </div>
                    <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full" style="width: {{ min(100, max(0, (float) $financeOverview['collectionRate'])) }}%; background: linear-gradient(135deg, var(--theme-primary), var(--theme-accent));"></div>
                    </div>
                    <p class="mt-4 text-sm leading-7 text-slate-600">Student portal balances are driven from invoices and payment records, so finance figures stay aligned whenever online or office payments are posted.</p>
                    <div class="mt-4 text-sm text-slate-600">
                        <a href="{{ route('admin.finance.records', ['section' => 'payment-progression']) }}" class="font-semibold text-[color:var(--theme-primary)]">Open payment progression</a>
                        <span class="mx-2 text-slate-300">|</span>
                        <a href="{{ route('admin.finance.records', ['section' => 'overpayment-tracker']) }}" class="font-semibold text-[color:var(--theme-primary)]">Open overpayment tracker</a>
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-white/80 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Payment channels</div>
                    <div class="mt-4 space-y-3">
                        @foreach ($paymentSummary['providerBreakdown'] as $provider)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <div class="font-semibold text-slate-900">{{ $provider['label'] }}</div>
                                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500">{{ $provider['count'] }} payment{{ $provider['count'] === 1 ? '' : 's' }}</div>
                                </div>
                                <div class="text-sm font-bold text-slate-900">NGN {{ number_format((float) $provider['total'], 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-5 xl:grid-cols-[1.05fr,0.95fr]">
                <div class="rounded-[1.75rem] border border-slate-200 bg-white/80 px-5 py-5">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Top debtors</div>
                            <h3 class="display-font mt-2 text-xl font-bold text-slate-950">Students needing follow-up</h3>
                        </div>
                        <a href="{{ route('admin.finance.records', ['section' => 'student-balances']) }}" class="text-sm font-semibold text-[color:var(--theme-primary)]">Open full debtor list</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($topDebtors as $row)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $row['student']->user->fullName() }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $row['student']->admission_no }} | {{ $row['student']->schoolClass->display_name ?? 'No class' }}</div>
                                    </div>
                                    <div class="text-sm font-bold text-slate-900">NGN {{ number_format((float) $row['outstanding_total'], 2) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No debtors to follow up right now.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-white/80 px-5 py-5">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Class billing</div>
                            <h3 class="display-font mt-2 text-xl font-bold text-slate-950">Collection by class</h3>
                        </div>
                        <a href="{{ route('admin.finance.records', ['section' => 'class-bills']) }}" class="text-sm font-semibold text-[color:var(--theme-primary)]">Open class summary</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($classBillingRows->take(4) as $row)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-semibold text-slate-900">{{ $row['class']->display_name }}</div>
                                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500">{{ number_format((float) $row['collection_rate'], 1) }}%</div>
                                </div>
                                <div class="mt-2 text-sm text-slate-600">{{ $row['students_with_debt'] }} debtor{{ $row['students_with_debt'] === 1 ? '' : 's' }} | {{ $row['student_count'] }} student{{ $row['student_count'] === 1 ? '' : 's' }}</div>
                                <div class="mt-2 text-sm font-bold text-slate-900">Outstanding: NGN {{ number_format((float) $row['outstanding_total'], 2) }}</div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No class billing data yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="section-card" x-show="activeSection === 'recent-invoices'">
        <h2 class="display-font text-2xl font-bold text-slate-950">Recent invoices</h2>
        <div class="desktop-table table-wrap mt-5">
            <table class="min-w-full text-left text-sm">
                <thead class="text-slate-500">
                    <tr>
                        <th class="pb-3">Student</th>
                        <th class="pb-3">Invoice</th>
                        <th class="pb-3">Amount due</th>
                        <th class="pb-3">Paid</th>
                        <th class="pb-3">Balance</th>
                        <th class="pb-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($invoices->take(25) as $invoice)
                        <tr>
                            <td class="py-4">
                                <div class="font-semibold text-slate-900">{{ $invoice->student->user->fullName() }}</div>
                                <div class="text-xs text-slate-500">{{ $invoice->student->admission_no }}</div>
                            </td>
                            <td class="py-4 text-slate-600">
                                <div>{{ $invoice->invoice_no }}</div>
                                <div class="text-xs text-slate-500">{{ $invoice->feeItem->name ?? 'Direct invoice' }}</div>
                            </td>
                            <td class="py-4 text-slate-600">NGN {{ number_format((float) $invoice->amount_due, 2) }}</td>
                            <td class="py-4 text-slate-600">NGN {{ number_format((float) $invoice->amount_paid, 2) }}</td>
                            <td class="py-4 font-semibold text-slate-900">NGN {{ number_format((float) $invoice->balance, 2) }}</td>
                            <td class="py-4 text-slate-600">{{ ucfirst($invoice->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-slate-500">No invoices yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mobile-record-list mt-5">
            @forelse ($invoices->take(25) as $invoice)
                <article class="mobile-record-card">
                    <div class="mobile-record-title">{{ $invoice->student->user->fullName() }}</div>
                    <div class="mobile-record-subtitle">{{ $invoice->invoice_no }} | {{ $invoice->student->admission_no }}</div>
                    <div class="mobile-record-grid mt-4">
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Fee item</div>
                            <div class="mobile-record-value">{{ $invoice->feeItem->name ?? 'Direct invoice' }}</div>
                        </div>
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Amount due</div>
                            <div class="mobile-record-value">NGN {{ number_format((float) $invoice->amount_due, 2) }}</div>
                        </div>
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Paid</div>
                            <div class="mobile-record-value">NGN {{ number_format((float) $invoice->amount_paid, 2) }}</div>
                        </div>
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Balance</div>
                            <div class="mobile-record-value">NGN {{ number_format((float) $invoice->balance, 2) }}</div>
                        </div>
                        <div class="mobile-record-item">
                            <div class="mobile-record-label">Status</div>
                            <div class="mobile-record-value">{{ ucfirst($invoice->status) }}</div>
                        </div>
                    </div>
                </article>
            @empty
                <article class="mobile-record-card">
                    <div class="mobile-record-value text-slate-500">No invoices yet.</div>
                </article>
            @endforelse
        </div>
    </section>
    </div>
</x-app-layout>
