@php
    $adminBackground = ! empty($schoolSettings['admin_background_image'])
        ? "linear-gradient(135deg, rgba(244, 248, 255, 0.84), rgba(235, 242, 253, 0.88)), url('".asset($schoolSettings['admin_background_image'])."')"
        : "linear-gradient(135deg, rgba(242, 247, 255, 0.94), rgba(230, 239, 253, 0.88)), radial-gradient(circle at top left, rgba(11, 42, 102, 0.16), transparent 34%)";
@endphp

<div aria-hidden="true" class="site-background-stack">
    <div class="site-background-band" style="top: 0; bottom: 0; background-image: {!! $adminBackground !!};"></div>
</div>
