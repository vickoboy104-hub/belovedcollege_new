<?php

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\FeeItem;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request, ?string $section = null): View
    {
        $sections = collect([
            'create-fee-item',
            'generate-invoice',
            'record-payment',
            'finance-overview',
            'recent-invoices',
        ]);
        $activeFinanceSection = $sections->contains($section) ? $section : 'create-fee-item';

        return view('admin.finance.index', [
            ...$this->sharedFinanceData(),
            'activeFinancePage' => 'desk',
            'activeFinanceSection' => $activeFinanceSection,
        ]);
    }

    public function records(Request $request, ?string $section = null): View
    {
        $sections = collect([
            'printable-fee-list',
            'created-fee-items',
            'student-balances',
            'class-bills',
            'payment-summary',
            'recent-payments',
            'overpayment-tracker',
            'payment-progression',
        ]);
        $activeFinanceRecordsSection = $sections->contains($section) ? $section : 'printable-fee-list';
        $studentSearch = trim((string) $request->string('student_search'));
        $data = $this->sharedFinanceData();

        return view('admin.finance.records', [
            ...$data,
            'activeFinancePage' => 'records',
            'activeFinanceRecordsSection' => $activeFinanceRecordsSection,
            'studentSearch' => $studentSearch,
            'studentBalanceRows' => $this->buildStudentBalanceRows($data['invoices'], $studentSearch),
        ]);
    }

    public function printableFeeList(Request $request): View
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:school_classes,id'],
            'fee_item_ids' => ['nullable', 'array'],
            'fee_item_ids.*' => ['integer', 'exists:fee_items,id'],
        ]);

        $schoolClass = SchoolClass::findOrFail($validated['class_id']);
        $selectedIds = collect($validated['fee_item_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->values();

        $feeItems = FeeItem::query()
            ->with('academicSession', 'term', 'schoolClass')
            ->where(function ($query) use ($schoolClass): void {
                $query->whereNull('school_class_id')
                    ->orWhere('school_class_id', $schoolClass->id);
            })
            ->when($selectedIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $selectedIds))
            ->get()
            ->sortBy(fn (FeeItem $item) => ($item->term?->name ?? 'ZZZ').' '.$item->name)
            ->values();

        return view('admin.finance.printable-fee-list', [
            'schoolClass' => $schoolClass,
            'feeItems' => $feeItems,
            'selectedIds' => $selectedIds,
            'total' => (float) $feeItems->sum('amount'),
        ]);
    }

    public function storeFeeItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_mandatory' => ['nullable', 'boolean'],
        ]);

        $duplicateQuery = FeeItem::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($validated['name'])]);

        foreach (['academic_session_id', 'term_id', 'school_class_id'] as $column) {
            if (! empty($validated[$column])) {
                $duplicateQuery->where($column, $validated[$column]);
            } else {
                $duplicateQuery->whereNull($column);
            }
        }

        if ($duplicateQuery->exists()) {
            return back()->withErrors([
                'name' => 'A matching fee item already exists for the selected session, term, and class.',
            ])->withInput();
        }

        FeeItem::create([
            ...$validated,
            'is_mandatory' => $request->boolean('is_mandatory', true),
        ]);

        return back()->with('status', 'Fee item created successfully.');
    }

    public function destroyFeeItem(FeeItem $feeItem): RedirectResponse
    {
        $feeItemName = $feeItem->name;

        $feeItem->delete();

        return back()->with('status', $feeItemName.' deleted successfully.');
    }

    public function storeInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fee_item_id' => ['nullable', 'exists:fee_items,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'amount_due' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $feeItem = ! empty($validated['fee_item_id']) ? FeeItem::find($validated['fee_item_id']) : null;
        $amount = (float) ($validated['amount_due'] ?? $feeItem?->amount ?? 0);
        $dueDate = $validated['due_date'] ?? $feeItem?->due_date;
        $students = collect();
        $createdCount = 0;
        $skippedCount = 0;

        if (! empty($validated['student_id'])) {
            $students = Student::with('user')->whereKey($validated['student_id'])->get();
        } elseif (! empty($validated['school_class_id'])) {
            $students = Student::query()->with('user')->where('school_class_id', $validated['school_class_id'])->get();
        }

        abort_if($students->isEmpty(), 422, 'Select a student or a class with students.');

        foreach ($students as $student) {
            if ($feeItem && FeeInvoice::query()->where('student_id', $student->id)->where('fee_item_id', $feeItem->id)->exists()) {
                $skippedCount++;

                continue;
            }

            FeeInvoice::create([
                'invoice_no' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'student_id' => $student->id,
                'fee_item_id' => $feeItem?->id,
                'amount_due' => $amount,
                'amount_paid' => 0,
                'balance' => $amount,
                'due_date' => $dueDate,
                'status' => 'unpaid',
                'issued_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $createdCount++;
        }

        if ($createdCount === 0 && $skippedCount > 0) {
            return back()->with('status', 'No new invoices were created because matching fee invoices already exist for the selected student(s).');
        }

        if ($skippedCount > 0) {
            return back()->with('status', $createdCount.' invoice(s) generated successfully. '.$skippedCount.' duplicate fee invoice(s) skipped.');
        }

        return back()->with('status', 'Invoice(s) generated successfully.');
    }

    public function storeManualPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fee_invoice_id' => ['required', 'exists:fee_invoices,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'provider' => ['required', 'in:manual,paystack,palmpay'],
            'channel' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice = FeeInvoice::with('student')->findOrFail($validated['fee_invoice_id']);

        if ((float) $invoice->balance <= 0) {
            return back()->withErrors([
                'fee_invoice_id' => 'This invoice has already been settled.',
            ])->withInput();
        }

        Payment::create([
            'fee_invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'provider' => match ($validated['provider']) {
                'paystack' => PaymentProvider::Paystack,
                'palmpay' => PaymentProvider::PalmPay,
                default => PaymentProvider::Manual,
            },
            'reference' => 'MAN-'.Str::upper(Str::random(10)),
            'receipt_no' => 'RCP-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'amount' => $validated['amount'],
            'currency' => 'NGN',
            'status' => PaymentStatus::Paid,
            'channel' => $validated['channel'] ?: 'school-office',
            'paid_at' => now(),
            'recorded_by' => $request->user()->id,
            'note' => $validated['note'] ?? 'Recorded manually at the finance desk.',
            'payload' => [
                'source' => 'manual_finance_entry',
                'recorded_amount' => (float) $validated['amount'],
                'invoice_balance_before_payment' => (float) $invoice->balance,
                'overpayment_amount' => max((float) $validated['amount'] - (float) $invoice->balance, 0),
            ],
        ]);

        $invoice->refresh()->syncBalance();

        return back()->with('status', 'Payment recorded successfully.');
    }

    public function receipt(Payment $payment): View
    {
        $payment->load('student.user', 'feeInvoice.feeItem', 'recorder');

        return view('admin.receipt', compact('payment'));
    }

    protected function sharedFinanceData(): array
    {
        $feeItems = FeeItem::with('academicSession', 'term', 'schoolClass')->latest()->get();
        $invoices = FeeInvoice::with('student.user', 'student.schoolClass', 'feeItem', 'payments')->latest('issued_at')->get();
        $allPayments = Payment::with('student.user', 'student.schoolClass', 'feeInvoice')
            ->latest('paid_at')
            ->get()
            ->reject(fn (Payment $payment) => data_get($payment->payload, 'source') === 'bundle_allocation')
            ->values();
        $students = Student::with('user', 'schoolClass')->orderBy('admission_no')->get();
        $classes = SchoolClass::orderBy('name')->get();
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        $terms = Term::orderByDesc('start_date')->get();

        return [
            'feeItems' => $feeItems,
            'invoices' => $invoices,
            'payments' => $allPayments->take(15)->values(),
            'paymentCount' => $allPayments->count(),
            'students' => $students,
            'classes' => $classes,
            'sessions' => $sessions,
            'terms' => $terms,
            'classFeeCatalog' => $this->buildClassFeeCatalog($classes, $feeItems),
            'classBillingRows' => $this->buildClassBillingRows($classes, $students, $invoices),
            'paymentSummary' => $this->buildPaymentSummary($allPayments),
            'topDebtors' => $this->buildStudentBalanceRows($invoices)->take(6)->values(),
            'overpaymentRows' => $this->buildOverpaymentRows($invoices),
            'paymentProgressionRows' => $this->buildPaymentProgressionRows($invoices),
            'financeOverview' => [
                'outstandingInvoiceCount' => $invoices->where('balance', '>', 0)->count(),
                'paymentCount' => $allPayments->count(),
                'outstandingTotal' => (float) $invoices->sum('balance'),
                'feeItemCount' => $feeItems->count(),
                'totalBilled' => (float) $invoices->sum('amount_due'),
                'totalCollected' => (float) $invoices->sum('amount_paid'),
                'overpaymentTotal' => (float) $invoices->sum(fn (FeeInvoice $invoice) => max((float) $invoice->amount_paid - (float) $invoice->amount_due, 0)),
                'overpaidStudentCount' => $invoices
                    ->filter(fn (FeeInvoice $invoice) => (float) $invoice->amount_paid > (float) $invoice->amount_due)
                    ->pluck('student_id')
                    ->unique()
                    ->count(),
                'studentDebtorCount' => $invoices->where('balance', '>', 0)->pluck('student_id')->unique()->count(),
                'collectionRate' => (float) ((float) $invoices->sum('amount_due') > 0
                    ? round(((float) $invoices->sum('amount_paid') / (float) $invoices->sum('amount_due')) * 100, 1)
                    : 0),
            ],
        ];
    }

    protected function buildClassFeeCatalog(Collection $classes, Collection $feeItems): Collection
    {
        return $classes->map(function (SchoolClass $class) use ($feeItems) {
            $items = $feeItems
                ->filter(fn (FeeItem $item) => $item->school_class_id === null || $item->school_class_id === $class->id)
                ->sortBy(fn (FeeItem $item) => ($item->term?->name ?? 'ZZZ').' '.$item->name)
                ->values()
                ->map(fn (FeeItem $item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'amount' => (float) $item->amount,
                    'term' => $item->term?->name,
                    'due_date' => optional($item->due_date)->format('Y-m-d'),
                    'scope' => $item->schoolClass->display_name ?? 'All classes',
                    'mandatory' => $item->is_mandatory,
                ])
                ->values();

            return [
                'id' => $class->id,
                'name' => $class->display_name,
                'items' => $items,
                'total' => round($items->sum('amount'), 2),
            ];
        })->values();
    }

    protected function buildStudentBalanceRows(Collection $invoices, string $studentSearch = ''): Collection
    {
        $rows = $invoices
            ->groupBy('student_id')
            ->map(function ($group) {
                $student = $group->first()->student;
                $unpaid = $group->filter(fn (FeeInvoice $invoice) => (float) $invoice->balance > 0)->values();
                $progress = $group->filter(fn (FeeInvoice $invoice) => (float) $invoice->amount_paid > 0)->values();

                return [
                    'student' => $student,
                    'unpaid_items' => $unpaid->map(fn (FeeInvoice $invoice) => [
                        'label' => $invoice->feeItem->name ?? 'Direct invoice',
                        'invoice_no' => $invoice->invoice_no,
                        'amount_due' => (float) $invoice->amount_due,
                        'balance' => (float) $invoice->balance,
                        'status' => $invoice->status,
                    ])->all(),
                    'progress_items' => $progress->map(fn (FeeInvoice $invoice) => [
                        'label' => $invoice->feeItem->name ?? 'Direct invoice',
                        'invoice_no' => $invoice->invoice_no,
                        'amount_paid' => (float) $invoice->amount_paid,
                        'balance' => (float) $invoice->balance,
                        'status' => $invoice->status,
                    ])->all(),
                    'outstanding_total' => (float) $group->sum('balance'),
                    'paid_total' => (float) $group->sum('amount_paid'),
                ];
            })
            ->filter(fn (array $row) => $row['outstanding_total'] > 0 || count($row['progress_items']) > 0);

        if ($studentSearch !== '') {
            $needle = Str::lower($studentSearch);

            $rows = $rows->filter(function (array $row) use ($needle) {
                $student = $row['student'];
                $haystack = Str::lower(implode(' ', array_filter([
                    $student->user->fullName(),
                    $student->user->email,
                    $student->admission_no,
                    $student->schoolClass->display_name ?? null,
                ])));

                return str_contains($haystack, $needle);
            });
        }

        return $rows->sortByDesc('outstanding_total')->values();
    }

    protected function buildClassBillingRows(Collection $classes, Collection $students, Collection $invoices): Collection
    {
        return $classes->map(function (SchoolClass $class) use ($students, $invoices) {
            $classStudents = $students->where('school_class_id', $class->id);
            $classStudentIds = $classStudents->pluck('id');
            $classInvoices = $invoices->whereIn('student_id', $classStudentIds);
            $expectedTotal = (float) $classInvoices->sum('amount_due');
            $collectedTotal = (float) $classInvoices->sum('amount_paid');
            $outstandingTotal = (float) $classInvoices->sum('balance');

            return [
                'class' => $class,
                'student_count' => $classStudents->count(),
                'invoice_count' => $classInvoices->count(),
                'students_with_debt' => $classInvoices->where('balance', '>', 0)->pluck('student_id')->unique()->count(),
                'expected_total' => $expectedTotal,
                'collected_total' => $collectedTotal,
                'outstanding_total' => $outstandingTotal,
                'collection_rate' => $expectedTotal > 0 ? round(($collectedTotal / $expectedTotal) * 100, 1) : 0,
            ];
        })->sortByDesc('outstanding_total')->values();
    }

    protected function buildPaymentSummary(Collection $payments): array
    {
        $paidPayments = $payments->filter(fn (Payment $payment) => $payment->status === PaymentStatus::Paid)->values();
        $providerLabels = [
            'manual' => 'Manual Office',
            'paystack' => 'Paystack',
            'palmpay' => 'PalmPay',
        ];

        $providerBreakdown = collect($providerLabels)
            ->map(function (string $label, string $provider) use ($paidPayments) {
                $providerPayments = $paidPayments->filter(fn (Payment $payment) => $payment->provider->value === $provider);

                return [
                    'label' => $label,
                    'count' => $providerPayments->count(),
                    'total' => (float) $providerPayments->sum('amount'),
                ];
            })
            ->values();

        $channelBreakdown = $paidPayments
            ->groupBy(fn (Payment $payment) => Str::headline((string) ($payment->channel ?: 'Unspecified')))
            ->map(fn (Collection $group, string $channel) => [
                'channel' => $channel,
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $dailyCollection = $paidPayments
            ->groupBy(fn (Payment $payment) => optional($payment->paid_at)->format('Y-m-d') ?: 'Unknown')
            ->map(fn (Collection $group, string $day) => [
                'day' => $day,
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('day')
            ->take(7)
            ->values();

        return [
            'providerBreakdown' => $providerBreakdown,
            'channelBreakdown' => $channelBreakdown,
            'dailyCollection' => $dailyCollection,
        ];
    }

    protected function buildOverpaymentRows(Collection $invoices): Collection
    {
        return $invoices
            ->filter(fn (FeeInvoice $invoice) => (float) $invoice->amount_paid > (float) $invoice->amount_due)
            ->map(function (FeeInvoice $invoice) {
                $overpayment = max((float) $invoice->amount_paid - (float) $invoice->amount_due, 0);

                return [
                    'invoice' => $invoice,
                    'student' => $invoice->student,
                    'overpayment' => $overpayment,
                    'payment_count' => $invoice->payments->count(),
                    'last_payment_at' => $invoice->payments->sortByDesc('paid_at')->first()?->paid_at,
                ];
            })
            ->sortByDesc('overpayment')
            ->values();
    }

    protected function buildPaymentProgressionRows(Collection $invoices): Collection
    {
        return $invoices
            ->filter(fn (FeeInvoice $invoice) => (float) $invoice->amount_paid > 0 || (float) $invoice->balance > 0)
            ->map(function (FeeInvoice $invoice) {
                $amountDue = (float) $invoice->amount_due;
                $amountPaid = (float) $invoice->amount_paid;
                $overpayment = max($amountPaid - $amountDue, 0);
                $progress = $amountDue > 0 ? min(round(($amountPaid / $amountDue) * 100, 1), 100) : 0;

                return [
                    'invoice' => $invoice,
                    'student' => $invoice->student,
                    'progress' => $progress,
                    'overpayment' => $overpayment,
                    'last_payment_at' => $invoice->payments->sortByDesc('paid_at')->first()?->paid_at,
                    'recent_payments' => $invoice->payments
                        ->sortByDesc('paid_at')
                        ->take(3)
                        ->values(),
                ];
            })
            ->sortByDesc(fn (array $row) => (float) $row['invoice']->balance)
            ->values();
    }
}
