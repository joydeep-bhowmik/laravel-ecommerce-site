<?php

use App\Traits\Toast;
use App\Models\ShippingZone;
use Livewire\Volt\Component;

new class extends Component {
    use Toast;

    public string|null $id = null;
    public string $name = '';
    public string $country = '';
    public string|null $state = null;
    public string|null $city = null;
    public string|null $postal_code_range = null;
    public float $price_per_kg = 0.0;
    public float $tax_rate = 0.0;

    function mount(string|null $id = null)
    {
        $this->id = $id;

        if ($this->id) {
            $zone = ShippingZone::find($this->id);

            if (!$zone) {
                abort(404);
            }

            $this->name = $zone->name;
            $this->country = $zone->country;
            $this->state = $zone->state;
            $this->city = $zone->city;
            $this->postal_code_range = $zone->postal_code_range;
            $this->price_per_kg = $zone->price_per_kg;
            $this->tax_rate = $zone->tax_rate;
        }
    }

    function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code_range' => 'nullable|string|max:255',
            'price_per_kg' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        $zone = $this->id ? ShippingZone::find($this->id) : new ShippingZone();

        $zone->name = $this->name;
        $zone->country = $this->country;
        $zone->state = $this->state;
        $zone->city = $this->city;
        $zone->postal_code_range = $this->postal_code_range;
        $zone->price_per_kg = $this->price_per_kg;
        $zone->tax_rate = $this->tax_rate;
        $zone->save();

        $this->success('Saved', 'Shipping Zone saved successfully');

        if (!$this->id) {
            $this->redirect(route('admin.shipping.zones.edit', ['id' => $zone->id]), navigate: true);
        }
    }

    function delete()
    {
        ShippingZone::find($this->id)?->delete() && $this->success('Deleted');

        $this->redirect(route('admin.shipping.zones.index'), navigate: true);
    }
};
?>

<div>
    <x-header :title="$id ? 'Shipping Zones / Edit' : 'Shipping Zones / Create'">
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
                <x-card title="Shipping Zone Details">
                    <div class="space-y-3">
                        <x-input label="Zone Name" wire:model='name' />
                        <x-input label="Country" wire:model='country' />
                        <x-input label="State (Optional)" wire:model='state' />
                        <x-input label="City (Optional)" wire:model='city' />
                        <x-input label="Postal Code Range (Optional)" wire:model='postal_code_range' />
                        <x-input label="Price per KG" type="number" step="0.01" wire:model='price_per_kg' />
                        <x-input label="Tax Rate (%)" type="number" step="0.01" wire:model='tax_rate' />
                    </div>
                </x-card>
            </div>
        </div>
    </x-form>
</div>
