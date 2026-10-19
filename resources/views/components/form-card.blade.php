@props(['action' => '', 'method' => 'POST', 'title' => null])

<div {{ $attributes->merge(['class' => 'form-section']) }}>
    @if($title)
        <div class="form-section-header">
            <h3 class="section-title">
                {{ $title }}
            </h3>
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
