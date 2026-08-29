<x-portal-layout>
    <x-slot name="header">
        <x-page-header title="Bank transfer verification" description="Review student transfer claims before they affect invoice balances." eyebrow="Finance desk" />
    </x-slot>

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('status') }}</div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-black text-[#071833]">Pending bank transfers</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">Only verify a claim after confirming the transaction in the official school bank account.</p>
            </div>
            <a href="{{ route('admin.finance', ['section' => 'record-payment']) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-black text-slate-700">Finance desk</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Bank Reference</th>
                            <th class="px-4 py-3">Submitted</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transfers as $payment)
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-slate-900">{{ $payment->student->user->fullName() }}</div>
                                    <div class="text-[11px] font-semibold text-slate-500">{{ $payment->student->admission_no }} • {{ $payment->student->schoolClass->display_name ?? 'No class' }}</div>
                                </td>
                                <td class="px-4 py-4 font-black text-slate-900">NGN {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="px-4 py-4 font-mono text-xs text-slate-700">{{ data_get($payment->payload, 'bank_reference') }}</td>
                                <td class="px-4 py-4 text-xs font-semibold text-slate-500">{{ $payment->created_at?->format('d M Y, h:i A') }}</td>
                                <td class="px-4 py-4 text-right">
                                    <form method="POST" action="{{ route('admin.bank-transfers.verify', $payment) }}" onsubmit="return confirm('Confirm that this transfer is visible in the school bank account?')">
                                        @csrf
                                        <button class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-black text-white hover:bg-emerald-700">Verify payment</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-sm font-semibold text-slate-500">No pending bank-transfer claims.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-portal-layout>
