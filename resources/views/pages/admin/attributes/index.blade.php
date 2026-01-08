<?php
use App\Models\Attribute;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Laravel\Folio\name;

name('admin.attributes.index');

new class extends Component {
    public $table;
    use WithPagination;

    function with()
    {
        $data = Attribute::paginate();

        $headers = [['key' => 'id', 'label' => '#', 'class' => 'w-16'], ['key' => 'name', 'label' => 'Name', 'class' => 'w-72'], ['key' => 'value', 'label' => 'value'], ['key' => 'action', 'label' => 'Action']];

        return compact('data', 'headers');
    }
};

?>
<x-admin-layout title="attributes / all">

    <x-header title="Attributes">
        <x-slot:actions>
            <x-button :link="route('admin.attributes.create')" icon="o-plus" />
        </x-slot:actions>
    </x-header>

    @volt('admin.attributes.index')
        <div>
            <x-card>
                @if ($data->count())
                    <x-table :headers="$headers" :rows="$data" with-pagination>
                        @scope('cell_action', $attribute)
                            <x-button :link="route('admin.attributes.edit', ['id' => $attribute->id])" class="btn-primary">Edit</x-button>
                        @endscope
                    </x-table>
                @else
                    <center>No data</center>
                @endif

            </x-card>

        </div>
    @endvolt
</x-admin-layout>
