@props(['headers' => [], 'pagination' => null, 'minWidth' => '1050px', 'label' => 'Scrollable data table'])

<div {{ $attributes->merge(['class' => 'admin-table-card admin-table-wrap']) }}>
    <div
        class="admin-table-scroll"
        role="region"
        aria-label="{{ $label }}"
        tabindex="0"
    >
        <table class="admin-data-table" style="min-width: {{ $minWidth }};">
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
