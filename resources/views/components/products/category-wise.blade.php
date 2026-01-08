@php
    use App\Models\Category;
    $categories = Category::whereNull('parent_category_id')->with('products')->get();
@endphp

@foreach ($categories as $index => $category)
    @php
        // Alternate between 3 different layout styles
        $layoutStyle = $index % 3;
    @endphp

    @if ($layoutStyle === 0)
        <!-- Layout Style 1: Full-width banner with products below -->
        <section
            class="mb-20 overflow-hidden bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300 border border-[#8A5324]/20">
            <!-- Banner with title overlay -->
            <div class="relative h-64 w-full leaf-pattern">
                @if ($category->banner_url)
                    <img src="{{ asset('storage/' . $category->banner_url) }}" alt="{{ $category->name }}"
                        class="w-full h-full object-cover transition-all duration-700 hover:scale-105">
                @else
                    <div class="w-full h-full bg-gradient-to-r from-[#8A5324] to-[#6B3D1E]"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6">
                    <h2 class="text-4xl font-bold text-white capitalize">{{ $category->name }}</h2>
                    <a href="/search?category={{ $category->id }}"
                        class="mt-2 inline-flex items-center text-white/90 hover:text-white transition-colors">
                        <span>Shop Collection</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Products grid -->
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @foreach ($category->products->take(4) as $product)
                        <x-products.cards.simple :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @elseif($layoutStyle === 1)
        <!-- Layout Style 2: Split layout with text on left, products on right -->
        <section
            class="mb-20 overflow-hidden bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300 border border-[#8A5324]/20">
            <div class="md:flex">
                <!-- Left side - Category info -->
                <div class="md:w-1/3 p-8 bg-gradient-to-br from-[#f8f4f0] to-white flex flex-col justify-center">
                    <h2 class="text-3xl font-bold text-[#8A5324] capitalize mb-4">{{ $category->name }}</h2>
                    <p class="text-gray-600 mb-6">Explore our premium collection of {{ $category->name }} products.</p>
                    <a href="/search?category={{ $category->id }}"
                        class="inline-flex items-center justify-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-[#8A5324] hover:bg-[#6B3D1E] transition-colors duration-300">
                        View all
                    </a>
                </div>

                <!-- Right side - Products grid -->
                <div class="md:w-2/3 p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @foreach ($category->products->take(4) as $product)
                            <x-products.cards.simple :product="$product" />
                        @endforeach
                    </div>

                    <center>
                        <a href="/search?category={{ $category->id }}"
                            class="mt-10 block underline text-opacity-50 font-semibold">
                            View all
                        </a>
                    </center>
                </div>
            </div>
        </section>
    @else
        <!-- Layout Style 3: Centered layout with featured product -->
        <section class="mb-20 text-center">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-[#8A5324] capitalize mb-2">{{ $category->name }}</h2>
                <div class="w-16 h-1 bg-gradient-to-r from-[#8A5324] to-[#6B3D1E] mx-auto rounded-full"></div>
            </div>

            @if ($category->banner_url)
                <div class="mb-8 mx-auto max-w-2xl rounded-xl overflow-hidden">
                    <img src="{{ asset('storage/' . $category->banner_url) }}" alt="{{ $category->name }}"
                        class="w-full h-64 object-cover transition-all duration-700 hover:scale-105">
                </div>
            @endif

            <!-- Featured product grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-6xl mx-auto mb-8">
                @foreach ($category->products->take(4) as $product)
                    <x-products.cards.simple :product="$product" />
                @endforeach
            </div>

            <a href="/search?category={{ $category->id }}"
                class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-[#8A5324] hover:bg-[#6B3D1E] transition-colors duration-300">
                Browse {{ $category->name }} Collection
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </a>
        </section>
    @endif
@endforeach
