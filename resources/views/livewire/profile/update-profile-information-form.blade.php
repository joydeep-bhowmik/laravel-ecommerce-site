<?php

use App\Models\User;
use App\Traits\Toast;
use Livewire\Volt\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

new class extends Component {
    use Toast;
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->success(
            'Profile Updated', // Title
            'Your profile information has been updated successfully.', // Subtitle
        );

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        $this->info(
            'Verification Link Sent', // Title
            'A new verification link has been sent to your email address.', // Subtitle
        );
    }
}; ?>

<x-card title="Profile Information" subtitle="update your account's profile information and email address."
    class=" max-w-xl">


    <x-form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <!-- Name -->
        <x-input wire:model="name" label="Name" type="text" placeholder="Enter your name" class="w-full" required
            autofocus />


        <!-- Email -->

        <x-input wire:model="email" label="Email" type="email" placeholder="Enter your email" class="w-full"
            required />


        <!-- Email Verification -->
        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
            <div class="mt-2">
                <p class="text-sm text-gray-800 dark:text-gray-200">
                    {{ __('Your email address is unverified.') }}

                    <button wire:click.prevent="sendVerification"
                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>
            </div>
        @endif

        <x-slot:actions>
            <!-- Save Button -->
            <x-button type="submit" class=" btn-primary" spinner="updateProfileInformation">
                {{ __('Save') }}
            </x-button>
        </x-slot:actions>

    </x-form>
</x-card>
