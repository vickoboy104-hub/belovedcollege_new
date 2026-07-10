<x-dashboard-card
    title="Danger Zone: Delete Account"
    subtitle="Permanently delete your account and all associated school records."
    icon="trash"
    accent="red"
>
    <div class="space-y-4">
        <p class="text-xs font-semibold text-slate-500 max-w-2xl leading-relaxed">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>

        <div class="pt-2">
            <x-action-button
                type="button"
                variant="danger"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            >
                {{ __('Delete Account') }}
            </x-action-button>
        </div>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 space-y-6">
            @csrf
            @method('delete')

            <div>
                <h3 class="display-font text-lg font-bold text-slate-900 leading-snug">
                    {{ __('Are you sure you want to delete your account?') }}
                </h3>
                <p class="text-xs font-semibold text-slate-500 mt-2 leading-relaxed">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your account password to confirm that you would like to permanently delete your account.') }}
                </p>
            </div>

            <div class="space-y-1">
                <label for="password" class="text-xs font-bold text-slate-700 uppercase tracking-wider">Account Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="theme-input w-full mt-1 sm:w-3/4"
                    placeholder="{{ __('Enter your account password') }}"
                    required
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5 mt-6">
                <x-secondary-button x-on:click="$dispatch('close')" type="button">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-action-button type="submit" variant="danger">
                    {{ __('Confirm Delete Account') }}
                </x-action-button>
            </div>
        </form>
    </x-modal>
</x-dashboard-card>

