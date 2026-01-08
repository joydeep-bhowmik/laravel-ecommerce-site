@php
    // Safely get sizes with fallback to empty array
    $sizes = $product->sizes ?? [];
    $inStockSizes = collect($sizes)
        ->filter(function ($size) {
            return ($size['quantity'] ?? 0) > 0;
        })
        ->values();

    // Set safe defaults for all product properties
    $product->firstImageUrl = $product->firstImageUrl ?? '';
    $product->slug = $product->slug ?? '#';
    $product->name = $product->name ?? 'Unknown Product';
    $product->base_price = $product->base_price ?? 0;
    $product->original_price = $product->original_price ?? $product->base_price;
    $product->id = $product->id ?? null;
@endphp

@props(['product', 'cta' => true])

<div class="max-w-xs group cursor-pointer">
    <div
        class="relative h-64 bg-[#F3D6B9] overflow-hidden rounded-xl grid place-items-center transition-all duration-300 group-hover:shadow-lg group-hover:ring-2 group-hover:ring-[#8A5324]/10">
        <!-- Product Image -->
        @if ($product->firstImageUrl)
            <img src="{{ $product->firstImageUrl }}" alt="{{ $product->name }}"
                class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
                loading="lazy">
        @else
            <div class="text-gray-500">No Image</div>
        @endif

        <!-- Quick View Button -->
        <div
            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-black/10">
            <a href="{{ route('product.view', ['slug' => $product->slug]) }}"
                class="bg-white px-4 py-2 rounded-full font-medium text-[#8A5324] shadow-md hover:bg-[#8A5324] hover:text-white transition-colors">
                Quick View
            </a>
        </div>
    </div>

    <a href="{{ route('product.view', ['slug' => $product->slug]) }}" class="mt-4 block space-y-1">
        <!-- Product Name -->
        <h3 class="text-lg font-medium text-gray-900 truncate group-hover:text-[#8A5324] transition-colors">
            {{ $product->name }}
        </h3>

        <!-- Price -->
        <div class="flex items-center gap-2">
            <p class="text-2xl font-bold text-[#8A5324]">₹{{ number_format($product->base_price, 2) }}</p>
            @if ($product->original_price > $product->base_price)
                <p class="text-sm text-gray-500 line-through">₹{{ number_format($product->original_price, 2) }}</p>
            @endif
        </div>
    </a>

    @if ($cta)


        <!-- Add to Cart or View Options -->
        @if ($inStockSizes->count() === 1)
            @php
                $size = $inStockSizes->first();
            @endphp
            <form method="POST" action="{{ route('cart.add') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="size_name" value="{{ $size['name'] ?? '' }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit"
                    class="mt-3 w-full bg-[#8A5324] text-white py-2 rounded-lg font-medium hover:bg-[#6a3f1d] transition-colors duration-300">
                    Add to Cart
                </button>
            </form>
        @else
            <a href="{{ route('product.view', ['slug' => $product->slug]) }}"
                class="mt-3 block text-center w-full bg-[#8A5324] text-white py-2 rounded-lg font-medium hover:bg-[#6a3f1d] transition-colors duration-300">
                {{ $inStockSizes->count() > 1 ? 'View Options' : 'Out of Stock' }}
            </a>
        @endif
    @endif
</div>
