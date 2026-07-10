@props(['date', 'status', 'note' => null])

@php
    $normalized = strtolower(trim((string) $status));
    
    $config = [
        'present' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'classes', 'color' => 'green'],
        'late' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'icon' => 'circle', 'color' => 'gold'],
        'absent' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'icon' => 'circle', 'color' => 'red'],
        'excused' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'circle', 'color' => 'blue'],
    ][$normalized] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'icon' => 'circle', 'color' => 'gray'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-4 p-4 rounded-[16px] border border-[#c8d6ea] bg-white shadow-[0_4px_12px_rgba(15,23,42,0.04)] hover:border-blue-400 hover:shadow-[0_8px_20px_rgba(15,23,42,0.08)] transition-all duration-200']) }}>
    <!-- Left Icon Block -->
    <x-icon-box :icon="$config['icon']" :color="$config['color']" size="sm" class="mt-0.5" />
    
    <!-- Info -->
    <div class="flex-1 min-w-0 space-y-1">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <span class="text-sm font-bold text-slate-800 tracking-tight">
                {{ $date }}
            </span>
            <x-status-badge :status="$status" class="scale-90 origin-left" />
        </div>
        
        @if($note)
            <p class="text-xs font-semibold text-slate-500 italic mt-0.5">
                Note: {{ $note }}
            </p>
        @endif
    </div>
</div>
