<?php
use function Laravel\Folio\{name, middleware};

middleware('auth');
name('profile');
?>

<x-app-layout title="profile">


    <x-profile.layout isactive="profile">

        <x-header title="Profile" />
        <div class="max-w-7xl space-y-6">
            <x-card>
                <livewire:profile.update-profile-information-form />
            </x-card>

            <x-card>
                <livewire:profile.update-password-form />
            </x-card>


        </div>
    </x-profile.layout>
</x-app-layout>
