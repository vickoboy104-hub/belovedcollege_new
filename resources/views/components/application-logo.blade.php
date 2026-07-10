@if (! empty($schoolSettings['logo_path']))
    <img src="{{ asset($schoolSettings['logo_path']) }}" alt="{{ $schoolSettings['school_name'] ?? 'School logo' }}" {{ $attributes->merge(['class' => 'school-logo-image object-contain']) }}>
@else
    <svg viewBox="0 0 88 88" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
        <defs>
            <linearGradient id="schoolmark-gold" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#ffd166" />
                <stop offset="100%" stop-color="#f5b400" />
            </linearGradient>
            <filter id="schoolmark-shadow" x="-20%" y="-20%" width="140%" height="140%">
                <feDropShadow dx="0" dy="5" stdDeviation="4" flood-color="#020617" flood-opacity="0.22" />
            </filter>
        </defs>
        <g filter="url(#schoolmark-shadow)">
            <path d="M44 6 73 17v21c0 19-12.7 33.6-29 43C27.7 71.6 15 57 15 38V17L44 6Z" fill="#061a3f" stroke="url(#schoolmark-gold)" stroke-width="4" />
            <path d="M27 22c7.2-4.3 15.7-5.9 24.4-4.5 3.9.6 7.6 1.9 10.9 3.8" fill="none" stroke="url(#schoolmark-gold)" stroke-width="2.5" stroke-linecap="round" />
            <text x="44" y="52" text-anchor="middle" font-family="Montserrat, Arial, sans-serif" font-size="33" font-weight="800" fill="url(#schoolmark-gold)">B</text>
            <path d="M23 64c5.7 4.7 12.7 8 21 10 8.3-2 15.3-5.3 21-10" fill="none" stroke="url(#schoolmark-gold)" stroke-width="2.6" stroke-linecap="round" />
            <path d="M20 30c-6.5 8.7-7.7 19.5-3.2 29.3M68 30c6.5 8.7 7.7 19.5 3.2 29.3" fill="none" stroke="url(#schoolmark-gold)" stroke-width="2" stroke-linecap="round" opacity=".78" />
        </g>
    </svg>
@endif
