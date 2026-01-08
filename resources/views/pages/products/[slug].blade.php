<?php

use App\Traits\Toast;
use App\Models\Product;
use Livewire\Volt\Component;
use function Laravel\Folio\name;

name('product.view');

new class extends Component {
    use Toast;
    public string $id;
    public string $slug;
    public string|null $selectedSize = null;
    public string|null $price = '';
    public Product $product;

    function mount()
    {
        $slug = request('slug');
        $product = Product::where('slug', $this->slug)->first();

        if (!$product) {
            return abort(404);
        }
        $this->product = $product;
        $this->id = $product->id;
        $this->slug = $product->slug;
        $this->price = $product->base_price;
    }

    function selectSize(string $name)
    {
        $this->selectedSize = trim($name);

        $sizes = collect($this->product->sizes);

        $sizeDetails = $sizes->firstWhere('name', $this->selectedSize);

        $this->price = $sizeDetails['price'];
    }

    // function with()
    // {
    //     $product = Product::find($this->id);
    //     return compact('product');
    // }

    function addToCart()
    {
        if (!$this->selectedSize) {
            return;
        }

        $user = auth()->user();

        if (!$user) {
            return redirect()->to(route('login'));
        }

        $response = $user->addToCart(productId: $this->id, sizeName: $this->selectedSize, quantity: 1);

        if ($response['status']) {
            $this->success('Added to cart', $response['message']);
            $this->redirect(route('carts'));
        } else {
            $this->Error('Error occurred', $response['message']);
        }
    }
};
?>

<x-app-layout>

    @volt('view-product')
        <div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 p-3 lg:p-5 container mx-auto">
                <div x-data="{
                    open: false,
                    showHint: false,
                    currentImage: '',
                    zoomInstance: null,
                
                    openModal(src) {
                        this.currentImage = src;
                        this.open = true;
                        this.$nextTick(() => {
                            this.zoomInstance = Panzoom(this.$refs.zoomImage, {
                                maxScale: 5,
                                contain: 'outside'
                            });
                
                            this.$refs.zoomImage.parentElement.addEventListener('wheel', this.zoomInstance.zoomWithWheel);
                        });
                    },
                
                    closeModal() {
                        this.open = false;
                        if (this.zoomInstance) {
                            this.zoomInstance.destroy();
                            this.zoomInstance = null;
                        }
                    }
                }" x-init="$watch('open', value => {
                
                    if (open) {
                        this.showHint = true;
                    }
                
                    if (!value && zoomInstance) {
                        zoomInstance.destroy();
                        zoomInstance = null;
                    }
                })">



                    <swiper-container pagination="true" class="!z-0" navigation>
                        @foreach ($product->images as $image)
                            <swiper-slide>
                                <img src="{{ $image['url'] }}" alt="Product Image" class="cursor-zoom-in"
                                    @click="$dispatch('open-image', { src: '{{ $image['url'] }}' })">
                            </swiper-slide>
                        @endforeach
                    </swiper-container>

                    <div class=" grid place-items-center fixed inset-0 bg-[rgba(0,0,0,0.8)] z-50" x-show="open" x-transition
                        style="display: none" @open-image.window="openModal($event.detail.src)"
                        @keydown.escape.window="closeModal()">

                        <div class="relative max-w-full max-h-full overflow-hidden">

                            <div x-show="open" x-transition.opacity
                                class="absolute top-4 left-1/2 -translate-x-1/2 bg-black/70 text-white text-sm px-3 py-1 rounded-lg pointer-events-none z-50">
                                Pinch to zoom, scroll to zoom, drag to move
                            </div>

                            <button type="button" class="bg-white p-3 rounded-full fixed top-5 right-5 z-50">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>

                            </button>

                            <img x-ref="zoomImage" :src="currentImage" alt="" class="w-full cursor-zoom-out"
                                @click.outside="closeModal()">
                        </div>
                    </div>
                </div>

                <div class=" space-y-5 lg:p-5">


                    <x-card>

                        <x-header :title="$product->name" />
                        <h2 class=" text-3xl">
                            ₹ {{ $price }}
                        </h2>

                    </x-card>

                    <x-card title="Select Size">
                        @if ($product->sizes)
                            @foreach ($product->sizes as $size)
                                <x-button :disabled="$size['quantity'] < 1"
                                    class=" {{ trim($size['name']) == $selectedSize ? ' btn-primary' : '' }} "
                                    wire:click="selectSize(`{{ $size['name'] }}`)"
                                    spinner>{{ ucwords($size['name']) }}</x-button>
                            @endforeach
                        @else
                            <center>Currently Unavailable</center>
                        @endif

                    </x-card>

                    <x-button :disabled="!$selectedSize" class="btn-primary w-full mt-5" wire:click='addToCart' spinner>Add to
                        cart</x-button>

                    <x-card title="Description">
                        <div class="  mx-auto prose lg:prose-xl w-full mt-10">
                            {!! $product->getHTMLDescription() !!}
                        </div>
                    </x-card>
                </div>


            </div>


        </div>
    @endvolt
</x-app-layout>
