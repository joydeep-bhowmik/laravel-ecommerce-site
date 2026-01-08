<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use function Laravel\Folio\{name};
use function Laravel\Folio\middleware;
middleware('guest');
name('register');

new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<x-guest-layout title="Register">

    @volt('register')
        <div>
            <x-form wire:submit="register">
                <!-- Name -->
                <x-input label="Name" wire:model='name' icon="o-user" />

                <!-- Email -->
                <x-input label="Email" wire:model='email' icon="o-envelope" />

                <!-- Password -->
                <x-password label="Password" wire:model='password' right />

                <!-- Confirm Password -->
                <x-password label="Password confirmation" wire:model='password_confirmation' right />

                <div class="flex items-center justify-end mt-4">
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        href="{{ route('login') }}" wire:navigate>
                        {{ __('Already registered?') }}
                    </a>

                    <x-button class="ms-4 btn-primary" type="submit" spinner="register">
                        {{ __('Register') }}
                    </x-button>
                </div>
            </x-form>
        </div>
    @endvolt
</x-guest-layout>
