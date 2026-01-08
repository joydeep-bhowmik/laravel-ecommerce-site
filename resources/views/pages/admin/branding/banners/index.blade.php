<?php
use App\Models\Banner;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Laravel\Folio\name;

name('admin.branding.banners.index');

new class extends Component {
    public $table;
    use WithPagination;

    function with()
    {
        $data = Banner::paginate();
        $headers = [['key' => 'image', 'label' => 'Image', 'class' => 'w-32'], ['key' => 'name', 'label' => 'Name', 'class' => 'w-72'], ['key' => 'action', 'label' => 'Action']];

        return compact('data', 'headers');
    }
};
?>

<x-admin-layout title="Branding/  Banners / All">
    <x-header title="Banners">
        <x-slot:actions>
            <x-button :link="route('admin.branding.banners.create')" icon="o-plus" />
        </x-slot:actions>
    </x-header>

    @volt('admin.branding.banners.index')
        <div>
            <x-card>
                @if ($data->count())
                    <x-table :headers="$headers" :rows="$data" model="Banner" with-pagination>
                        @scope('cell_action', $banner)
                            <x-button :link="route('admin.branding.banners.edit', ['id' => $banner->id])" class="btn-primary">Edit</x-button>
                        @endscope
                        @scope('cell_image', $banner)
                            <img src="{{ $banner->getFirstMediaUrl('banners') }}" alt="Banner Image"
                                class="h-16 w-32 object-cover rounded-lg" />
                        @endscope
                    </x-table>
                @else
                    <center>No data</center>
                @endif
            </x-card>
        </div>
    @endvolt
</x-admin-layout>
