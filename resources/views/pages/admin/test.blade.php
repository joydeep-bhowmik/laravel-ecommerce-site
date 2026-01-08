<?php
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Mary\Traits\WithMediaSync;
use function Laravel\Folio\name;
use Illuminate\Support\Collection;

name('admin.test');

new class extends Component {
    // Add these Traits
    use WithFileUploads, WithMediaSync;

    // Temporary files
    #[Rule(['files.*' => 'image|max:1024'])]
    public array $files = [];

    // Library metadata (optional validation)
    #[Rule('required')]
    public Collection $library;

    // Editing this user
    public User $user;

    public function mount(): void
    {
        $this->user = auth()->user();
        // Load existing library metadata from your model
        $this->library = $this->user->library ?? collect([]);

        // Or ... an empty collection if this component creates a user
        // $this->library = new Collection()
    }

    public function save(): void
    {
        // Your stuff ...

        // Sync files and updates library metadata
        $this->syncMedia($this->user);

        // Or ... first create the user, if this component creates a user
        // $user = User::create([...]);
        // $this->syncMedia($user);
    }
};

?>
<x-admin-layout title="Test">


    @volt('test')
        <div>
            <x-image-library wire:model="files" wire:library="library" :preview="$library" label="Product images"
                hint="Max 100Kb" />
        </div>
    @endvolt

</x-admin-layout>
