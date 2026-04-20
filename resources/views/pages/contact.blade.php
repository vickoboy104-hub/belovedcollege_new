@extends('layouts.public')

@section('content')
    @php
        $contactEmail = $schoolSettings['school_email'] ?? $schoolSettings['contact_email_recipient'] ?? 'vickoboy104@gmail.com';
        $whatsappNumber = preg_replace('/\D+/', '', $schoolSettings['whatsapp_number'] ?? '08165587119');
        $whatsappDigits = str_starts_with($whatsappNumber, '0') ? '234'.substr($whatsappNumber, 1) : $whatsappNumber;
        $whatsappLink = $schoolSettings['whatsapp_link'] ?? "https://wa.me/{$whatsappDigits}";
    @endphp
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[0.92fr,1.08fr]">
            <div class="mesh-card px-8 py-10 reveal-up">
                <p class="section-kicker text-sm font-semibold uppercase tracking-[0.28em]">Contact the school</p>
                <h1 class="display-font mt-4 text-4xl font-bold text-slate-950">Admissions, enquiries, and parent support.</h1>
                <div class="mt-6 space-y-4 text-sm leading-7 text-slate-600">
                    <p>BELOVED SCHOOLS welcomes parents, guardians, and well-wishers who want to learn more about admission, academics, or student life.</p>
                    <p><span class="font-semibold text-slate-900">Phone:</span> {{ $schoolSettings['school_phone'] ?? '08067046701' }}</p>
                    <p><span class="font-semibold text-slate-900">Email:</span> <a href="mailto:{{ $contactEmail }}" class="font-semibold text-[color:var(--theme-primary)]">{{ $contactEmail }}</a></p>
                    <p><span class="font-semibold text-slate-900">WhatsApp:</span> <a href="{{ $whatsappLink }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-[color:var(--theme-primary)]">{{ $schoolSettings['whatsapp_number'] ?? '08165587119' }}</a></p>
                    <p><span class="font-semibold text-slate-900">Address:</span> {{ $schoolSettings['school_address'] ?? 'Ayeteju Street, Ore, Ondo State' }}</p>
                    <p><span class="font-semibold text-slate-900">Established:</span> 2006</p>
                </div>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="mailto:{{ $contactEmail }}" class="theme-button">Email Us</a>
                    <a href="{{ $whatsappLink }}" target="_blank" rel="noopener noreferrer" class="theme-button-secondary">Chat on WhatsApp</a>
                </div>
            </div>

            <div class="section-card reveal-up">
                <h2 class="display-font text-2xl font-bold text-slate-950">Send a message</h2>
                <form method="POST" action="{{ route('contact.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <input name="name" value="{{ old('name') }}" placeholder="Full name" class="theme-input" required />
                        <input name="email" value="{{ old('email') }}" type="email" placeholder="Email address" class="theme-input" required />
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="phone-field">
                            <input id="contact-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" value="{{ old('phone') }}" placeholder="Phone number" class="theme-input" />
                            <button type="button" class="contact-picker-button" x-data="contactField({ target: 'contact-phone' })" x-show="supported" x-cloak @click="pick()">Pick</button>
                        </div>
                        <input name="subject" value="{{ old('subject') }}" placeholder="Subject" class="theme-input" required />
                    </div>
                    <textarea name="message" rows="6" placeholder="How can the school help?" class="theme-input w-full" required>{{ old('message') }}</textarea>
                    <button type="submit" class="theme-button">Send message</button>
                </form>
            </div>
        </div>
    </section>
@endsection
