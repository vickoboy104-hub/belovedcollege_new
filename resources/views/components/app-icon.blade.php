@props(['name' => 'circle'])

<svg
    {{ $attributes->merge(['class' => 'app-icon h-5 w-5']) }}
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
>
    @switch($name)
        @case('dashboard')
            <rect x="3" y="3" width="7" height="8" rx="1.5" />
            <rect x="14" y="3" width="7" height="5" rx="1.5" />
            <rect x="14" y="12" width="7" height="9" rx="1.5" />
            <rect x="3" y="15" width="7" height="6" rx="1.5" />
            @break

        @case('search')
            <circle cx="11" cy="11" r="6.5" />
            <path d="m16 16 4 4" />
            @break

        @case('close')
        @case('x')
            <path d="M6 6l12 12" />
            <path d="M18 6 6 18" />
            @break

        @case('logout')
            <path d="M10 5H6.5A2.5 2.5 0 0 0 4 7.5v9A2.5 2.5 0 0 0 6.5 19H10" />
            <path d="M14 8l4 4-4 4" />
            <path d="M18 12H9" />
            @break

        @case('bell')
            <path d="M18 9a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
            <path d="M10 21h4" />
            @break

        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="m4 7 8 6 8-6" />
            @break

        @case('people')
            <path d="M16.5 19c0-2.2-1.8-4-4.5-4s-4.5 1.8-4.5 4" />
            <circle cx="12" cy="9" r="3" />
            <path d="M5.5 17.5c-.9-.7-1.5-1.8-1.5-3 0-2 1.5-3.5 3.5-3.5" />
            <path d="M18.5 17.5c.9-.7 1.5-1.8 1.5-3 0-2-1.5-3.5-3.5-3.5" />
            @break

        @case('school')
            <path d="M3 10.5 12 5l9 5.5" />
            <path d="M5 10v8.5h14V10" />
            <path d="M9 18.5V14h6v4.5" />
            <path d="M12 5v13.5" />
            @break

        @case('student')
            <path d="M4 8l8-4 8 4-8 4-8-4Z" />
            <path d="M7 10.5v4.2c0 1.6 2.2 3.3 5 3.3s5-1.7 5-3.3v-4.2" />
            <path d="M20 8v5" />
            @break

        @case('parents')
            <circle cx="8" cy="9" r="3" />
            <path d="M3.5 19c.4-2.5 2.1-4 4.5-4s4.1 1.5 4.5 4" />
            <circle cx="16.5" cy="10.5" r="2.5" />
            <path d="M13.5 18.5c.6-1.8 1.9-2.8 3.6-2.8 1.6 0 2.9 1 3.4 2.8" />
            @break

        @case('staff')
            <path d="M8 7a4 4 0 0 1 8 0v1H8V7Z" />
            <rect x="4" y="8" width="16" height="11" rx="2" />
            <path d="M9 13h6" />
            <path d="M12 10v6" />
            @break

        @case('reports')
            <path d="M7 3h7l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
            <path d="M14 3v5h5" />
            <path d="M9 13h6" />
            <path d="M9 17h4" />
            @break

        @case('settings')
            <path d="M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z" />
            <path d="M19 13.5v-3l-2-.7a6.8 6.8 0 0 0-.8-1.9l.9-1.9-2.1-2.1-1.9.9a6.8 6.8 0 0 0-1.9-.8l-.7-2h-3l-.7 2a6.8 6.8 0 0 0-1.9.8L3 3.9 1 6l.9 1.9a6.8 6.8 0 0 0-.8 1.9l-2 .7v3l2 .7a6.8 6.8 0 0 0 .8 1.9L1 18l2 2.1 1.9-.9a6.8 6.8 0 0 0 1.9.8l.7 2h3l.7-2a6.8 6.8 0 0 0 1.9-.8l1.9.9 2.1-2.1-.9-1.9a6.8 6.8 0 0 0 .8-1.9l2-.7Z" transform="translate(2 1) scale(.85)" />
            @break

        @case('bills')
            <path d="M6 3h12v18l-2-1.2-2 1.2-2-1.2-2 1.2-2-1.2L6 21V3Z" />
            <path d="M9 8h6" />
            <path d="M9 12h6" />
            <path d="M9 16h3" />
            @break

        @case('finance-records')
            <path d="M4 19V5" />
            <path d="M4 19h16" />
            <path d="M8 15v-4" />
            <path d="M12 15V8" />
            <path d="M16 15v-6" />
            <path d="M20 15v-2" />
            @break

        @case('learning')
            <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21V5.5Z" />
            <path d="M8 7h8" />
            <path d="M8 11h6" />
            <path d="M4 17.5A2.5 2.5 0 0 1 6.5 15H20" />
            @break

        @case('portal')
            <rect x="4" y="5" width="16" height="12" rx="2" />
            <path d="M9 21h6" />
            <path d="M12 17v4" />
            <path d="M8 9h8" />
            <path d="M8 13h5" />
            @break

        @case('profile')
        @case('avatar')
            <circle cx="12" cy="8" r="3.5" />
            <path d="M5 20c.8-3.2 3.2-5 7-5s6.2 1.8 7 5" />
            @break

        @case('plus')
            <path d="M12 5v14" />
            <path d="M5 12h14" />
            @break

        @case('download')
            <path d="M12 3v12" />
            <path d="m7 10 5 5 5-5" />
            <path d="M5 21h14" />
            @break

        @case('print')
            <path d="M7 8V4h10v4" />
            <rect x="6" y="14" width="12" height="7" rx="1.5" />
            <path d="M6 18H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2" />
            @break

        @case('save')
            <path d="M5 3h12l2 2v16H5V3Z" />
            <path d="M8 3v6h8" />
            <path d="M8 17h8" />
            @break

        @case('back')
            <path d="m12 19-7-7 7-7" />
            <path d="M19 12H5" />
            @break

        @case('check')
            <path d="m5 12 4 4L19 6" />
            @break

        @case('alert-circle')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v6" />
            <path d="M12 17h.01" />
            @break

        @case('edit')
            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z" />
            @break

        @case('eye')
            <path d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
            <circle cx="12" cy="12" r="2.5" />
            @break

        @case('wallet')
            <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5H19v14H6.5A2.5 2.5 0 0 1 4 16.5v-9Z" />
            <path d="M16 12h4" />
            <path d="M16 12.01h.01" />
            @break

        @case('bank')
            <path d="M3 10h18" />
            <path d="m5 10 7-5 7 5" />
            <path d="M6 10v8" />
            <path d="M10 10v8" />
            <path d="M14 10v8" />
            <path d="M18 10v8" />
            <path d="M4 18h16" />
            @break

        @case('cash')
        @case('currency-dollar')
            <rect x="3" y="6" width="18" height="12" rx="2" />
            <circle cx="12" cy="12" r="2.5" />
            <path d="M6.5 9v.01" />
            <path d="M17.5 15v.01" />
            @break

        @case('calculator')
            <rect x="5" y="3" width="14" height="18" rx="2" />
            <path d="M8 7h8" />
            <path d="M8 11h.01" />
            <path d="M12 11h.01" />
            <path d="M16 11h.01" />
            <path d="M8 15h.01" />
            <path d="M12 15h.01" />
            <path d="M16 15h.01" />
            @break

        @case('layers')
            <path d="m12 3 9 5-9 5-9-5 9-5Z" />
            <path d="m3 12 9 5 9-5" />
            <path d="m3 16 9 5 9-5" />
            @break

        @case('shield-check')
            <path d="M12 3 20 6v6c0 5-3.4 8-8 9-4.6-1-8-4-8-9V6l8-3Z" />
            <path d="m9 12 2 2 4-5" />
            @break

        @case('activity')
            <path d="M3 12h4l2-6 4 12 2-6h6" />
            @break

        @case('message-square')
            <path d="M5 5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H9l-5 4v-4H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
            @break

        @case('cbt')
            <rect x="4" y="4" width="16" height="12" rx="2" />
            <path d="M8 20h8" />
            <path d="M12 16v4" />
            <path d="M8 8h8" />
            <path d="M8 12h5" />
            @break

        @case('x')
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
            @break

        @case('announcement')
            <path d="M4 11v2a2 2 0 0 0 2 2h2l5 4v-4h3l4 2V7l-4 2H6a2 2 0 0 0-2 2Z" />
            <path d="M8 15l1 4" />
            @break

        @case('finance')
            <circle cx="12" cy="12" r="8" />
            <path d="M12 7v10" />
            <path d="M15 9.5c-.7-.7-1.7-1-3-1-1.6 0-2.5.7-2.5 1.8 0 2.8 5.5 1 5.5 4 0 1.1-.9 1.9-2.7 1.9-1.2 0-2.4-.4-3.3-1.2" />
            @break

        @case('classes')
            <rect x="4" y="5" width="7" height="6" rx="1.5" />
            <rect x="13" y="5" width="7" height="6" rx="1.5" />
            <rect x="4" y="13" width="7" height="6" rx="1.5" />
            <rect x="13" y="13" width="7" height="6" rx="1.5" />
            @break

        @default
            <circle cx="12" cy="12" r="8" />
    @endswitch
</svg>
