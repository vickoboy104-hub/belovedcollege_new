@props(['status', 'label' => null])

@php
    $normalized = strtolower(trim((string) $status));
    
    $config = [
        'active' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'lbl' => 'Active'],
        'published' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'lbl' => 'Published'],
        'paid' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'lbl' => 'Paid'],
        'approved' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'lbl' => 'Approved'],
        'present' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'lbl' => 'Present'],
        'confirmed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'lbl' => 'Confirmed'],
        
        'draft' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'lbl' => 'Draft'],
        'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'lbl' => 'Pending'],
        'part-paid' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-800', 'border' => 'border-orange-200', 'lbl' => 'Part-paid'],
        'part_paid' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-800', 'border' => 'border-orange-200', 'lbl' => 'Part-paid'],
        'partially paid' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-800', 'border' => 'border-orange-200', 'lbl' => 'Part-paid'],
        'late' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'lbl' => 'Late'],
        'excused' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'lbl' => 'Excused'],
        'marking' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'lbl' => 'Marking'],
        
        'inactive' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'lbl' => 'Inactive'],
        'unpaid' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'lbl' => 'Unpaid'],
        'overdue' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'lbl' => 'Overdue'],
        'rejected' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'lbl' => 'Rejected'],
        'absent' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'lbl' => 'Absent'],
        'failed' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'lbl' => 'Failed'],
        
        'marked' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'lbl' => 'Marked'],
        'submitted' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'lbl' => 'Submitted'],
        'closed' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-300', 'lbl' => 'Closed'],
        'open' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-200', 'lbl' => 'Open'],
    ][$normalized] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'lbl' => ucfirst($status)];
@endphp

<span {{ $attributes->merge(['class' => 'status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border ' . $config['bg'] . ' ' . $config['text'] . ' ' . $config['border']]) }}>
    {{ $label ?? $config['lbl'] }}
</span>
