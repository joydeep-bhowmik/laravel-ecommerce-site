<?php
use App\Models\Product;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Laravel\Folio\name;

name('admin.products.index');

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'latest'; // Default sorting

    function sort($type)
    {
        $this->sortBy = $type;
    }

    function with()
    {
        $query = Product::query();

        // Apply Search Filter (Name or Slug)
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")->orWhere('slug', 'like', "%{$this->search}%");
        }

        // Apply Sorting
        match ($this->sortBy) {
            'latest' => $query->latest(),
            'oldest' => $query->oldest(),
            'name-asc' => $query->orderBy('name', 'asc'),
            'name-desc' => $query->orderBy('name', 'desc'),
            default => $query->latest(),
        };

        $data = $query->paginate(10);
        $headers = [['key' => 'image', 'label' => 'Image', 'class' => 'w-32'], ['key' => 'name', 'label' => 'Name', 'class' => 'w-72'], ['key' => 'slug', 'label' => 'Slug'], ['key' => 'action', 'label' => 'Action']];

        return compact('data', 'headers');
    }
};
?>

<x-admin-layout title="Products / all">


    @volt('admin.products.index')
        <div>

            <x-header title="Products">
                <x-slot:middle class="!justify-end">

                    <div class="flex items-center gap-2">
                        <x-loading wire:loading />
                        <x-input wire:model.live.debounce.500ms="search" icon="o-magnifying-glass"
                            placeholder="Search by Name or Slug..." />
                    </div>
                </x-slot:middle>
                <x-slot:actions>
                    <x-dropdown>
                        <x-menu-item title="Latest" icon="o-clock" wire:click="sort('latest')" :active="$sortBy === 'latest'" />
                        <x-menu-item title="Oldest" icon="o-calendar" wire:click="sort('oldest')" :active="$sortBy === 'oldest'" />
                        <x-menu-item title="Name A-Z" icon="o-arrow-up" wire:click="sort('name-asc')" :active="$sortBy === 'name-asc'" />
                        <x-menu-item title="Name Z-A" icon="o-arrow-down" wire:click="sort('name-desc')"
                            :active="$sortBy === 'name-desc'" />
                        <x-menu-item title="Price: High to Low" icon="o-arrow-trending-down"
                            wire:click="sort('high-to-low')" :active="$sortBy === 'high-to-low'" />
                        <x-menu-item title="Price: Low to High" icon="o-arrow-trending-up" wire:click="sort('low-to-high')"
                            :active="$sortBy === 'low-to-high'" />
                    </x-dropdown>


                    <x-button :link="route('admin.products.create')" icon="o-plus" />
                </x-slot:actions>
            </x-header>
            <x-card>
                @if ($data->count())
                    <x-table :headers="$headers" :rows="$data" with-pagination>
                        @scope('cell_action', $product)
                            <x-button :link="route('admin.products.edit', ['id' => $product->id])" class="btn-primary">
                                Edit
                            </x-button>
                        @endscope
                        @scope('cell_image', $product)
                            <img src="{{ $product->firstImageUrl }}" alt="">
                        @endscope
                    </x-table>
                @else
                    <center>No data</center>
                @endif
            </x-card>
        </div>
    @endvolt
</x-admin-layout>
