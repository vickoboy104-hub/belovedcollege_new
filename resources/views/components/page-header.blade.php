@props(['title', 'eyebrow' => null])

<div {{ $attributes->merge(['class' => 'admin-page-header']) }}>
    <div>
        @if($eyebrow || isset($eyebrowSlot))
            <p class="eyebrow">
                {{ $eyebrow ?? $eyebrowSlot }}
            </p>
        @endif
        <h1>
            {{ $title }}
        </h1>
        @isset($meta)
            <p class="page-meta">
                {{ $meta }}
            </p>
        @endisset
    </div>
    @if(isset($actions))
        <div class="page-actions">
            {{ $actions }}
        </div>
    @endif
</div>
