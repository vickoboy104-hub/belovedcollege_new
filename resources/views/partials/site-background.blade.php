@php
    $backgroundImages = [
        ! empty($schoolSettings['site_background_1']) ? asset($schoolSettings['site_background_1']) : null,
        ! empty($schoolSettings['site_background_2']) ? asset($schoolSettings['site_background_2']) : null,
        ! empty($schoolSettings['site_background_3']) ? asset($schoolSettings['site_background_3']) : null,
    ];

    $fallbackBackgrounds = [
        "linear-gradient(135deg, rgba(242, 247, 255, 0.90), rgba(230, 239, 253, 0.82)), radial-gradient(circle at top left, rgba(11, 42, 102, 0.18), transparent 34%)",
        "linear-gradient(135deg, rgba(248, 250, 255, 0.88), rgba(235, 244, 255, 0.82)), radial-gradient(circle at top right, rgba(2, 132, 199, 0.16), transparent 30%)",
        "linear-gradient(135deg, rgba(247, 250, 255, 0.88), rgba(235, 242, 251, 0.82)), radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.14), transparent 32%)",
    ];

    $resolvedBackgrounds = collect($backgroundImages)->map(function (?string $image, int $index) use ($fallbackBackgrounds, $schoolSettings) {
        if (! $image) {
            return $fallbackBackgrounds[$index];
        }

        $overlayPercent = max(0, min(100, (int) ($schoolSettings["site_background_".($index + 1)."_opacity"] ?? 78)));
        $overlayStrong = round($overlayPercent / 100, 2);
        $overlaySoft = round(min(1, $overlayStrong + 0.06), 2);

        return "linear-gradient(135deg, rgba(248, 251, 255, {$overlayStrong}), rgba(238, 245, 255, {$overlaySoft})), url('{$image}')";
    })->all();
@endphp

<div aria-hidden="true" class="site-background-stack">
    <div class="site-background-band is-top" style="background-image: {!! $resolvedBackgrounds[0] !!};"></div>
    <div class="site-background-band is-middle" style="background-image: {!! $resolvedBackgrounds[1] !!};"></div>
    <div class="site-background-band is-bottom" style="background-image: {!! $resolvedBackgrounds[2] !!};"></div>
</div>
