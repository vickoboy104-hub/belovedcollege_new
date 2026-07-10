@props(['title', 'className', 'subjectName', 'teacherName', 'date', 'description' => null, 'resourceUrl' => null, 'resourceUrlText' => 'Open Resource', 'actions' => null])

<div {{ $attributes->merge(['class' => 'card bg-white border border-[#c8d6ea] rounded-[18px] p-6 shadow-[0_10px_25px_rgba(15,23,42,0.08)] hover:border-[#fbbf24] hover:shadow-[0_16px_35px_rgba(15,23,42,0.14)] hover:-translate-y-0.5 transition-all duration-250 flex flex-col justify-between gap-5']) }}>
    <div class="space-y-3">
        <!-- Top bar with Icon and Date -->
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-icon-box icon="learning" color="blue" size="md" />
                <div>
                    <h3 class="display-font text-lg font-bold text-slate-900 leading-snug">
                        {{ $title }}
                    </h3>
                    <p class="text-xs font-bold text-slate-500 mt-0.5">
                        {{ $className }} &bull; {{ $subjectName }}
                    </p>
                </div>
            </div>
            <span class="text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-100 px-2.5 py-1 rounded-[8px] shrink-0">
                {{ $date }}
            </span>
        </div>

        <!-- Description / Excerpt -->
        @if($description)
            <p class="text-sm text-slate-600 leading-relaxed font-medium">
                {{ \Illuminate\Support\Str::limit($description, 180) }}
            </p>
        @endif

        <!-- Meta list -->
        <div class="flex items-center gap-2 text-xs font-bold text-slate-500 bg-slate-50/50 p-2.5 rounded-[12px] border border-slate-100">
            <x-app-icon name="profile" class="h-4 w-4 text-blue-500" />
            <span>Teacher: <strong class="text-slate-700">{{ $teacherName }}</strong></span>
        </div>
    </div>

    <!-- Actions block -->
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 mt-1">
        <div>
            @if($resourceUrl)
                <a href="{{ $resourceUrl }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 transition group">
                    <span>{{ $resourceUrlText }}</span>
                    <span class="transform group-hover:translate-x-0.5 transition-transform duration-150">&rarr;</span>
                </a>
            @endif
        </div>
        
        @if($actions || isset($actionsSlot))
            <div class="flex items-center gap-2">
                {{ $actions ?? $actionsSlot }}
            </div>
        @endif
    </div>
</div>
