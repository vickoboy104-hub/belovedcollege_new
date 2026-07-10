<x-portal-layout>
    <x-slot name="header">
        <x-page-header
            title="My Profile & Settings"
            eyebrow="Account Settings"
            description="Manage your account profile information, update your secure password, and configure other personal account details."
        />
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto space-y-8">
        {{-- Section 1: Update Profile Details & Security settings side-by-side or stacked cleanly --}}
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
            <div class="space-y-8">
                @include('profile.partials.update-profile-information-form')

                @include('profile.partials.update-password-form')

                @unless ($user->hasAnyRole(\App\Enums\UserRole::Student))
                    @include('profile.partials.delete-user-form')
                @endunless
            </div>
        </div>
    </div>
</x-portal-layout>

