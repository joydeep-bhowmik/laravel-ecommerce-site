<?php

use App\Traits\Toast;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

new class extends Component {
    use Toast;
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->success(
            'Password Updated', // Title
            'Your password has been updated successfully.', // Subtitle
        );
    }
}; ?>

<x-card class="max-w-xl" title="Update Password"
    subtitle="Ensure your account is using a long, random password to stay secure.">




    <x-form wire:submit="updatePassword" class="mt-6 space-y-6">
        <!-- Current Password -->
        <x-password right wire:model="current_password" label="Current Password" type="password"
            placeholder="Enter your current password" class="w-full" />

        <!-- New Password -->
        <x-password right wire:model="password" label="New Password" type="password"
            placeholder="Enter your new password" class="w-full" />

        <!-- Confirm Password -->
        <x-password right wire:model="password_confirmation" label="Confirm Password" type="password"
            placeholder="Confirm your new password" class="w-full" />

        <x-slot:actions>
            <!-- Save Button -->
            <x-button type="submit" class=" btn-primary" spinner="updatePassword">
                {{ __('Save') }}
            </x-button>
        </x-slot:actions>
    </x-form>
</x-card>
