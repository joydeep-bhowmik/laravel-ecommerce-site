<?php

namespace App\Http\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Livewire\Volt\Component;
use Illuminate\Support\Collection;

new class extends Component {
    public string|null $search = '';
    public string|null $category = null;
    public array $selectedSizes = [];
    public array $selectedAttributes = [];
    public Collection $allAttributes;
    public bool $filterDrawer = false;
    public $maxPrice;
    public string $sortBy = 'latest';

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => null],
        'selectedAttributes' => ['except' => []],
        'maxPrice' => ['except' => null],
        'sortBy' => ['except' => ''], // Add sortBy to query string
    ];

    public function mount()
    {
        $this->search = request('search');
        // Get all attributes from products and group them by type
        $this->allAttributes = Attribute::select('name', 'value')
            ->get()
            ->groupBy('name') // Group by attribute type (name)
            ->map(function ($group) {
                return $group->pluck('value')->toArray(); // Extract values for each type
            });
    }

    function updated($property, $value)
    {
        // if ($property == 'maxPrice') {
        //     dd($value);
        // }
    }

    public function with()
    {
        $query = Product::query();

        // Search by name or description
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%');
        }

        // Filter by category
        if ($this->category) {
            $query->where('category_id', $this->category);
        }

        // Filter by selected attributes
        if (!empty($this->selectedAttributes)) {
            $query->where(function ($q) {
                foreach ($this->selectedAttributes as $attr) {
                    $arr = explode(':', $attr);

                    if (strtoupper(trim($arr[0])) == 'SIZE') {
                        $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(sizes, '$[*].name')) LIKE ?", ["%{$arr[1]}%"]);
                    } else {
                        $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(attributes, '$[*].value')) LIKE ?", ["%{$arr[1]}%"]);
                    }
                }
            });
        }

        // Filter by max price
        if ($this->maxPrice) {
            $query->where('base_price', '<=', $this->maxPrice);
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'latest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                // Default sorting (if any)
                break;
        }

        // Debugging: Uncomment to see the generated SQL query
        // dd($query->toSql());

        $categories = [];
        $noParentCategories = Category::where('parent_category_id', null)->get()->map(fn($category) => ['id' => $category->id, 'name' => $category->name])->toArray();

        $categories['No Parent'] = $noParentCategories;

        $rootCategories = Category::where('parent_category_id', null)->get();
        foreach ($rootCategories as $category) {
            $children = $category->children()->get()->map(fn($child) => ['id' => $child->id, 'name' => $child->name])->toArray();

            if (!empty($children)) {
                $categories[$category->name] = $children;
            }
        }

        $subcategories = Category::where('parent_category_id', $this->category)->get();

        return [
            'products' => $query->get(),
            'categories' => $categories,
            'subcategories' => $subcategories,
        ];
    }
};
?>

<div>
    <div>

        <x-drawer wire:model="filterDrawer" class="w-11/12 lg:w-1/3">

            <div class=" py-5">
                <x-header title="Filter By" size="text-xl font-normal" separator>

                    <x-slot:actions>

                        <x-button icon="o-x-mark" class=" btn-ghost" @click="$wire.filterDrawer = false">Close</x-button>
                    </x-slot:actions>
                </x-header>

                @persist('filter')
                    <div class="sapce-y-5 divide-y">

                        {{-- <div class="py-5">
                            <x-range wire:model.live="maxPrice" min="500" step="10" label="Price range" />
                            <x-badge :value="$maxPrice" />
                        </div> --}}

                        <div class="py-5">
                            <x-select-group label="Category" placeholder="Select " placeholder-value="0" :options="$categories"
                                wire:model.live.debounce.500ms='category' />

                        </div>
                        @foreach ($allAttributes as $type => $values)
                            <div>
                                <x-collapse class="border-0">
                                    <x-slot:heading class="border-0 !label-text !label">
                                        {{ ucfirst($type) }}
                                    </x-slot:heading>
                                    <x-slot:content>
                                        <div class="pr-5 -ml-2">
                                            @foreach ($values as $value)
                                                <x-checkbox :label="ucfirst($value)"
                                                    wire:model.live.debounce.500ms="selectedAttributes" :value="$type . ':' . $value" />
                                            @endforeach
                                        </div>
                                    </x-slot:content>

                                </x-collapse>
                            </div>
                        @endforeach

                    </div>
                @endpersist
            </div>

        </x-drawer>

        <div class="p-5 container mx-auto">
            {{-- <div class="flex space-x-4">
                <!-- Search Bar -->
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search products..."
                    class="w-full px-4 py-2 border rounded-lg shadow-sm ">


            </div> --}}



            <x-ui.loading-screen wire:loading />
            @if ($subcategories->count())
                <div class=" flex gap-5 overflow-x-auto my-5">
                    @foreach ($subcategories as $item)
                        <a href="{{ route('search') }}?category={{ $item->id }}"
                            class=" inline-block border-4 p-2 rounded-full even:bg-green-100 odd:bg-blue-100 { request('category') == $category ? 'odd:border-blue-500 even:border-green-400' : '' }}">
                            <x-avatar :image="$item->thumbnailUrl" :title="ucwords($item->name)" :link="route('search', ['category' => $item->id])" class="!w-10" />
                        </a>
                    @endforeach
                </div>
            @endif
            <!-- Product List -->

            <x-button icon="o-adjustments-horizontal" @click="$wire.filterDrawer = true">Filter By</x-button>
            <x-dropdown label="Sort by">
                <x-menu-item title="Price: Low to High" icon="o-arrow-up" wire:click="$set('sortBy', 'price_asc')"
                    :active="$sortBy === 'price_asc'" />
                <x-menu-item title="Price: High to Low" icon="o-arrow-down" wire:click="$set('sortBy', 'price_desc')"
                    :active="$sortBy === 'price_desc'" />
                <x-menu-item title="Name: A to Z" icon="o-arrow-up" wire:click="$set('sortBy', 'name_asc')"
                    :active="$sortBy === 'name_asc'" />
                <x-menu-item title="Name: Z to A" icon="o-arrow-down" wire:click="$set('sortBy', 'name_desc')"
                    :active="$sortBy === 'name_desc'" />
                <x-menu-item title="Latest" icon="o-clock" wire:click="$set('sortBy', 'latest')" :active="$sortBy === 'latest'" />
                <x-menu-item title="Oldest" icon="o-clock" wire:click="$set('sortBy', 'oldest')" :active="$sortBy === 'oldest'" />
            </x-dropdown>

            <x-products.grid>
                @forelse($products as $product)
                    <x-products.cards.simple :$product />
                @empty
                    <p class="text-center col-span-full mt-64">No products found.</p>
                @endforelse
            </x-products.grid>
        </div>
    </div>
</div>
