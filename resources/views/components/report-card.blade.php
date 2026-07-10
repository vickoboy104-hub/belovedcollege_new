@props(['student', 'session', 'term', 'attendance' => null, 'remark' => null, 'principalRemark' => null, 'actions' => null])

<div {{ $attributes->merge(['class' => 'card bg-white border-2 border-slate-300 rounded-[20px] p-6 sm:p-8 shadow-xl max-w-4xl mx-auto relative overflow-hidden']) }}>
    <!-- Official Blue accent border top -->
    <div class="absolute top-0 inset-x-0 h-2 bg-[#071833]"></div>
    
    <!-- Fine grid background pattern -->
    <div class="absolute right-0 bottom-0 top-0 w-1/3 opacity-[0.02] flex items-center justify-center pointer-events-none select-none">
        <x-application-logo class="w-64 h-64 transform translate-x-12 translate-y-6 shrink-0" />
    </div>

    <!-- Header Block -->
    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between border-b-2 border-[#071833] pb-6">
        <div class="flex items-center gap-4">
            <x-application-logo class="h-16 w-16 text-[#071833] shrink-0" />
            <div>
                <h2 class="display-font text-2xl font-black text-slate-900 uppercase tracking-tight">
                    Beloved Schools
                </h2>
                <p class="text-[10px] font-bold text-[#fbbf24] uppercase tracking-[0.25em]">
                    Building Minds, Shaping Character
                </p>
                <p class="text-xs font-semibold text-slate-500 mt-1">
                    Ore, Ondo State, Nigeria. &bull; Est. 2006
                </p>
            </div>
        </div>
        
        <div class="text-left sm:text-right">
            <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold bg-[#071833] text-[#fbbf24] uppercase tracking-wider border border-[#071833]">
                Official Academic Report
            </span>
            <p class="text-xs font-bold text-slate-600 mt-2">
                Session: <strong class="text-[#071833]">{{ $session }}</strong>
            </p>
            <p class="text-xs font-bold text-slate-600">
                Term: <strong class="text-[#071833]">{{ $term }}</strong>
            </p>
        </div>
    </div>

    <!-- Student Details Summary -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-6 border-b border-slate-200 text-xs font-bold text-slate-500">
        <div>
            <div>Student Name</div>
            <div class="text-sm font-extrabold text-slate-950 mt-1">{{ $student->name }}</div>
        </div>
        <div>
            <div>Admission No.</div>
            <div class="text-sm font-extrabold text-slate-950 mt-1">{{ $student->admission_no ?? $student->identifier }}</div>
        </div>
        <div>
            <div>Class</div>
            <div class="text-sm font-extrabold text-slate-950 mt-1">{{ $student->class->name ?? 'JSS 1' }}</div>
        </div>
        <div>
            <div>Attendance</div>
            <div class="text-sm font-extrabold text-emerald-600 mt-1">{{ $attendance ?? '92%' }}</div>
        </div>
    </div>

    <!-- Main Subject Table Area -->
    <div class="my-6">
        {{ $slot }}
    </div>

    <!-- Bottom Remarks -->
    <div class="grid md:grid-cols-2 gap-6 border-t border-slate-200 pt-6 mt-6">
        @if($remark)
            <div class="bg-slate-50 p-4 rounded-[14px] border border-slate-200">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Teacher's Remark</div>
                <p class="text-xs font-semibold text-slate-700 leading-relaxed italic">
                    "{{ $remark }}"
                </p>
            </div>
        @endif
        @if($principalRemark || $remark)
            <div class="bg-slate-50 p-4 rounded-[14px] border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Principal's Decision</div>
                    <p class="text-xs font-semibold text-slate-700 leading-relaxed italic">
                        "{{ $principalRemark ?? 'Excellent performance. Approved for promotion.' }}"
                    </p>
                </div>
                
                <div class="border-t border-slate-200/80 pt-3 mt-4 flex items-center justify-between text-[10px] font-bold text-slate-400">
                    <span>Authorized Signature</span>
                    <span class="text-slate-500">Beloved Schools Principal</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Print control panel -->
    @if($actions || isset($actionsSlot))
        <div class="print:hidden flex items-center justify-end gap-3 border-t border-slate-100 pt-5 mt-8">
            {{ $actions ?? $actionsSlot }}
        </div>
    @endif
</div>
