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
    <div class="mb-6 rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/5">
        <div class="section-kicker text-xs font-semibold uppercase tracking-[0.32em]">{{ $config['title'] }}</div>
        <h1 class="display-font mt-3 text-3xl font-bold text-slate-950">{{ $config['title'] }}</h1>
        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $config['subtitle'] }}</p>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('student.login') }}" class="{{ $audience === 'student' ? 'theme-button' : 'theme-button-secondary' }}">Student</a>
            <a href="{{ route('staff.login') }}" class="{{ $audience === 'staff' ? 'theme-button' : 'theme-button-secondary' }}">Staff</a>
        </div>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route($config['route']) }}" class="space-y-5">
        @csrf
        <input type="hidden" name="audience" value="{{ $audience }}">

        <div>
            <x-input-label for="login" :value="$config['identifier']" />
            <x-text-input id="login" class="mt-1 block w-full" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4">
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

        <button type="submit" class="theme-button w-full justify-center text-center">
            Log in
        </button>
    </form>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-600">
        <div class="font-semibold text-slate-900">Portal access</div>
        <p class="mt-2">{{ $schoolSettings['portal_notice'] ?? 'Your school administrator can create, reset, and share your temporary password.' }}</p>
        <div class="mt-3 flex flex-wrap gap-3">
            <a href="{{ $config['switch'] }}" class="font-semibold text-[color:var(--theme-primary)]">{{ $config['switch_label'] }}</a>
            <a href="{{ route('contact') }}" class="font-semibold text-[color:var(--theme-primary)]">Contact school</a>
        </div>
    </div>
</x-guest-layout>
