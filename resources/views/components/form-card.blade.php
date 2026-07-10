@props(['action' => '', 'method' => 'POST', 'title' => null, 'description' => null])

<div {{ $attributes->merge(['class' => 'form-section']) }}>
    @if($title || $description)
        <div class="form-section-header">
            @if($title)
                <h3 class="section-title">
                    {{ $title }}
                </h3>
            @endif
            @if($description)
                <p class="section-description">
                    {{ $description }}
                </p>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ $action }}" {{ $attributes->only(['enctype', 'id', 'class']) }}>
        @csrf
        @if(in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
            @method($method)
        @endif

        <div class="form-stack">
            {{ $slot }}
        </div>

        @if(isset($actions))
            <div class="form-actions">
                {{ $actions }}
            </div>
        @endif
    </form>
</div>
