@props(['title' => null])

<x-app-layout>
    @if(isset($header) || $title)
        <x-slot name="header">
            @if(isset($header))
                {{ $header }}
            @else
                <div class="flex items-center justify-between gap-4">
                    <h1 class="display-font text-2xl font-extrabold text-slate-900 leading-snug">
                        {{ $title }}
                    </h1>
                </div>
            @endif
        </x-slot>
    @endif

    {{ $slot }}
</x-app-layout>
