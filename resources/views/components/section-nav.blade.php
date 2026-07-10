@props([
    'items' => [],
    'active' => null,
])

<nav class="class-filter-panel" aria-label="Section navigation">
    <div class="class-chip-list">
        @foreach ($items as $item)
            <a
                href="{{ $item['href'] }}"
                class="class-chip {{ ($active === $item['key']) ? 'active' : '' }}"
                @if ($active === $item['key'])
                    aria-current="page"
                @endif
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>
