@php
    $audience = $audience ?? 'generic';
    $config = match ($audience) {
        'student' => [
            'title' => 'Student Login',
            'subtitle' => 'Use your admission number or student ID to access your portal.',
            'identifier' => 'Admission Number or Student ID',
            'route' => 'student.login.store',
            'switch' => route('staff.login'),
            'switch_label' => 'Go to staff login',
        ],
        'staff' => [
            'title' => 'Staff Login',
            'subtitle' => 'Use your email address or staff ID to access the staff workspace.',
            'identifier' => 'Email Address or Staff ID',
            'route' => 'staff.login.store',
            'switch' => route('student.login'),
            'switch_label' => 'Go to student login',
        ],
        default => [
            'title' => 'Portal Login',
            'subtitle' => 'Choose the right sign-in path for your role.',
            'identifier' => 'Email, Admission Number, or Staff ID',
            'route' => 'login',
            'switch' => route('student.login'),
            'switch_label' => 'Student login',
        ],
    };
@endphp

<x-guest-layout>
    <div class="auth-login-header">
        <p class="auth-form-kicker">{{ $config['title'] }}</p>
        <h1 class="display-font mt-3 text-3xl font-bold text-slate-950">{{ $config['title'] }}</h1>
        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $config['subtitle'] }}</p>

        <div class="auth-segment mt-5">
            <a href="{{ route('student.login') }}" class="auth-segment-link {{ $audience === 'student' ? 'is-active' : '' }}">Student</a>
            <a href="{{ route('staff.login') }}" class="auth-segment-link {{ $audience === 'staff' ? 'is-active' : '' }}">Staff</a>
        </div>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route($config['route']) }}" class="mt-7 space-y-5">
        @csrf
        <input type="hidden" name="audience" value="{{ $audience }}">

        <div>
            <x-input-label for="login" :value="$config['identifier']" class="auth-label" />
            <x-text-input id="login" class="auth-input mt-2 block w-full" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="auth-label" />
            <x-password-input
                id="password"
                name="password"
                autocomplete="current-password"
                :required="true"
                wrapper-class="mt-2"
                input-class="auth-input block w-full"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="auth-form-options flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-500" name="remember">
                <span>Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-slate-600 underline underline-offset-4 hover:text-slate-900" href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="theme-button auth-submit w-full justify-center text-center">
            Log in
        </button>
    </form>

    <div class="auth-notice mt-6 text-sm text-slate-600">
        <div class="font-semibold text-slate-900">Portal access</div>
        <p class="mt-2">{{ $schoolSettings['portal_notice'] ?? 'Your school administrator can create, reset, and share your temporary password.' }}</p>
        <div class="mt-3 flex flex-wrap gap-3">
            <a href="{{ $config['switch'] }}" class="font-semibold text-[color:var(--theme-primary)]">{{ $config['switch_label'] }}</a>
            <a href="{{ route('contact') }}" class="font-semibold text-[color:var(--theme-primary)]">Contact school</a>
        </div>
    </div>
</x-guest-layout>
