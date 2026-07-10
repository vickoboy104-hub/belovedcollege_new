<x-portal-layout>
    <x-slot name="header">
        <x-page-header 
            title="Create fee items, issue invoices, and record payments" 
            description="Manage and track all student billings, fee catalog, payments and collections."
            eyebrow="Finance desk"
        />
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
        $compactMoney = function (float $amount): string {
            $sign = $amount < 0 ? '-' : '';
            $absolute = abs($amount);

            return match (true) {
                $absolute >= 1000000000 => $sign.'₦'.number_format($absolute / 1000000000, 2).'B',
                $absolute >= 1000000 => $sign.'₦'.number_format($absolute / 1000000, 2).'M',
                $absolute >= 1000 => $sign.'₦'.number_format($absolute / 1000, 1).'K',
                default => $sign.'₦'.number_format($absolute, 0),
            };
        };
    @endphp

    <div x-data="{ activeSection: @js($activeFinanceSection) }" class="grid gap-8">
        <div class="grid gap-8 xl:grid-cols-2" x-show="['create-fee-item', 'generate-invoice'].includes(activeSection)">
            <!-- Create Fee Item Form Card -->
            <div x-show="activeSection === 'create-fee-item'">
                <x-form-card 
                    title="Create fee item" 
                    description="Use class-linked fee items so every newly registered student can inherit the right term fees."
                    action="{{ route('admin.fee-items.store') }}" 
                    method="POST"
                >
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Fee Item Name</label>
                            <input name="name" placeholder="e.g. Tuition Fee" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Amount (NGN)</label>
                            <input name="amount" type="number" step="0.01" placeholder="0.00" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Due Date</label>
                            <input name="due_date" type="date" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Academic Session</label>
                            <select name="academic_session_id" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="">Select Session</option>
                                @foreach ($sessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Term</label>
                            <select name="term_id" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="">Select Term</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Class Scope</label>
                            <select name="school_class_id" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="">Select Class scope</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Description</label>
                            <textarea name="description" rows="3" placeholder="Additional details regarding this fee..." class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500 md:col-span-2"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-semibold text-slate-700 cursor-pointer hover:bg-slate-50 transition">
                                <input type="checkbox" name="is_mandatory" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                Mandatory fee item (applies automatically)
                            </label>
                        </div>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="primary" class="w-full md:w-auto">
                            Create Fee Item
                        </x-action-button>
                    </x-slot>
                </x-form-card>
            </div>

            <!-- Generate Invoice Form Card -->
            <div x-show="activeSection === 'generate-invoice'">
                <x-form-card 
                    title="Generate invoice" 
                    description="Apply an invoice to one student or an entire class. Existing linked fee invoices are skipped automatically so duplicates do not stack."
                    action="{{ route('admin.invoices.store') }}" 
                    method="POST"
                >
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Link Fee Item</label>
                            <select name="fee_item_id" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="">Select Fee Item</option>
                                @foreach ($feeItems as $feeItem)
                                    <option value="{{ $feeItem->id }}">{{ $feeItem->name }} - NGN {{ number_format((float) $feeItem->amount, 2) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Override Amount (Optional)</label>
                            <input name="amount_due" type="number" step="0.01" placeholder="Enter override amount" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Due Date</label>
                            <input name="due_date" type="date" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Single Student Scope</label>
                            <select name="student_id" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="">Select Student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->user->fullName() }} - {{ $student->admission_no }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Or Apply to Whole Class</label>
                            <select name="school_class_id" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="">Select Class scope</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Notes</label>
                            <textarea name="notes" rows="3" placeholder="Notes printed directly on the invoice..." class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500 md:col-span-2"></textarea>
                        </div>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="primary" class="w-full md:w-auto">
                            Generate Invoice
                        </x-action-button>
                    </x-slot>
                </x-form-card>
            </div>
        </div>

        <div class="grid gap-8 xl:grid-cols-[1.1fr,0.9fr]" x-show="['record-payment', 'finance-overview'].includes(activeSection)">
            <!-- Record Payment Section -->
            <div x-show="activeSection === 'record-payment'" class="space-y-8">
                <x-form-card 
                    title="Record direct school payment" 
                    description="Use this when payment happens physically at school. The student balance updates immediately, receipts stay printable, and any excess above the live invoice balance is now tracked as an overpayment."
                    action="{{ route('admin.manual-payments.store') }}" 
                    method="POST"
                >
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Student Invoice</label>
                            <select name="fee_invoice_id" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500 md:col-span-2" required>
                                <option value="">Select invoice</option>
                                @foreach ($invoices as $invoice)
                                    <option value="{{ $invoice->id }}">{{ $invoice->student->user->fullName() }} | {{ $invoice->invoice_no }} | Balance: NGN {{ number_format((float) $invoice->balance, 2) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Amount Paid (NGN)</label>
                            <input name="amount" type="number" step="0.01" placeholder="0.00" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Payment Method</label>
                            <select name="provider" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                                <option value="manual">Manual office payment</option>
                                <option value="paystack">Paystack-assisted</option>
                                <option value="palmpay">PalmPay-assisted</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Payment Reference / Channel</label>
                            <input name="channel" placeholder="e.g. Cash, bank transfer, POS, teller receipt, or branch note" class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500 md:col-span-2" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Payment Notes</label>
                            <textarea name="note" rows="3" placeholder="Internal memo regarding this transaction..." class="theme-input border-[#c8d6ea] rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500 md:col-span-2"></textarea>
                        </div>
                    </div>
                    <x-slot name="actions">
                        <x-action-button type="submit" variant="primary" class="w-full md:w-auto">
                            Record Payment
                        </x-action-button>
                    </x-slot>
                </x-form-card>

                @if ($bankAccounts->isNotEmpty() || filled($schoolSettings['payment_instruction'] ?? null))
                    <x-dashboard-card title="School payment instructions" icon="bank" accent="blue">
                        <div class="space-y-4">
                            @foreach ($bankAccounts as $account)
                                <div class="rounded-xl border border-[#c8d6ea] bg-slate-50/50 px-4 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $account['bank'] ?: 'School Bank Account' }}</div>
                                        <div class="text-xs font-semibold text-slate-500 mt-0.5">{{ $account['account_name'] ?: 'Account name pending' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-extrabold text-blue-700 font-mono tracking-tight">{{ $account['account_number'] ?: 'Account number pending' }}</div>
                                    </div>
                                </div>
                            @endforeach

                            @if (filled($schoolSettings['payment_instruction'] ?? null))
                                <div class="mt-4 text-sm font-medium leading-relaxed text-slate-600 border-t border-slate-100 pt-4">
                                    {{ $schoolSettings['payment_instruction'] }}
                                </div>
                            @endif
                        </div>
                    </x-dashboard-card>
                @endif
            </div>

            <!-- Overview Section -->
            <div x-show="activeSection === 'finance-overview'" class="space-y-8 col-span-2">
                <div class="metrics-grid grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <x-stat-card 
                        label="Outstanding invoices" 
                        value="{{ $financeOverview['outstandingInvoiceCount'] }}" 
                        accent="rose" 
                        icon="alert-circle" 
                    />
                    <x-stat-card 
                        label="Payments recorded" 
                        value="{{ $financeOverview['paymentCount'] }}" 
                        accent="green" 
                        icon="cash" 
                    />
                    <x-stat-card 
                        label="Outstanding total" 
                        value="{{ $compactMoney((float) $financeOverview['outstandingTotal']) }}" 
                        accent="rose" 
                        icon="wallet" 
                    />
                    <x-stat-card 
                        label="Total collected" 
                        value="{{ $compactMoney((float) $financeOverview['totalCollected']) }}" 
                        accent="green" 
                        icon="currency-dollar" 
                    />
                </div>

                <div class="metrics-grid grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <x-stat-card 
                        label="Created fee items" 
                        value="{{ $financeOverview['feeItemCount'] }}" 
                        accent="blue" 
                        icon="layers" 
                    />
                    <x-stat-card 
                        label="Total billed" 
                        value="{{ $compactMoney((float) $financeOverview['totalBilled']) }}" 
                        accent="blue" 
                        icon="calculator" 
                    />
                    <x-stat-card 
                        label="Tracked overpayments" 
                        value="{{ $compactMoney((float) $financeOverview['overpaymentTotal']) }}" 
                        accent="emerald" 
                        icon="shield-check"
                    >
                        {{ $financeOverview['overpaidStudentCount'] }} student{{ $financeOverview['overpaidStudentCount'] === 1 ? '' : 's' }} with excess credit
                    </x-stat-card>
                </div>

                <!-- Graphs & Summaries Grid -->
                <div class="grid gap-8 xl:grid-cols-[1.1fr,0.9fr]">
                    <!-- Collection Health Card -->
                    <x-dashboard-card title="Collection health" subtitle="Total expected fee collections compared with settled billings." icon="activity" accent="blue">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <div class="text-xs uppercase tracking-wider font-bold text-slate-400">Overall Rate</div>
                                <div class="display-font mt-1 text-3xl font-extrabold text-slate-900">{{ number_format((float) $financeOverview['collectionRate'], 1) }}%</div>
                            </div>
                            <div class="text-sm font-semibold text-slate-500 mt-2 sm:mt-0">
                                {{ $financeOverview['studentDebtorCount'] }} student{{ $financeOverview['studentDebtorCount'] === 1 ? '' : 's' }} with unpaid balances.
                            </div>
                        </div>

                        <div class="mt-5">
                            <x-progress-bar :value="min(100, max(0, (float) $financeOverview['collectionRate']))" />
                        </div>

                        <p class="mt-4 text-xs font-semibold leading-relaxed text-slate-400">
                            Outstanding accounts automatically feed into student profiles and parent portals, keeping records aligned transparently whenever invoice adjustments are applied.
                        </p>
                        
                        <div class="mt-6 flex flex-wrap gap-4 text-xs font-bold border-t border-slate-100 pt-4">
                            <a href="{{ route('admin.finance.records', ['section' => 'payment-progression']) }}" class="text-blue-600 hover:underline flex items-center gap-1">
                                Open payment progression &rarr;
                            </a>
                            <span class="text-slate-300">|</span>
                            <a href="{{ route('admin.finance.records', ['section' => 'overpayment-tracker']) }}" class="text-blue-600 hover:underline flex items-center gap-1">
                                Open overpayment tracker &rarr;
                            </a>
                        </div>
                    </x-dashboard-card>

                    <!-- Payment Channels Card -->
                    <x-dashboard-card title="Payment channels" subtitle="Summary of receipts tracked across different gateways." icon="cash" accent="emerald">
                        <div class="space-y-3">
                            @foreach ($paymentSummary['providerBreakdown'] as $provider)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3 transition hover:bg-slate-50">
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $provider['label'] }}</div>
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">{{ $provider['count'] }} payment{{ $provider['count'] === 1 ? '' : 's' }}</div>
                                    </div>
                                    <div class="text-sm font-extrabold text-slate-900">
                                        NGN {{ number_format((float) $provider['total'], 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-dashboard-card>
                </div>

                <div class="grid gap-8 xl:grid-cols-[1.1fr,0.9fr]">
                    <!-- Top Debtors -->
                    <x-dashboard-card title="Top debtors" subtitle="Students needing billing follow-up." icon="alert-circle" accent="rose">
                        <x-slot name="actions">
                            <a href="{{ route('admin.finance.records', ['section' => 'student-balances']) }}" class="text-xs font-bold text-blue-600 hover:underline">
                                Full list &rarr;
                            </a>
                        </x-slot>

                        <div class="space-y-3">
                            @forelse ($topDebtors as $row)
                                <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-950 text-sm">{{ $row['student']->user->fullName() }}</div>
                                        <div class="mt-1 text-xs font-semibold text-slate-500">
                                            {{ $row['student']->admission_no }} | {{ $row['student']->schoolClass->display_name ?? 'No class' }}
                                        </div>
                                    </div>
                                    <div class="text-sm font-extrabold text-red-600">
                                        NGN {{ number_format((float) $row['outstanding_total'], 2) }}
                                    </div>
                                </div>
                            @empty
                                <x-empty-state 
                                    title="No outstanding debtors" 
                                    description="All billing records for this term have been cleared."
                                />
                            @endforelse
                        </div>
                    </x-dashboard-card>

                    <!-- Class Billing Summary -->
                    <x-dashboard-card title="Class billing" subtitle="Financial health summary broken down by class." icon="layers" accent="blue">
                        <x-slot name="actions">
                            <a href="{{ route('admin.finance.records', ['section' => 'class-bills']) }}" class="text-xs font-bold text-blue-600 hover:underline">
                                Full catalog &rarr;
                            </a>
                        </x-slot>

                        <div class="space-y-3">
                            @forelse ($classBillingRows->take(4) as $row)
                                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="font-bold text-slate-900 text-sm">{{ $row['class']->display_name }}</div>
                                        <x-status-badge 
                                            status="{{ $row['collection_rate'] >= 80 ? 'paid' : ($row['collection_rate'] >= 40 ? 'part-paid' : 'unpaid') }}"
                                            label="{{ number_format((float) $row['collection_rate'], 1) }}% Rate"
                                        />
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-xs font-semibold text-slate-500">
                                        <span>{{ $row['students_with_debt'] }} debtor{{ $row['students_with_debt'] === 1 ? '' : 's' }} / {{ $row['student_count'] }} student{{ $row['student_count'] === 1 ? '' : 's' }}</span>
                                        <span class="text-slate-900 font-extrabold">Owes: NGN {{ number_format((float) $row['outstanding_total'], 2) }}</span>
                                    </div>
                                </div>
                            @empty
                                <x-empty-state 
                                    title="No class billing data" 
                                    description="No fee items have been linked to class groups yet."
                                />
                            @endforelse
                        </div>
                    </x-dashboard-card>
                </div>
            </div>
        </div>

        <!-- Recent Invoices Table Card -->
        <section class="space-y-6" x-show="activeSection === 'recent-invoices'">
            <x-data-table :headers="['Student', 'Invoice No', 'Amount Due', 'Amount Paid', 'Balance Due', 'Status', 'Actions']">
                @forelse ($invoices->take(25) as $invoice)
                    @php
                        $invoicePreview = [
                            'type' => 'bill',
                            'title' => $invoice->student->user->fullName(),
                            'subtitle' => 'Invoice '.$invoice->invoice_no.' - '.($invoice->feeItem->name ?? 'Direct invoice'),
                            'avatar' => substr($invoice->student->user->first_name, 0, 1).substr($invoice->student->user->last_name, 0, 1),
                            'profileUrl' => route('admin.students.show', $invoice->student),
                            'ctaLabel' => 'View Full Details',
                            'fields' => [
                                ['label' => 'Invoice No', 'value' => $invoice->invoice_no],
                                ['label' => 'Fee Item', 'value' => $invoice->feeItem->name ?? 'Direct invoice'],
                                ['label' => 'Admission No', 'value' => $invoice->student->admission_no ?: 'Pending'],
                                ['label' => 'Class', 'value' => $invoice->student->schoolClass->display_name ?? 'Unassigned'],
                                ['label' => 'Amount Due', 'value' => 'NGN '.number_format((float) $invoice->amount_due, 2)],
                                ['label' => 'Amount Paid', 'value' => 'NGN '.number_format((float) $invoice->amount_paid, 2)],
                                ['label' => 'Balance Due', 'value' => 'NGN '.number_format((float) $invoice->balance, 2)],
                                ['label' => 'Status', 'value' => ucfirst((string) $invoice->status)],
                            ],
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition duration-150">
                        <td>
                            <div class="table-person">
                                <div class="table-avatar">{{ $invoicePreview['avatar'] }}</div>
                                <div class="table-person-text">
                                    <strong>{{ $invoice->student->user->fullName() }}</strong>
                                    <span>{{ $invoice->student->admission_no ?: 'Pending' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">{{ $invoice->invoice_no }}</div>
                            <div class="text-xs font-semibold text-slate-400 mt-0.5">{{ $invoice->feeItem->name ?? 'Direct invoice' }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-semibold">NGN {{ number_format((float) $invoice->amount_due, 2) }}</td>
                        <td class="px-6 py-4 text-slate-600">NGN {{ number_format((float) $invoice->amount_paid, 2) }}</td>
                        <td class="px-6 py-4 font-extrabold text-slate-950">NGN {{ number_format((float) $invoice->balance, 2) }}</td>
                        <td class="px-6 py-4">
                            <x-status-badge :status="$invoice->status" />
                        </td>
                        <td>
                            <button type="button" class="table-view-btn" data-preview='@json($invoicePreview, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>View</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center">
                            <x-empty-state 
                                title="No invoices found" 
                                description="No billing actions have been recorded yet."
                            />
                        </td>
                    </tr>
                @endforelse
            </x-data-table>
        </section>

        <x-entity-preview-modal />
    </div>
</x-portal-layout>
