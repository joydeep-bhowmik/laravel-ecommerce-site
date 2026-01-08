<?php
use App\Models\Category;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Laravel\Folio\name;
name('admin.categories.index');

new class extends Component {
    public $table;
    use WithPagination;

    function with()
    {
        $data = Category::paginate();
        $headers = [['key' => 'image', 'label' => 'Image', 'class' => 'w-32'], ['key' => 'name', 'label' => 'Name', 'class' => 'w-72'], ['key' => 'parent_category_id', 'label' => 'Parent Category'], ['key' => 'action', 'label' => 'Action']];

        return compact('data', 'headers');
    }
};
?>

<x-admin-layout title="Categories / all">
    <x-header title="Categories">
        <x-slot:actions>
            <x-button :link="route('admin.categories.create')" icon="o-plus" />
        </x-slot:actions>
    </x-header>

    @volt('admin.categories.index')
        <div>
            <x-card>
                @if ($data->count())
                    <x-table :headers="$headers" :rows="$data" model="Category" with-pagination>
                        @scope('cell_action', $category)
                            <x-button :link="route('admin.categories.edit', ['id' => $category->id])" class="btn-primary">Edit</x-button>
                        @endscope
                        @scope('cell_image', $category)
                            <img src="{{ $category->thumbnailUrl }}" alt="">
                        @endscope
                    </x-table>
                @else
                    <center>No data</center>
                @endif
            </x-card>
        </div>
    @endvolt
</x-admin-layout>
