<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use function Laravel\Folio\{name};
use function Laravel\Folio\middleware;
middleware('guest');

name('login');

new class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<x-guest-layout title="Login">

    @volt('login')
        <div>


            <x-form wire:submit="login">
                <!-- Email Address -->
                <x-input label="Email" wire:model="form.email" icon="o-envelope" />

                <!-- Password -->
                <x-password label="Password" wire:model="form.password" right icon="o-lock-closed" />

                <!-- Remember Me -->
                <x-checkbox label="Remember Me" wire:model='form.remember' />

                <div class="flex items-center justify-end mt-4">
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </a>

                    <x-button class="ms-3 btn-primary" type="submit" spinner="login">
                        {{ __('Log in') }}
                    </x-button>
                </div>

                <center>Or </center>

                <x-button class="  w-full" :link="route('register')">
                    {{ __('Create account') }}
                </x-button>
            </x-form>
        </div>
    @endvolt
</x-guest-layout>
