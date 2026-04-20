<div class="flex flex-wrap gap-3">
    <a
        href="{{ route('admin.finance') }}"
        class="rounded-full px-5 py-3 text-sm font-semibold transition {{ $activeFinancePage === 'desk' ? 'text-white' : 'border border-slate-300 text-slate-700' }}"
        @if ($activeFinancePage === 'desk')
            style="background-color: var(--theme-primary);"
        @endif
    >
        Finance desk
    </a>
    <a
        href="{{ route('admin.finance.records') }}"
        class="rounded-full px-5 py-3 text-sm font-semibold transition {{ $activeFinancePage === 'records' ? 'text-white' : 'border border-slate-300 text-slate-700' }}"
        @if ($activeFinancePage === 'records')
            style="background-color: var(--theme-primary);"
        @endif
    >
        Records and printable lists
    </a>
</div>
