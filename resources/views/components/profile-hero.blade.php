@props(['name', 'role', 'id', 'avatar' => null, 'classDetails' => null, 'status' => 'Active'])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-gradient-to-r from-[#071833] via-[#0b1f3a] to-[#0f2c52] border border-[#c8d6ea] rounded-[24px] p-6 sm:p-8 shadow-[0_10px_30px_rgba(15,23,42,0.15)] text-white']) }}>
    <!-- Left top brand colored thin accent strip -->
    <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-blue-600 to-[#fbbf24]"></div>
    
    <!-- Watermark Stamp -->
    <div class="absolute right-0 bottom-0 top-0 w-1/3 opacity-[0.03] flex items-center justify-center pointer-events-none select-none">
        <x-application-logo class="w-48 h-48 transform translate-x-12 translate-y-6 rotate-12 shrink-0" />
    </div>

    <div class="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-center">
        <!-- Avatar Block -->
        <div class="shrink-0 flex items-center justify-center">
            @if($avatar)
                <img src="{{ $avatar }}" alt="{{ $name }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border-3 border-[#fbbf24] shadow-md object-cover" />
            @else
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border-3 border-[#fbbf24] bg-blue-600/20 text-[#fbbf24] flex items-center justify-center text-3xl font-extrabold shadow-md">
                    {{ collect(explode(' ', $name))->map(fn($part) => substr($part, 0, 1))->take(2)->join('') }}
                </div>
            @endif
        </div>

        <!-- Info Block -->
        <div class="space-y-2.5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-blue-600/35 border border-blue-500/30 text-blue-300">
                    {{ $role }}
                </span>
                @if($status)
                    <x-status-badge :status="$status" class="scale-90 origin-left" />
                @endif
            </div>

            <h2 class="display-font text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                {{ $name }}
            </h2>

            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs font-semibold text-slate-300">
                <div class="flex items-center gap-2">
                    <x-app-icon name="profile" class="h-4 w-4 text-[#fbbf24]" />
                    <span>ID: <strong class="text-white">{{ $id }}</strong></span>
                </div>
                @if($classDetails)
                    <div class="flex items-center gap-2 border-l border-white/20 pl-6">
                        <x-app-icon name="classes" class="h-4 w-4 text-[#fbbf24]" />
                        <span>Class: <strong class="text-white">{{ $classDetails }}</strong></span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
