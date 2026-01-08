<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use function Laravel\Folio\{name};

name('password.confirm');
new class extends Component {
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (
            !Auth::guard('web')->validate([
                'email' => Auth::user()->email,
                'password' => $this->password,
            ])
        ) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<x-guest-layout title="Confirm password">

    @volt('confirm-password')
        <div>
            <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </div>

            <x-form wire:submit="confirmPassword">
                <!-- Password -->
                <x-password label="Password" wire:model="password" right icon="o-lock-closed" />

                <div class="flex justify-end mt-4">
                    <x-button>
                        {{ __('Confirm') }}
                    </x-button>
                </div>
            </x-form>
        </div>
    @endvolt
</x-guest-layout>
