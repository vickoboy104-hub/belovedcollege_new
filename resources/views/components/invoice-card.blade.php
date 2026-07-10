@props([
    'invoiceNumber',
    'studentName',
    'className',
    'feeItems' => [],
    'total',
    'paid' => 0,
    'balance',
    'status',
    'actions' => null
])

<div {{ $attributes->merge(['class' => 'card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)] hover:border-[#fbbf24] hover:shadow-[0_16px_35px_rgba(15,23,42,0.14)] transition-all duration-250 flex flex-col justify-between gap-6']) }}>
    <!-- Header Block -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between border-b border-slate-100 pb-5">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5">
                <x-icon-box icon="bills" color="gold" size="sm" />
                <h3 class="display-font text-base font-extrabold text-slate-900 tracking-tight">
                    Invoice: <strong class="text-blue-600">{{ $invoiceNumber }}</strong>
                </h3>
            </div>
            <p class="text-xs font-bold text-slate-500">
                Student: <strong class="text-slate-800">{{ $studentName }}</strong> &bull; Class: <strong class="text-slate-850">{{ $className }}</strong>
            </p>
        </div>
        <x-status-badge :status="$status" class="shrink-0 scale-95" />
    </div>

    <!-- Fee Items Breakdowns -->
    @if(count($feeItems) > 0)
        <div class="rounded-[12px] border border-slate-200 overflow-hidden text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 font-bold text-slate-500">
                        <th class="px-4 py-2.5">Fee Item</th>
                        <th class="px-4 py-2.5 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @foreach($feeItems as $item)
                        <tr>
                            <td class="px-4 py-2.5">{{ $item['name'] ?? $item->name }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-slate-900">NGN {{ number_format((float) ($item['amount'] ?? $item->amount), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Totals Summaries -->
    <div class="grid grid-cols-3 gap-3 bg-slate-50 p-4 rounded-[14px] border border-slate-100/55 text-center text-xs font-bold">
        <div>
            <div class="text-[9px] uppercase tracking-wider text-slate-500">Total Billed</div>
            <div class="display-font text-sm font-extrabold text-slate-900 mt-1">NGN {{ number_format((float) $total, 2) }}</div>
        </div>
        <div class="border-x border-slate-200">
            <div class="text-[9px] uppercase tracking-wider text-slate-500">Amount Paid</div>
            <div class="display-font text-sm font-extrabold text-emerald-600 mt-1">NGN {{ number_format((float) $paid, 2) }}</div>
        </div>
        <div>
            <div class="text-[9px] uppercase tracking-wider text-slate-500">Balance Due</div>
            <div class="display-font text-sm font-extrabold text-rose-600 mt-1">NGN {{ number_format((float) $balance, 2) }}</div>
        </div>
    </div>

    <!-- Lower actions -->
    @if($actions || isset($actionsSlot))
        <div class="flex flex-wrap items-center justify-end gap-2.5 border-t border-slate-100 pt-4 mt-1">
            {{ $actions ?? $actionsSlot }}
        </div>
    @endif
</div>
