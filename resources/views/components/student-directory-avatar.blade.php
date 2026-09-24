@props(['user'])

@if($user->avatar_url)
    <img src="{{ $user->avatar_url }}" alt="{{ $user->fullName() }} profile photo" {{ $attributes->class(['table-avatar object-cover']) }} />
@else
    <div {{ $attributes->class(['table-avatar']) }}>
        {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
    </div>
@endif
