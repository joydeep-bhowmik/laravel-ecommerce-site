<?php
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Traits\Toast;
use Razorpay\Api\Api;
use App\Models\Address;
use App\Models\Product;
use App\Models\Setting;
use Livewire\Volt\Component;
use function Laravel\Folio\{name, middleware};
use Illuminate\Support\Facades\DB;
use App\Notifications\OrderReceived;

name('checkout');
middleware('auth');

new class extends Component {
    use Toast;
    public string|null $seletedAddress = null;
    public string|null $payment_method = null;
    public string|null $coupon_code = null;
    public array|null $applied_coupon = null;
    public float $discount_amount = 0;
    public array $available_coupons = [];

    function mount()
    {
        $this->seletedAddress = Address::where('user_id', auth()->id())
            ->where('is_default', 1)
            ->value('id');

        $this->getCouponSuggestions();
    }

    // Add this method
    public function getCouponSuggestions()
    {
        $this->available_coupons = Coupon::active()
            ->availableForUser(auth()->id(), auth()->user()->cartTotal())
            ->select('code', 'description', 'discount_type', 'discount_value', 'minimum_order_amount')
            ->get()
            ->toArray();
    }

    public function applyCouponSuggestion(string $code)
    {
        $this->coupon_code = $code;
        $this->applyCoupon();
    }

    public function applyCoupon()
    {
        $this->validate([
            'coupon_code' => 'required|string',
        ]);

        $coupon = Coupon::where('code', $this->coupon_code)->active()->first();

        if (!$coupon) {
            return $this->error('Invalid Coupon', 'The coupon code is invalid or expired.');
        }

        // Check minimum order amount
        $cartTotal = auth()->user()->cartTotal();
        if (!$coupon->isValidForCart($cartTotal)) {
            return $this->error('Coupon Requirement', "This coupon requires a minimum order of ₹{$coupon->minimum_order_amount}");
        }

        // Check if user already used this coupon
        if (
            $coupon->usage_limit > 0 &&
            $coupon
                ->users()
                ->where('user_id', auth()->id())
                ->exists()
        ) {
            return $this->error('Coupon Used', 'You have already used this coupon.');
        }

        $this->applied_coupon = [
            'code' => $coupon->code,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $coupon->discount_value,
            'description' => $coupon->description,
            'minimum_order_amount' => $coupon->minimum_order_amount,
        ];

        $this->calculateDiscount();
    }

    function removeCoupon()
    {
        $this->coupon_code = null;
        $this->applied_coupon = null;
        $this->discount_amount = 0;
    }

    protected function calculateDiscount()
    {
        if (!$this->applied_coupon) {
            $this->discount_amount = 0;
            return;
        }

        $subtotal = auth()->user()->cartTotal();

        if ($this->applied_coupon['discount_type'] === 'percentage') {
            $this->discount_amount = $subtotal * ($this->applied_coupon['discount_value'] / 100);
        } else {
            $this->discount_amount = min($subtotal, $this->applied_coupon['discount_value']);
        }
    }

    function createOrder()
    {
        $user = auth()->user();
        $admins = User::where('role', 'admin')->get();

        DB::beginTransaction();

        try {
            $data = $this->getSummary();
            $address = Address::find($this->seletedAddress);

            if (!$address) {
                throw new \Exception('Invalid Address');
            }

            // Final coupon validation
            if ($this->applied_coupon && $this->applied_coupon['minimum_order_amount'] > 0) {
                if ($data['subtotal'] < $this->applied_coupon['minimum_order_amount']) {
                    throw new \Exception('Cart total is below the minimum requirement for this coupon');
                }
            }

            foreach ($data['cartItems'] as $cartItem) {
                $product = Product::find($cartItem['product_id']);
                if (!$product) {
                    throw new \Exception('Product not available');
                }

                $size = collect($product->sizes)->firstWhere('name', trim($cartItem['size_name']));
                if (!$size || $size['quantity'] < $cartItem['quantity']) {
                    throw new \Exception("Product {$product->name} is out of stock or insufficient quantity.");
                }
            }

            if (!$this->payment_method) {
                throw new \Exception('Select A payment method');
            }

            $order = new Order();
            $order->uid = strtoupper(sprintf('ORD-%d-%s-%s', auth()->id(), date('dMY'), substr(uniqid(), -2)));
            $order->user_id = auth()->id();
            $order->address = $address;
            $order->items = $data['cartItems'];
            $order->status = 'onprocess';
            $order->payment_method = $this->payment_method;
            $order->payment_status = 'unpaid';
            $order->total_amount = $data['total'];
            $order->shipping_cost = $data['shipping']['shipping_cost'];
            $order->tax = $data['tax'];
            $order->discount_amount = $this->discount_amount;

            if ($this->applied_coupon) {
                $order->coupon_code = $this->applied_coupon['code'];
            }

            if ($order->payment_method == 'online') {
                $api = new Api(config('razorpay.key'), config('razorpay.secret'));
                $razorpayOrder = $api->order->create([
                    'receipt' => $order->uid,
                    'amount' => (int) $order->total_amount * 100,
                    'currency' => 'INR',
                    'payment_capture' => 1,
                    'notes' => ['username' => $user->name, 'user_id' => $user->id, 'email' => $user->email],
                ]);

                $order->r_order_id = $razorpayOrder->id;
                if (!$razorpayOrder) {
                    throw new \Exception('Failed to create Razorpay order');
                }
            }

            $order->save();

            if ($this->applied_coupon) {
                $coupon = Coupon::where('code', $this->applied_coupon['code'])->first();
                if ($coupon) {
                    $coupon->users()->attach(auth()->id(), ['redeemed_at' => now()]);
                    if ($coupon->usage_limit > 0) {
                        $coupon->decrement('usage_limit');
                    }
                }
            }

            $admins->each->notify(new OrderReceived($order));

            foreach ($data['cartItems'] as $cartItem) {
                $product = Product::find($cartItem['product_id']);
                $updatedSizes = collect($product->sizes)->map(function ($size) use ($cartItem) {
                    if (trim($size['name']) === trim($cartItem['size_name'])) {
                        $size['quantity'] -= $cartItem['quantity'];
                    }
                    return $size;
                });
                $product->sizes = $updatedSizes;
                $product->save();
            }

            Cart::where('user_id', auth()->id())->delete();
            DB::commit();

            $this->redirect(route('orders.view', ['id' => $order->uid]));
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Error', $e->getMessage());
        }
    }

    function getSummary()
    {
        $user = auth()->user()?->load('addresses');
        $subtotal = $user->cartTotal();

        // Validate coupon against current cart total
        if ($this->applied_coupon && $this->applied_coupon['minimum_order_amount'] > 0) {
            if ($subtotal < $this->applied_coupon['minimum_order_amount']) {
                $this->removeCoupon();
                $this->error('Coupon Removed', 'Your cart total is below the minimum requirement for this coupon');
            }
        }

        $this->calculateDiscount();
        $discountedSubtotal = $subtotal - $this->discount_amount;

        $shipping = $user->shippingCost($this->seletedAddress);
        $tax = $shipping['tax'] ?? 0;

        $cartItems = Cart::with('product')->where('user_id', Auth::id())->get();

        $allCodAvailable = $cartItems->every(function ($cartItem) {
            return optional($cartItem->product)->is_cod_available == true;
        });

        $baseTotal = $discountedSubtotal + $tax + $shipping['shipping_cost'];

        $standardCodRate = Setting::get('standard_cod_rate') ?? 0;
        $cod_charge = 0;

        if ($allCodAvailable && $this->payment_method === 'cod') {
            $calculatedCodCharge = $baseTotal * 0.015;
            $cod_charge = max($calculatedCodCharge, $standardCodRate);
        }

        $total = $baseTotal + $cod_charge;

        return compact('user', 'subtotal', 'tax', 'shipping', 'total', 'cartItems', 'allCodAvailable', 'cod_charge');
    }

    function with()
    {
        return $this->getSummary();
    }
};
?>
<x-app-layout title="Checkout">
    @volt('checkout')
        <div>
            <div class="mx-auto mt-6 max-w-4xl flex-1 space-y-6 lg:mt-0 lg:w-full p-3">
                <x-header title="Checkout" />
                <x-ui.loading-screen wire:loading />
                @if (count($cartItems) > 0)
                    <x-card title="Items">
                        <div class=" space-y-4 divide-y">
                            @foreach ($cartItems as $cart)
                                <div class="space-y-3 grid grid-cols-[100px_auto] md:gap-6 md:space-y-0">
                                    <div>
                                        <a href="#" class="block mt-5">
                                            <img src="{{ $cart->product?->firstImageUrl }}" alt="{{ $cart->product?->name }}"
                                                class="h-20 w-20 rounded-lg object-cover dark:hidden" />
                                            <img src="{{ $cart->product?->firstImageUrl }}"
                                                alt="{{ $cart->product?->name }}"
                                                class="hidden h-20 w-20 rounded-lg object-cover dark:block" />
                                        </a>
                                    </div>
                                    <div class=" space-y-1">
                                        <div class="w-full min-w-0 flex-1  md:order-2 md:max-w-md">
                                            <a href="#"
                                                class="text-base font-medium text-gray-900 hover:underline dark:text-white">
                                                {{ $cart->product?->name }}
                                            </a>
                                            <p class="text-gray-600 dark:text-gray-400">Size:
                                                {{ $cart->size_name }}
                                            </p>
                                            <p class="text-gray-600 dark:text-gray-400">Price:
                                                ₹{{ $cart->price }}
                                            </p>
                                            <p class="text-gray-600 dark:text-gray-400">Quantity:
                                                {{ $cart->quantity }}
                                            </p>
                                            @if ($cart->product?->is_cod_available)
                                                <x-badge value="COD" class="badge-neutral" />
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-card>

                    <div class=" space-y-4 mt-5">
                        <x-card class="p-6 " title="Address">
                            <div class="space-y-4 divide-y">
                                @forelse ($user->addresses as $address)
                                    <div class=" grid grid-cols-[30px_auto] gap-5">
                                        <div class=" p-2">
                                            <input type="radio" name="address" class="radio radio-primary"
                                                wire:model.live.debounce.500ms="seletedAddress"
                                                value="{{ $address->id }}" />
                                        </div>
                                        <div
                                            class="flex flex-col justify-between space-y-4 sm:flex-row sm:items-center sm:space-y-0">
                                            <div>
                                                <p class="text-lg font-medium text-gray-900 dark:text-white">
                                                    {{ $address['full_name'] }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $address['address_line_1'] }},
                                                    {{ $address['city'] }}, {{ $address['state'] }} -
                                                    {{ $address['postal_code'] }}
                                                </p>
                                                @if ($address['is_default'])
                                                    <x-badge value="Default"
                                                        class=" bg-green-100 text-xs font-medium text-green-800 dark:bg-green-200" />
                                                @endif
                                            </div>
                                            <div class="flex space-x-2">
                                                <x-button :link="route('addresses')" type="button" no-wire-navigate
                                                    class="bg-primary-100 text-primary-700 hover:bg-primary-200" spinner>
                                                    Edit
                                                </x-button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <x-button :link="route('addresses')" type="button" no-wire-navigate
                                        class="bg-primary-100 text-primary-700 hover:bg-primary-200" spinner>
                                        Create one
                                    </x-button>
                                @endforelse
                            </div>
                        </x-card>
                    </div>

                    <x-card title="Payment method">
                        @php
                            $payment_methods = [
                                ['id' => 'cod', 'name' => 'COD', 'disabled' => !($allCodAvailable && $seletedAddress)],
                                [
                                    'id' => 'online',
                                    'name' => 'Online',
                                    'disabled' => !$seletedAddress,
                                ],
                            ];
                        @endphp
                        <x-radio :options="$payment_methods" wire:model.live.debounce.500ms="payment_method" />
                    </x-card>

                    <x-card title="Apply Coupon">
                        <div class="flex gap-2">
                            <x-input wire:model.lazy="coupon_code" placeholder="Enter coupon code" />
                            @if (!$applied_coupon)
                                <x-button wire:click="applyCoupon" spinner>Apply</x-button>
                            @else
                                <x-button wire:click="removeCoupon" spinner>Remove</x-button>
                            @endif
                        </div>

                        @if ($applied_coupon)
                            <div class="mt-2 p-2 bg-green-50 rounded">
                                <p class="font-medium">Coupon Applied: {{ $applied_coupon['code'] }}</p>
                                <p class="text-sm">{{ $applied_coupon['description'] }}</p>
                                <p class="text-sm">
                                    Discount:
                                    @if ($applied_coupon['discount_type'] === 'percentage')
                                        {{ $applied_coupon['discount_value'] }}%
                                    @else
                                        ₹{{ number_format($applied_coupon['discount_value'], 2) }}
                                    @endif
                                </p>
                                @if ($applied_coupon['minimum_order_amount'] > 0)
                                    <p class="text-sm text-gray-600">
                                        Minimum order: ₹{{ number_format($applied_coupon['minimum_order_amount'], 2) }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <!-- Add this coupon suggestions section -->
                        @if (count($available_coupons) > 0 && !$applied_coupon)
                            <div class="mt-4 space-y-2">
                                <p class="text-sm font-medium text-gray-700">Available Coupons:</p>
                                <div class="space-y-2">
                                    @foreach ($available_coupons as $coupon)
                                        <div wire:click="applyCouponSuggestion('{{ $coupon['code'] }}')"
                                            class="cursor-pointer p-2 border rounded hover:bg-gray-50 transition-colors">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="font-medium">{{ $coupon['code'] }}</p>
                                                    <p class="text-sm text-gray-600">{{ $coupon['description'] }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="font-medium text-primary-600">
                                                        @if ($coupon['discount_type'] === 'percentage')
                                                            {{ $coupon['discount_value'] }}% OFF
                                                        @else
                                                            ₹{{ number_format($coupon['discount_value'], 2) }} OFF
                                                        @endif
                                                    </p>
                                                    @if ($coupon['minimum_order_amount'] > 0)
                                                        <p class="text-xs text-gray-500">
                                                            Min. order
                                                            ₹{{ number_format($coupon['minimum_order_amount'], 2) }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </x-card>

                    <x-card>
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">Order Summary</p>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Subtotal</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">
                                        ₹{{ number_format($subtotal, 2) }}</dd>
                                </dl>

                                @if ($discount_amount > 0)
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Discount</dt>
                                        <dd class="text-base font-medium text-green-600 dark:text-green-400">
                                            -₹{{ number_format($discount_amount, 2) }}</dd>
                                    </dl>
                                @endif

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Tax</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">
                                        ₹{{ number_format($tax, 2) }}</dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Shipping</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">
                                        @if ($shipping['shipping_cost'] !== null)
                                            ₹{{ $shipping['shipping_cost'] }}
                                        @else
                                            {{ $shipping['message'] }}
                                        @endif
                                    </dd>
                                </dl>
                                @if ($payment_method == 'cod')
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400">COD charges</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-white">
                                            ₹{{ $cod_charge }}</dd>
                                    </dl>
                                @endif
                            </div>

                            <dl
                                class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                                <dt class="text-base font-bold text-gray-900 dark:text-white">Total</dt>
                                <dd class="text-base font-bold text-gray-900 dark:text-white">
                                    ₹{{ number_format($total, 2) }}</dd>
                            </dl>
                        </div>

                        <div class="flex items-center justify-center gap-2 mt-4">
                            <x-button class="btn-primary" :disabled="$shipping['shipping_cost'] === null || !$payment_method" wire:click='createOrder' spinner>
                                Place order
                            </x-button>
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400"> or </span>
                            <x-button :link="url('/')" no-wire-navigate> Continue Shopping</x-button>
                        </div>
                    </x-card>
                @else
                    <div class="text-center py-10">
                        <p class="text-gray-500 text-lg">Your cart is empty.</p>
                        <x-button class="btn-primary mt-10" :link="url('/')">
                            Continue Shopping
                        </x-button>
                    </div>
                @endif
            </div>
        </div>
    @endvolt
</x-app-layout>
