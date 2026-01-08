<?php

use App\Models\Cart;
use App\Traits\Toast;
use Livewire\Volt\Component;
use function Laravel\Folio\name;
use Illuminate\Support\Facades\Auth;

name('carts');

new class extends Component {
    use Toast;
    public $cartItems = [];

    public function mount()
    {
        $this->loadCart();
    }

    public function loadCart()
    {
        $this->cartItems = Cart::with('product')->where('user_id', Auth::id())->get();
    }

    public function updateQuantity($cartId, $quantity)
    {
        $cartItem = Cart::find($cartId);

        if (!$cartItem) {
            $this->error('error', 'Cart item not found.');
            return;
        }

        // Get product size stock availability
        $product = $cartItem->product;
        $sizes = collect($product->sizes);
        $size = $sizes->firstWhere('name', $cartItem->size_name);

        if (!$size) {
            $this->error('error', 'Size not found in product.');
            return;
        }

        if ($quantity > $size['quantity']) {
            $this->error('error', 'Requested quantity exceeds available stock.');
            return;
        }

        $cartItem->update(['quantity' => $quantity]);

        $this->loadCart(); // Refresh cart after update
    }

    public function removeItem($cartId)
    {
        $cartItem = Cart::find($cartId);

        if ($cartItem) {
            $cartItem->delete();
        }

        $this->loadCart(); // Refresh cart after removal
    }
};
?>
<x-app-layout title="Carts">
    @volt('carts')
        <section>
            <x-profile.layout isactive="carts">
                <div class="max-w-screen-xl ">
                    <x-header title="Carts" />

                    <div class="mt-6 sm:mt-8 md:gap-6 lg:flex lg:items-start xl:gap-8">
                        <!-- Cart Items -->
                        <div class=" w-full flex-none lg:max-w-2xl xl:max-w-4xl">
                            <div class="space-y-6">

                                @if (count($cartItems) > 0)
                                    @foreach ($cartItems as $cart)
                                        <x-card>
                                            <div class="space-y-4 grid grid-cols-[100px_auto] md:gap-6 md:space-y-0">
                                                <div>
                                                    <!-- Product Image -->
                                                    <a href="#" class="block mt-5">
                                                        <img src="{{ $cart['product']['firstImageUrl'] }}"
                                                            alt="{{ $cart['product']['name'] }}"
                                                            class="h-20 w-20 rounded-lg object-cover dark:hidden" />
                                                        <img src="{{ $cart['product']['firstImageUrl'] }}"
                                                            alt="{{ $cart['product']['name'] }}"
                                                            class="hidden h-20 w-20 rounded-lg object-cover dark:block" />
                                                    </a>
                                                </div>

                                                <div class=" space-y-3">
                                                    <!-- Product Details -->
                                                    <div class="w-full min-w-0 flex-1 space-y-4 md:order-2 md:max-w-md">
                                                        <a href="#"
                                                            class="text-base font-medium text-gray-900 hover:underline dark:text-white">
                                                            {{ $cart['product']['name'] }}
                                                        </a>
                                                        <p class="text-gray-600 dark:text-gray-400">Size:
                                                            {{ $cart['size_name'] }}
                                                        </p>
                                                        <p class="text-gray-600 dark:text-gray-400">Price:
                                                            ₹{{ $cart['price'] }}
                                                        </p>
                                                    </div>

                                                    <!-- Quantity Controls -->
                                                    <div
                                                        class="flex items-center justify-between md:order-3 md:justify-end">
                                                        <div class="flex items-center">
                                                            <button
                                                                wire:click="updateQuantity({{ $cart['id'] }}, {{ max(1, $cart['quantity'] - 1) }})"
                                                                class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                                                                <svg class="h-2.5 w-2.5 text-gray-900 dark:text-white"
                                                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                                    fill="none" viewBox="0 0 18 2">
                                                                    <path stroke="currentColor" stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M1 1h16" />
                                                                </svg>
                                                            </button>
                                                            <input type="text" value="{{ $cart['quantity'] }}"
                                                                class="w-10 shrink-0 border-0 bg-transparent text-center text-sm font-medium text-gray-900 focus:outline-none focus:ring-0 dark:text-white"
                                                                readonly />
                                                            <button
                                                                wire:click="updateQuantity({{ $cart['id'] }}, {{ $cart['quantity'] + 1 }})"
                                                                class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                                                                <svg class="h-2.5 w-2.5 text-gray-900 dark:text-white"
                                                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                                    fill="none" viewBox="0 0 18 18">
                                                                    <path stroke="currentColor" stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M9 1v16M1 9h16" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                        <div class="text-end md:order-4 md:w-32">
                                                            <p class="text-base font-bold text-gray-900 dark:text-white">
                                                                ₹{{ number_format($cart['price'] * $cart['quantity'], 2) }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- Remove Button -->
                                                    <div class="md:order-5">
                                                        <button wire:click="removeItem({{ $cart['id'] }})"
                                                            class="inline-flex items-center text-sm font-medium text-red-600 hover:underline dark:text-red-500">
                                                            <svg class="me-1.5 h-5 w-5" aria-hidden="true"
                                                                xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" fill="none" viewBox="0 0 24 24">
                                                                <path stroke="currentColor" stroke-linecap="round"
                                                                    stroke-linejoin="round" stroke-width="2"
                                                                    d="M6 18 17.94 6M18 18 6.06 6" />
                                                            </svg>
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </x-card>
                                    @endforeach
                                @else
                                    <div class="text-center py-10">
                                        <p class="text-gray-500 text-lg">Your cart is empty.</p>
                                        <x-button class=" btn-primary mt-10" :link="url('/')">
                                            Continue Shopping
                                        </x-button>
                                    </div>
                                @endif
                            </div>
                            @if (count($cartItems) > 0)
                                <x-button :link="route('checkout')" no-wire-navigate class=" btn-primary w-full mt-5"
                                    icon="o-lock-closed">₹{{ auth()->user()->cartTotal() }} Checkout</x-button>
                            @endif
                        </div>


                    </div>
                </div>


            </x-profile.layout>
        </section>
    @endvolt
</x-app-layout>
