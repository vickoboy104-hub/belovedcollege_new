@props(['type' => 'button', 'variant' => 'primary', 'icon' => null, 'href' => null])

@php
    $baseClasses = 'btn inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[12px] font-bold text-xs uppercase tracking-wider transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-[0.98]';
    
    $variantClasses = [
        'primary' => 'btn-primary bg-[#071833] text-white border border-[#071833] hover:bg-[#0b1f3a] hover:border-[#0b1f3a] focus:ring-[#071833]',
        'secondary' => 'btn-secondary bg-white text-[#1d4ed8] border border-[#c8d6ea] hover:bg-slate-50 hover:border-[#b0c4de] focus:ring-[#1d4ed8]',
        'accent' => 'bg-[#fbbf24] text-[#071833] border border-[#fbbf24] hover:bg-[#fbbf24]/90 focus:ring-[#fbbf24] font-extrabold',
        'danger' => 'bg-rose-600 text-white border border-rose-600 hover:bg-rose-700 hover:border-rose-700 focus:ring-rose-600 btn-danger',
        'success' => 'btn-primary bg-[#071833] text-white border border-[#071833] hover:bg-[#0b1f3a] hover:border-[#0b1f3a] focus:ring-[#071833]',
    ][$variant] ?? 'bg-[#071833] text-white border border-[#071833] hover:bg-[#0b1f3a] focus:ring-[#071833]';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
        @if($icon)
            <x-app-icon :name="$icon" class="btn-icon shrink-0" />
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
        @if($icon)
            <x-app-icon :name="$icon" class="btn-icon shrink-0" />
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
