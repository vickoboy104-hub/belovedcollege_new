@props(['user'])

@if($user->avatar_url)
    <img src="{{ $user->avatar_url }}" alt="{{ $user->fullName() }} profile photo" {{ $attributes->class(['table-avatar object-cover']) }} />
@else
    <div {{ $attributes->class(['table-avatar']) }}>
        {{ collect(explode(' ', $user->fullName()))->filter()->map(fn ($part) => substr($part, 0, 1))->take(2)->join('') ?: 'U' }}
    </div>
@endif
