<x-form-card
    action="{{ route('password.update') }}"
    method="PUT"
    title="Update Password"
    description="Ensure your account is using a long, random password to stay secure."
>
    <div class="grid gap-6 md:grid-cols-3">
        <div class="space-y-1">
            <label for="update_password_current_password" class="text-xs font-bold text-slate-700 uppercase tracking-wider">Current Password</label>
            <x-password-input
                id="update_password_current_password"
                name="current_password"
                autocomplete="current-password"
                wrapper-class="mt-1"
                input-class="theme-input w-full"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="space-y-1">
            <label for="update_password_password" class="text-xs font-bold text-slate-700 uppercase tracking-wider">New Password</label>
            <x-password-input
                id="update_password_password"
                name="password"
                autocomplete="new-password"
                wrapper-class="mt-1"
                input-class="theme-input w-full"
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div class="space-y-1">
            <label for="update_password_password_confirmation" class="text-xs font-bold text-slate-700 uppercase tracking-wider">Confirm Password</label>
            <x-password-input
                id="update_password_password_confirmation"
                name="password_confirmation"
                autocomplete="new-password"
                wrapper-class="mt-1"
                input-class="theme-input w-full"
            />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>
    </div>

    <x-slot name="actions">
        @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-xs font-bold text-emerald-600 flex items-center gap-1 mr-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ __('Password updated successfully.') }}
            </p>
        @endif

        <x-action-button type="submit" variant="primary">
            Update Password
        </x-action-button>
    </x-slot>
</x-form-card>
