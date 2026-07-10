@php
    if (! empty($schoolSettings['admin_background_image'])) {
        $adminBackground = "linear-gradient(135deg, rgba(244, 248, 255, 0.84), rgba(235, 242, 253, 0.88)), url('".asset($schoolSettings['admin_background_image'])."')";
    } else {
        $adminBackground = null; // Use CSS variable from theme-head
    }
@endphp

<div aria-hidden="true" class="site-background-stack">
    <div class="site-background-band" style="top: 0; bottom: 0;@if($adminBackground) background-image: {!! $adminBackground !!};@else background: var(--admin-bg-gradient);@endif"></div>
</div>
