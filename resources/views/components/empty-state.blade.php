@props([
    'title' => 'Nothing here yet',
    'description' => null,
    'subtitle' => null,
    'explanation' => null,
    'icon' => 'circle',
    'actionUrl' => null,
    'actionText' => null,
])

@php
    $bodyText = $description ?? $subtitle ?? $explanation;
@endphp

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="empty-state-icon">
        <x-app-icon :name="$icon" />
    </div>
    
    <h3>
        {{ $title }}
    </h3>
    
    @if($bodyText)
        <p>
            {{ $bodyText }}
        </p>
    @endif

    @if($actionUrl && $actionText)
        <div class="mt-6">
            <x-action-button variant="primary" :href="$actionUrl">
                {{ $actionText }}
            </x-action-button>
        </div>
    @endif
</div>
