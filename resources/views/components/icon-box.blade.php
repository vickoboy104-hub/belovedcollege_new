@props(['icon', 'color' => 'blue', 'size' => 'md'])

@php
    $colorClasses = [
        'blue' => 'bg-blue-50 text-blue-600 border border-blue-100',
        'green' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
        'emerald' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
        'purple' => 'bg-purple-50 text-purple-600 border border-purple-100',
        'gold' => 'bg-amber-50 text-amber-600 border border-amber-100',
        'orange' => 'bg-orange-50 text-orange-600 border border-orange-100',
        'amber' => 'bg-amber-50 text-amber-600 border border-amber-100',
        'red' => 'bg-rose-50 text-rose-600 border border-rose-100',
        'rose' => 'bg-rose-50 text-rose-600 border border-rose-100',
        'gray' => 'bg-slate-50 text-slate-600 border border-slate-100',
    ][$color] ?? 'bg-blue-50 text-blue-600 border border-blue-100';

    $sizeClasses = [
        'sm' => 'w-8 h-8 rounded-[10px] p-1.5',
        'md' => 'w-10 h-10 rounded-[12px] p-2.5',
        'lg' => 'w-12 h-12 rounded-[14px] p-3',
    ][$size] ?? 'w-10 h-10 rounded-[12px] p-2.5';
@endphp

<div {{ $attributes->merge(['class' => 'icon-box flex items-center justify-center shrink-0 shadow-sm ' . $colorClasses . ' ' . $sizeClasses]) }}>
    <x-app-icon :name="$icon" class="w-full h-full" />
</div>
