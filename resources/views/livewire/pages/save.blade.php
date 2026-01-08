<?php

use App\Traits\Toast;
use App\Models\Page;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

new class extends Component {
    use Toast, WithFileUploads;

    public string|null $id = null;
    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public string $body = '';

    function mount(string|null $id = null)
    {
        $this->id = $id;

        if ($this->id) {
            $page = Page::find($this->id);
            if (!$page) {
                abort(404);
            }
            $this->title = $page->title;
            $this->slug = $page->slug;
            $this->description = $page->description;
            $this->body = $page->body;
        }
    }

    function save()
    {
        // Validate the input
        $this->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,' . $this->id,
            'description' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $page = $this->id ? Page::find($this->id) : new Page();
        $page->title = $this->title;
        $page->slug = $this->slug;
        $page->description = $this->description;
        $page->body = $this->body;

        $page->save();

        $this->success('Saved', 'Page saved successfully');

        if (!$this->id) {
            $this->redirect(route('admin.pages.edit', ['id' => $page->id]), navigate: true);
        }
    }

    function delete()
    {
        $page = Page::find($this->id);
        if ($page) {
            $page->delete();
            $this->success('Deleted');
        }
        $this->redirect(route('admin.pages.index'), navigate: true);
    }
};
?>
<div>
    <x-header :title="$id ? 'Pages / Edit' : 'Pages / Create'">
        <x-slot:actions>
            <x-button spinner class="btn-primary" wire:click='save'>Save</x-button>
            @if ($id)
                <x-button spinner class="btn-error" wire:click='delete' wire:confirm='Are you sure?'>Delete</x-button>
            @endif
        </x-slot:actions>
    </x-header>
    <x-form>
        <div class="grid grid-cols-1 lg:grid-cols-[auto_300px] gap-5">
            <div class="space-y-5">
                <x-card title="Page Details">
                    <div class="space-y-3">
                        <x-input label="Title" wire:model='title' />
                        <x-input label="Slug" wire:model='slug'
                            @input="$el.value = $el.value.trim().replace(/\s+/g, '-').toLowerCase(); $el.dispatchEvent(new Event(`input`))" />
                        <x-input label="Description" wire:model='description' />
                        <x-markdown label="Body" wire:model='body' />
                    </div>
                </x-card>
            </div>
        </div>
    </x-form>
</div>
