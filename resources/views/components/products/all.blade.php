<?php

use App\Models\Product;
$products = Product::all();
?>


<div>
    <x-header title="All Products" size="text-3xl font-normal">
        <x-slot:actions>
            <x-button class=" btn-ghost opacity-55 underline " :link="route('search')" no-wire-navigate>See All</x-button>

        </x-slot:actions>

    </x-header>


    @if ($products && $products->count())
        <x-products.grid>
            @foreach ($products as $product)
                <x-products.cards.simple :$product />
            @endforeach

        </x-products.grid>
    @else
        <center>
            <div class="p-5 ">No products added</div>
        </center>
    @endif

</div>
