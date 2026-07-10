@props(['id' => 'portal-sidebar-search', 'items' => []])

<div
    x-data='portalSidebarSearch({ items: @js($items) })'
    x-on:keydown.escape.stop="clearSearch()"
    class="sidebar-search"
>
    <label class="sidebar-search-label" for="{{ $id }}">Search portal</label>

    <div class="sidebar-search-field">
        <x-app-icon name="search" class="sidebar-search-icon h-4 w-4" />
        <input
            id="{{ $id }}"
            x-ref="input"
            type="search"
            x-model="query"
            x-on:input="$nextTick(() => runSearch())"
            x-on:focus="runSearch()"
            class="sidebar-search-input"
            placeholder="Filter navigation"
            autocomplete="off"
        >
        <button
            type="button"
            x-cloak
            x-show="query.length"
            x-on:click="clearSearch()"
            class="sidebar-search-clear"
            aria-label="Clear portal search"
        >
            <x-app-icon name="close" class="h-3.5 w-3.5" />
        </button>
    </div>
</div>
