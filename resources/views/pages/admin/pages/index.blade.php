<?php
use App\Models\Page;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Laravel\Folio\name;

name('admin.pages.index');

new class extends Component {
    public $table;
    use WithPagination;

    function with()
    {
        $data = Page::paginate();

        $headers = [['key' => 'id', 'label' => '#', 'class' => 'w-16'], ['key' => 'title', 'label' => 'Title', 'class' => 'w-72'], ['key' => 'slug', 'label' => 'Slug'], ['key' => 'description', 'label' => 'Description'], ['key' => 'action', 'label' => 'Action']];

        return compact('data', 'headers');
    }
};
?>
<x-admin-layout title="Pages / All">

    <x-header title="Pages">
        <x-slot:actions>
            <x-button :link="route('admin.pages.create')" icon="o-plus" />
        </x-slot:actions>
    </x-header>

    @volt('admin.pages.index')
        <div>
            <x-card>
                @if ($data->count())
                    <x-table :headers="$headers" :rows="$data" with-pagination>
                        @scope('cell_action', $page)
                            <x-button :link="route('admin.pages.edit', ['id' => $page->id])" class="btn-primary">Edit</x-button>
                        @endscope
                    </x-table>
                @else
                    <center>No data</center>
                @endif
            </x-card>
        </div>
    @endvolt
</x-admin-layout>
