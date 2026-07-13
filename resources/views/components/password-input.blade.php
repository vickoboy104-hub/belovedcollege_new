@props([
    'id',
    'name',
    'autocomplete' => 'current-password',
    'required' => false,
    'disabled' => false,
    'wrapperClass' => '',
    'inputClass' => 'theme-input w-full',
])

<div x-data="{ passwordVisible: false }" class="relative {{ $wrapperClass }}" data-password-field>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        :type="passwordVisible ? 'text' : 'password'"
        autocomplete="{{ $autocomplete }}"
        @required($required)
        @disabled($disabled)
        class="{{ $inputClass }}"
        style="padding-right: 5.5rem;"
        data-password-input
        {{ $attributes->except(['class']) }}
    />

    <button
        type="button"
        class="absolute inset-y-0 right-2 my-auto inline-flex h-9 items-center gap-1.5 rounded-lg px-2.5 text-xs font-bold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-[color:var(--theme-primary)] focus:ring-offset-1"
        @click="passwordVisible = ! passwordVisible"
        :aria-label="passwordVisible ? 'Hide password' : 'Show password'"
        :title="passwordVisible ? 'Hide password' : 'Show password'"
        :aria-pressed="passwordVisible.toString()"
        aria-controls="{{ $id }}"
        data-password-toggle
    >
        <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path x-show="! passwordVisible" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6S2.25 12 2.25 12Z" />
            <circle x-show="! passwordVisible" cx="12" cy="12" r="2.75" stroke-width="2" />
            <path x-show="passwordVisible" style="display: none;" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 3 18 18M10.6 10.7a2 2 0 0 0 2.7 2.7M9.9 5.2A10.9 10.9 0 0 1 12 5c6.25 0 9.75 7 9.75 7a16.5 16.5 0 0 1-3.2 4.1M6.2 6.2C3.7 8 2.25 12 2.25 12s3.5 7 9.75 7c1.5 0 2.8-.4 4-1" />
        </svg>
        <span x-text="passwordVisible ? 'Hide' : 'Show'">Show</span>
    </button>
</div>
