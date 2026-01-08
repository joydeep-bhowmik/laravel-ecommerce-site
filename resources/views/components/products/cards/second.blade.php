@props(['product'])
<div class="bg-white border border-[#8A5324]/20 rounded-xl shadow hover:shadow-lg transition duration-300">
    @if ($product->firstImageUrl)
        <img src="{{ $product->firstImageUrl }}" alt="{{ $product->name }}" class="w-full h-48 object-cover rounded-t-xl">
    @else
        <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500 rounded-t-xl">
            No Image
        </div>
    @endif

    <div class="p-4">
        <h3 class="text-lg font-semibold text-[#8A5324] mb-1">{{ $product->name }}</h3>
        <p class="text-[#8A5324] font-bold text-xl">${{ number_format($product->price, 2) }}</p>
    </div>
</div>
