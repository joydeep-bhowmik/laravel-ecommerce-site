<?php
use App\Models\ShippingZone;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Laravel\Folio\name;

name('admin.shipping.zones.index');

new class extends Component {
    use WithPagination;

    function with()
    {
        $data = ShippingZone::paginate();

        $headers = [['key' => 'name', 'label' => 'Zone Name', 'class' => 'w-72'], ['key' => 'country', 'label' => 'Country'], ['key' => 'state', 'label' => 'State'], ['key' => 'city', 'label' => 'City'], ['key' => 'postal_code_range', 'label' => 'Postal Code Range'], ['key' => 'price_per_kg', 'label' => 'Price per KG'], ['key' => 'action', 'label' => 'Action']];

        return compact('data', 'headers');
    }
};
?>

<x-admin-layout title="Shipping / Zones / All ">
    <x-header title="Shipping / Zones / All">
        <x-slot:actions>
            <x-button :link="route('admin.shipping.zones.create')" icon="o-plus" />
        </x-slot:actions>
    </x-header>

    @volt('admin.shipping.zones.index')
        <div>
            <x-card>
                @if ($data->count())
                    <x-table :headers="$headers" :rows="$data" with-pagination>
                        @scope('cell_action', $zone)
                            <x-button :link="route('admin.shipping.zones.edit', ['id' => $zone->id])" class="btn-primary">Edit</x-button>
                        @endscope
                    </x-table>
                @else
                    <center>No data</center>
                @endif
            </x-card>
        </div>
    @endvolt
</x-admin-layout>
