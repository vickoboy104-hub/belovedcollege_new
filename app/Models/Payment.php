<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_invoice_id',
        'student_id',
        'provider',
        'reference',
        'receipt_no',
        'gateway_reference',
        'amount',
        'currency',
        'status',
        'channel',
        'paid_at',
        'recorded_by',
        'note',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function feeInvoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function allocateBundleInvoices(): void
    {
        DB::transaction(function (): void {
            /** @var self|null $lockedPayment */
            $lockedPayment = static::query()->lockForUpdate()->find($this->id);

            if (! $lockedPayment) {
                return;
            }

            $payload = $lockedPayment->payload ?? [];
            $invoiceIds = collect(data_get($payload, 'invoice_ids', []))
                ->filter()
                ->map(fn (mixed $id) => (int) $id)
                ->unique()
                ->values();

            if ($invoiceIds->isEmpty() || data_get($payload, 'bundle_allocated')) {
                return;
            }

            $invoices = FeeInvoice::query()
                ->with('feeItem')
                ->where('student_id', $lockedPayment->student_id)
                ->whereIn('id', $invoiceIds)
                ->lockForUpdate()
                ->get()
                ->sortBy(fn (FeeInvoice $invoice) => sprintf('%s-%010d', optional($invoice->due_date)->format('Ymd') ?: '99999999', $invoice->id))
                ->values();

            $remaining = (float) $lockedPayment->amount;
            $allocations = [];

            foreach ($invoices as $index => $invoice) {
                $invoice->refresh();

                if ($remaining <= 0 || (float) $invoice->balance <= 0) {
                    continue;
                }

                $applied = min((float) $invoice->balance, $remaining);

                static::create([
                    'fee_invoice_id' => $invoice->id,
                    'student_id' => $invoice->student_id,
                    'provider' => $lockedPayment->provider,
                    'reference' => $lockedPayment->reference.'-'.($index + 1).'-'.Str::upper(Str::random(4)),
                    'receipt_no' => $lockedPayment->receipt_no,
                    'gateway_reference' => $lockedPayment->gateway_reference,
                    'amount' => $applied,
                    'currency' => $lockedPayment->currency,
                    'status' => PaymentStatus::Paid,
                    'channel' => $lockedPayment->channel,
                    'paid_at' => $lockedPayment->paid_at ?? now(),
                    'recorded_by' => $lockedPayment->recorded_by,
                    'note' => 'Allocated from grouped payment '.$lockedPayment->reference,
                    'payload' => ['source' => 'bundle_allocation', 'parent_payment_id' => $lockedPayment->id],
                ]);

                $invoice->refresh()->syncBalance();
                $invoice->refresh()->loadMissing('feeItem');

                $allocations[] = [
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'fee_item' => $invoice->feeItem->name ?? 'School fee payment',
                    'amount_due' => (float) $invoice->amount_due,
                    'amount_paid_now' => $applied,
                    'amount_paid_total' => (float) $invoice->amount_paid,
                    'balance' => (float) $invoice->balance,
                    'status' => (string) $invoice->status,
                ];

                $remaining -= $applied;
            }

            $payload['bundle_allocated'] = true;
            $payload['allocated_invoices'] = $allocations;

            $lockedPayment->forceFill(['payload' => $payload])->save();
        });

        $this->refresh();
    }
}
