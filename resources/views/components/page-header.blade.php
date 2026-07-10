@props(['title', 'description' => null, 'eyebrow' => null])

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
        @if($description)
            <p class="page-description">
                {{ $description }}
            </p>
        @endif
    </div>
    @if(isset($actions))
        <div class="page-actions">
            {{ $actions }}
        </div>
    @endif
</div>
