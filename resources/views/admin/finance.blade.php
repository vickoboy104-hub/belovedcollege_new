<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.28em]" style="color: var(--theme-muted, #64748b);">Finance desk</p>
            <h1 class="display-font mt-2 text-3xl font-bold" style="color: var(--theme-text, #0f172a);">Fees, invoices, balances, manual payments, and receipts</h1>
        </div>
    </x-slot>

    <div class="grid gap-8 xl:grid-cols-2">
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold" style="color: var(--theme-text, #0f172a);">Create fee item</h2>
            <p class="mt-2 text-sm" style="color: var(--theme-muted, #64748b);">Use class-linked fee items so every newly registered student can inherit the right term fees.</p>
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
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <input name="due_date" type="date" class="theme-input" />
                <textarea name="description" rows="3" placeholder="Description" class="theme-input md:col-span-2"></textarea>
                <label class="flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm md:col-span-2" style="border-color: var(--theme-border, #cbd5e1); color: var(--theme-text, #0f172a);">
                    <input type="checkbox" name="is_mandatory" value="1" checked class="rounded" style="border-color: var(--theme-border, #cbd5e1);" />
                    Mandatory fee item
                </label>
                <button type="submit" class="theme-button md:col-span-2">Create fee item</button>
            </form>
        </section>

        <section class="section-card">
            <h2 class="display-font text-2xl font-bold" style="color: var(--theme-text, #0f172a);">Generate invoice</h2>
            <p class="mt-2 text-sm" style="color: var(--theme-muted, #64748b);">Apply an invoice to one student or an entire class. Outstanding balances will remain visible until fully paid.</p>
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
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
                <input name="due_date" type="date" class="theme-input md:col-span-2" />
                <textarea name="notes" rows="3" placeholder="Invoice note" class="theme-input md:col-span-2"></textarea>
                <button type="submit" class="theme-button md:col-span-2">Generate invoice</button>
            </form>
        </section>
    </div>

    <div class="mt-8 grid gap-8 xl:grid-cols-[1.1fr,0.9fr]">
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold" style="color: var(--theme-text, #0f172a);">Record direct school payment</h2>
            <p class="mt-2 text-sm" style="color: var(--theme-muted, #64748b);">Use this when payment happens physically at school. The student balance updates immediately and a receipt becomes printable.</p>
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
        </section>

        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Quick finance view</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-sm uppercase tracking-[0.22em] text-slate-500">Outstanding invoices</div>
                    <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $invoices->where('balance', '>', 0)->count() }}</div>
                </div>
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-sm uppercase tracking-[0.22em] text-slate-500">Payments recorded</div>
                    <div class="display-font mt-3 text-4xl font-bold text-slate-950">{{ $paymentCount }}</div>
                </div>
            </div>
            <p class="mt-5 text-sm leading-7 text-slate-600">Student portal balances are driven from invoices and payment records. When a payment is posted here or through the online gateway, the remaining balance reflects immediately.</p>
        </section>
    </div>

    <section
        class="section-card mt-8"
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
                <p class="mt-2 text-sm text-slate-500">Pick a class, tick the fee items you want included, and the total updates automatically for printing or saving as PDF.</p>
            </div>
                <div class="flex flex-wrap gap-3 print:hidden">
                <button type="button" @click="toggleAll()" class="theme-button-secondary">Select or clear all</button>
                <button type="button" onclick="openPrintSettings('.print-card, .receipt-card', { itemsPerPage: 4 })" class="theme-button">Print / Save as PDF</button>
            </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4 print:hidden">
            @foreach ($classes as $class)
                <button
                    type="button"
                    @click="selectedClassId = {{ $class->id }}; syncDefaults()"
                    class="rounded-2xl border px-4 py-4 text-left transition"
                    :class="selectedClassId === {{ $class->id }} ? 'border-transparent text-white shadow-lg' : 'border-slate-200 bg-slate-50 text-slate-900'"
                    :style="selectedClassId === {{ $class->id }} ? 'background-color: var(--theme-primary);' : 'background-color: #f8fafc;'"
                >
                    <div class="font-semibold">{{ $class->name }}</div>
                    <div class="mt-1 text-xs opacity-80">Open fee list</div>
                </button>
            @endforeach
        </div>

        <div class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-6 print:border-0 print:shadow-none">
            <div class="flex flex-col gap-2 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Fee schedule</div>
                    <h3 class="display-font mt-2 text-2xl font-bold text-slate-950" x-text="selectedClass.name || 'Select a class'"></h3>
                </div>
                <div class="text-sm text-slate-500">Checked items are included in the total below.</div>
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

    <div class="mt-8 grid gap-8 xl:grid-cols-3">
        <section class="section-card">
            <h2 class="display-font text-2xl font-bold text-slate-950">Created fee items</h2>
            <div class="mt-5 space-y-3">
                @forelse ($feeItems as $item)
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                        <div class="mt-1 text-sm text-slate-500">{{ $item->schoolClass->name ?? 'All classes' }} @if($item->term) | {{ $item->term->name }} @endif</div>
                        <div class="mt-2 text-sm font-bold text-slate-900">NGN {{ number_format((float) $item->amount, 2) }}</div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-6 text-sm text-slate-500">No fee items yet.</div>
                @endforelse
            </div>
            @if ($feeItems->isNotEmpty())
                <div class="mt-5 rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-5">
                    <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Total of all created fee items</div>
                    <div class="display-font mt-3 text-3xl font-bold text-slate-950">NGN {{ number_format((float) $feeItems->sum('amount'), 2) }}</div>
                </div>
            @endif
        </section>

        <section class="section-card xl:col-span-2">
            <h2 class="display-font text-2xl font-bold text-slate-950">Recent invoices</h2>
            <div class="mt-5 overflow-x-auto">
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
        </section>
    </div>

    <section class="section-card mt-8">
        <h2 class="display-font text-2xl font-bold text-slate-950">Students with unpaid or part-paid fees</h2>
        <p class="mt-2 text-sm text-slate-500">This shows what each student has not paid, what has been paid already, and the remaining balance when a fee is only partly settled.</p>
        <div class="mt-5 overflow-x-auto">
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
                                <div class="text-xs text-slate-500">{{ $row['student']->admission_no }} | {{ $row['student']->schoolClass->name ?? 'No class' }}</div>
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
    </section>

    <section class="section-card mt-8">
        <h2 class="display-font text-2xl font-bold text-slate-950">Recent payments and receipts</h2>
        <div class="mt-5 overflow-x-auto">
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
    </section>
</x-app-layout>
