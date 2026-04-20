@extends('layouts.public')

@section('content')
    @php
        $whatsappNumber = preg_replace('/\D+/', '', $schoolSettings['whatsapp_number'] ?? '08165587119');
        $whatsappDigits = str_starts_with($whatsappNumber, '0') ? '234'.substr($whatsappNumber, 1) : $whatsappNumber;
        $whatsappLink = $schoolSettings['whatsapp_link'] ?? "https://wa.me/{$whatsappDigits}";
    @endphp
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[1fr,0.9fr]">
            <div class="mesh-card px-8 py-10 reveal-up">
                <p class="section-kicker text-sm font-semibold uppercase tracking-[0.28em]">Admissions</p>
                <h1 class="display-font mt-4 text-4xl font-bold text-slate-950">Give your child the foundation for a successful future.</h1>
                <div class="mt-6 space-y-4 text-sm leading-7 text-slate-600">
                    <p>BELOVED SCHOOLS offers a full secondary school education from Junior Secondary School to Senior Secondary School in a disciplined and Godly learning environment.</p>
                    <p>Families can contact the school directly for guidance, interview scheduling, and clarification on admission requirements.</p>
                </div>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('contact') }}" class="theme-button">Enroll Today</a>
                    <a href="tel:{{ $schoolSettings['school_phone'] ?? '08067046701' }}" class="theme-button-secondary">Call Us: {{ $schoolSettings['school_phone'] ?? '08067046701' }}</a>
                    <a href="{{ $whatsappLink }}" target="_blank" rel="noopener noreferrer" class="theme-button-secondary">WhatsApp Us</a>
                </div>
            </div>

            <div class="section-card reveal-up">
                <h2 class="display-font text-2xl font-bold text-slate-950">Our Academic Program</h2>
                <div class="mt-5 space-y-3">
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="font-semibold text-slate-900">Junior Secondary School</div>
                        <div class="text-sm text-slate-500">JSS 1 - JSS 3</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="font-semibold text-slate-900">Senior Secondary School</div>
                        <div class="text-sm text-slate-500">SS 1 - SS 3</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="font-semibold text-slate-900">Senior Departments</div>
                        <div class="text-sm text-slate-500">Science, Commercial, and Art</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-2">
            @foreach ([
                ['step' => 'Program', 'title' => 'Junior Secondary School', 'copy' => 'Students in JSS 1 to JSS 3 receive a strong academic foundation with disciplined learning and close teacher guidance.'],
                ['step' => 'Program', 'title' => 'Senior Secondary School', 'copy' => 'Students in SS 1 to SS 3 are prepared for examinations, leadership, and future opportunities in higher education.'],
                ['step' => 'Department', 'title' => 'Science Department', 'copy' => 'The science track prepares students for careers and university paths in medicine, engineering, and scientific research.'],
                ['step' => 'Department', 'title' => 'Commercial Department', 'copy' => 'Students are guided in commerce-related subjects that prepare them for business, finance, and enterprise.'],
                ['step' => 'Department', 'title' => 'Art Department', 'copy' => 'The art department supports students with interests in communication, law, humanities, social sciences, and the creative arts.'],
                ['step' => 'Support', 'title' => 'Student-centered learning', 'copy' => 'Each department is designed to prepare students for future careers and higher education through focused instruction and moral guidance.'],
            ] as $item)
                <div class="section-card reveal-up">
                    <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">{{ $item['step'] }}</div>
                    <h2 class="display-font mt-3 text-2xl font-bold text-slate-950">{{ $item['title'] }}</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-600">{{ $item['copy'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="section-card mt-8 reveal-up">
            <div class="grid gap-6 lg:grid-cols-[1.15fr,0.85fr]">
                <div>
                    <h2 class="display-font text-3xl font-bold text-slate-950">Need help with admission?</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-600">For available spaces, admission guidance, or parent support, contact BELOVED SCHOOLS directly.</p>
                </div>
                <div class="space-y-3 text-sm text-slate-600">
                    <div><span class="font-semibold text-slate-900">Phone:</span> {{ $schoolSettings['school_phone'] ?? '08067046701' }}</div>
                    <div><span class="font-semibold text-slate-900">Address:</span> {{ $schoolSettings['school_address'] ?? 'Ayeteju Street, Ore, Ondo State' }}</div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('contact') }}" class="theme-button mt-3 inline-flex">Contact Us</a>
                        <a href="{{ $whatsappLink }}" target="_blank" rel="noopener noreferrer" class="theme-button-secondary mt-3 inline-flex">WhatsApp Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
