<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use function Laravel\Folio\name;
use function Laravel\Folio\middleware;
middleware('guest');
name('password.request');

new class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink($this->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<x-guest-layout title="Forgot password">
    @volt('forgot-password')
        <div>
            <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <x-form wire:submit="sendPasswordResetLink">
                <!-- Email Address -->
                <x-input label="Email" wire:model='email' icon="o-envelope" />

                <div class="flex items-center justify-end mt-4">
                    <x-button type='submit' class="btn-primary" spinner='sendPasswordResetLink'>
                        {{ __('Email Password Reset Link') }}
                    </x-button>
                </div>
            </x-form>
        </div>
    @endvolt
</x-guest-layout>
