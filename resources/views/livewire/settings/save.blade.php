<?php

use App\Models\Setting;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public array $settings = [];
    public $newKey = ''; // For adding new settings
    public $newValue = '';
    public $fileUploads = []; // To store files

    function mount()
    {
        if (Schema::hasTable('settings')) {
            $this->settings = Setting::pluck('value', 'key')->toArray();
        }
    }

    function save()
    {
        foreach ($this->settings as $key => $value) {
            Setting::set($key, $value);
        }

        // Handle file uploads dynamically
        foreach ($this->fileUploads as $key => $file) {
            // Get the old file path from the database
            $oldFilePath = Setting::get($key);

            // Delete old file if exists
            if ($oldFilePath && Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
            }

            // Store the new file in "storage/app/public/settings/"
            $path = $file->store('settings', 'public');

            // Update the database with the new file path
            Setting::set($key, $path);
        }

        // Add a new setting dynamically
        if (!empty($this->newKey) && !empty($this->newValue)) {
            Setting::set($this->newKey, $this->newValue);
            $this->settings[$this->newKey] = $this->newValue;
            $this->newKey = '';
            $this->newValue = '';
        }

        session()->flash('success', 'Settings updated successfully!');
    }
};
?>

<div>
    <x-header title="Settings">
        <x-slot:actions>
            <x-button spinner class="btn-primary" wire:click='save'>Save</x-button>
        </x-slot:actions>
    </x-header>

    <x-form>
        <div class="space-y-5">
            @foreach ($settings as $key => $value)
                <x-input label="{{ ucfirst(str_replace('_', ' ', $key)) }}" wire:model="settings.{{ $key }}" />
            @endforeach

            <x-card title="Upload Files">
                <x-file label="Logo" wire:model="fileUploads.logo" />
                @if (Setting::get('logo'))
                    <img src="{{ asset('storage/' . Setting::get('logo')) }}" class="h-16 w-16 object-cover mt-2" />
                @endif

                <x-file label="Favicon" wire:model="fileUploads.favicon" />
                @if (Setting::get('favicon'))
                    <img src="{{ asset('storage/' . Setting::get('favicon')) }}" class="h-8 w-8 object-cover mt-2" />
                @endif
            </x-card>

            <x-card title="Add New Setting">
                <x-input label="Key" wire:model="newKey" placeholder="e.g., custom_setting" />
                <x-input label="Value" wire:model="newValue" placeholder="Enter value here" />
            </x-card>
        </div>
    </x-form>
</div>
