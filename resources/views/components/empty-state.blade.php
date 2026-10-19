@props([
    'title' => 'Nothing here yet',
    'icon' => 'circle',
    'actionUrl' => null,
    'actionText' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="empty-state-icon">
        <x-app-icon :name="$icon" />
    </div>
    
    <h3>
        {{ $title }}
    </h3>
    
    @if($actionUrl && $actionText)
        <div class="mt-6">
            <x-action-button variant="primary" :href="$actionUrl">
                {{ $actionText }}
            </x-action-button>
        </div>
    @endif
</div>
