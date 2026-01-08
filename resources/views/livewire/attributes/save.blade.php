<?php

use App\Traits\Toast;
use App\Models\Attribute;
use Livewire\Volt\Component;

new class extends Component {
    use Toast;

    public string|null $id = null;
    public string $name = '';
    public string $value = '';

    function mount(string|null $id = null)
    {
        $this->id = $id;

        if ($this->id) {
            $attribute = Attribute::find($this->id);

            if (!$attribute) {
                abort(404);
            }

            $this->name = $attribute->name;
            $this->value = $attribute->value;
        }
    }

    function save()
    {
        $attribute = $this->id ? Attribute::find($this->id) : new Attribute();

        $attribute->name = $this->name;
        $attribute->value = $this->value;
        $attribute->save();

        $this->success('Saved', 'Attribue saved successfully');

        if (!$this->id) {
            $this->redirect(route('admin.attributes.edit', ['id' => $attribute->id]), navigate: true);
        }
    }

    function delete()
    {
        Attribute::find($this->id)?->delete() && $this->success('Deleted');

        $this->redirect(route('admin.attributes.index'), navigate: true);
    }
};
?>

<div>
    <x-header :title="$id ? 'Attributes / Edit' : 'Attributes / Create'">
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
                <x-card title="Attribute Details">
                    <div class="space-y-3">
                        <x-input label="Name" wire:model='name' />
                        <x-input label="Value" wire:model='value' />
                    </div>
                </x-card>
            </div>
        </div>
    </x-form>
</div>
