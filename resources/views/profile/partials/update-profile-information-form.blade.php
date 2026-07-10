<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<x-form-card
    action="{{ route('profile.update') }}"
    method="PATCH"
    title="Profile Information"
    description="Update your account's profile details and contact email address."
>
    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-1">
            <label for="name" class="text-xs font-bold text-slate-700 uppercase tracking-wider">Full Name</label>
            <input id="name" name="name" type="text" class="theme-input w-full mt-1" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="space-y-1">
            <label for="email" class="text-xs font-bold text-slate-700 uppercase tracking-wider">Email Address</label>
            <input id="email" name="email" type="email" class="theme-input w-full mt-1" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-[12px] text-xs text-amber-800">
                    <p class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ __('Your email address is unverified.') }}</span>
                    </p>
                    <button form="send-verification" class="mt-2 text-xs font-bold underline text-amber-700 hover:text-amber-900 transition-colors">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-semibold text-emerald-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <x-slot name="actions">
        @if (session('status') === 'profile-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-xs font-bold text-emerald-600 flex items-center gap-1 mr-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ __('Saved successfully.') }}
            </p>
        @endif

        <x-action-button type="submit" variant="primary">
            Save Changes
        </x-action-button>
    </x-slot>
</x-form-card>

