@props(['action' => '', 'method' => 'GET', 'title' => 'Filter Records', 'description' => null])

<div {{ $attributes->merge(['class' => 'admin-toolbar-card']) }}>
    <form method="{{ $method }}" action="{{ $action }}">
        <div class="admin-toolbar-layout">
            <div class="section-header">
                <div>
                <h3 class="section-title">
                    {{ $title }}
                </h3>
                @if($description)
                    <p class="section-description">
                        {{ $description }}
                    </p>
                @endif
                </div>
            </div>
            
            <div class="admin-toolbar-controls">
                {{ $slot }}
            </div>
        </div>
    </form>
</div>
