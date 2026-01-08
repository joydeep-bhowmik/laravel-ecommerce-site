<?php

use App\Models\Product;

use Livewire\Volt\Component;

new class extends Component {
    public string $key = '';

    public function with()
    {
        // Return empty collection if key is null or empty
        if (empty($this->key)) {
            return ['products' => collect()]; // Return empty collection
        }

        $products = Product::where(function ($query) {
            $query->where('name', 'like', '%' . $this->key . '%');
        })->get();

        return ['products' => $products];
    }
};

?>
<div class="grid place-items-center lg:rounded-xl rounded-none relative" x-data
    @click.away="$refs.box.style.display='none'">
    <form action="{{ route('search') }}" class="w-full max-w-xl mx-auto relative">
        <div class="flex items-center rounded-lg p-3 bg-white w-full max-w-xl mx-auto">
            <x-icon name="o-magnifying-glass" @click="show = false" wire:loading.remove />

            <x-icon name="o-arrow-path" class=" animate-spin " wire:loading />
            <input type="text" placeholder="Search" name="search" wire:model.live.debounce.500ms='key'
                @click="$refs.box.style.display='block';"
                class="border-0 w-full px-3 lg:py-0 border-black outline-none ring-0">
        </div>
        <div class="absolute left-0 right-0 z-50 mt-1 bg-white shadow-lg rounded-lg max-h-96 overflow-y-auto"
            x-ref='box'>
            <!-- Search results container - now fixed position -->
            @if ($products->isNotEmpty())

                @foreach ($products as $product)
                    <a href="{{ route('product.view', ['slug' => $product->slug]) }}"
                        class="flex items-center p-3 hover:bg-gray-100 border-b border-gray-100 last:border-0">
                        <!-- Product image -->
                        @if ($product->firstImageUrl)
                            <img src="{{ $product->firstImageUrl }}" alt="{{ $product->name }}"
                                class="w-12 h-12 object-cover rounded mr-3">
                        @else
                            <div class="w-12 h-12 bg-gray-200 rounded mr-3 flex items-center justify-center">
                                <x-icon name="o-photo" class="text-gray-400" />
                            </div>
                        @endif

                        <!-- Product info -->
                        <div class="flex-1 min-w-0">
                            <div class="font-medium truncate">{{ $product->name }}</div>
                            @if ($product->price)
                                <div class="text-sm text-gray-600">
                                    ${{ number_format($product->price, 2) }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach

            @endif
        </div>
    </form>
</div>
