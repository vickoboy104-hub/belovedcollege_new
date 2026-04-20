@props([
    'items' => [],
    'active' => null,
])

<nav class="mb-8 overflow-x-auto">
    <div class="flex min-w-max gap-3 pb-2">
        @foreach ($items as $item)
            <a
                href="{{ $item['href'] }}"
                class="rounded-full border px-4 py-2.5 text-sm font-semibold transition {{ ($active === $item['key']) ? 'border-transparent text-white' : 'border-slate-300 text-slate-700' }}"
                @if ($active === $item['key'])
                    style="background-color: var(--theme-primary);"
                @endif
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>
