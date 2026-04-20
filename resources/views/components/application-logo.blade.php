@if (! empty($schoolSettings['logo_path']))
    <img src="{{ asset($schoolSettings['logo_path']) }}" alt="{{ $schoolSettings['school_name'] ?? 'School logo' }}" {{ $attributes->merge(['class' => 'rounded-2xl object-cover']) }}>
@else
    <svg viewBox="0 0 88 88" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
        <defs>
            <linearGradient id="schoolmark" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="var(--theme-primary)" />
                <stop offset="58%" stop-color="var(--theme-secondary)" />
                <stop offset="100%" stop-color="var(--theme-highlight)" />
            </linearGradient>
        </defs>
        <rect x="4" y="4" width="80" height="80" rx="24" fill="url(#schoolmark)" />
        <path d="M19 34.5 44 20l25 14.5-25 14L19 34.5Z" fill="#fff" fill-opacity="0.92" />
        <path d="M26 44.5v12.4c0 5.8 8.1 10.6 18 10.6s18-4.8 18-10.6V44.5L44 55 26 44.5Z" fill="#fff" fill-opacity="0.92" />
        <circle cx="67.5" cy="41.5" r="3.5" fill="#fff" />
    </svg>
@endif
