@props([
    'items' => [],
    'active' => null,
])

<nav class="section-nav" aria-label="Section navigation">
    <div class="section-nav-strip">
        @foreach ($items as $item)
            <a
                href="{{ $item['href'] }}"
                class="section-nav-link {{ ($active === $item['key']) ? 'border-transparent text-white shadow-sm' : 'text-slate-700' }}"
                @if ($active === $item['key'])
                    style="background-color: var(--theme-primary);"
                @endif
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>
