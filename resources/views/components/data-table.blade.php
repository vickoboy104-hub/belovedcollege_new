@props([
    'headers' => [],
    'pagination' => null,
    'minWidth' => '1050px',
    'stickyEdges' => true,
    'stickyActions' => true,
    'label' => 'Scrollable data table',
])

@php
    $lastHeader = collect($headers)->last();
    $lastHeaderText = strtolower(trim(strip_tags((string) $lastHeader)));
    $hasActionColumn = $stickyActions && str_contains($lastHeaderText, 'action');
@endphp

<div {{ $attributes->merge(['class' => 'admin-table-card admin-table-wrap']) }}>
    <div
        class="admin-table-scroll"
        role="region"
        aria-label="{{ $label }}"
        tabindex="0"
    >
        <table
            class="admin-data-table {{ $stickyEdges ? 'has-sticky-edge-columns' : '' }} {{ $hasActionColumn ? 'has-sticky-actions' : '' }}"
            style="min-width: {{ $minWidth }};"
            @if ($hasActionColumn) data-sticky-actions @endif
        >
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>
                            {!! $header !!}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if($pagination || isset($paginationSlot))
        <div class="admin-table-pagination">
            {{ $pagination ?? $paginationSlot }}
        </div>
    @endif
</div>
