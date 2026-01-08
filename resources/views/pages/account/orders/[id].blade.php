<?php

use App\Models\Order;
use Razorpay\Api\Api;
use App\Models\Setting;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Laravel\Folio\name;
use Illuminate\Support\Facades\Log;

name('orders.view');

new class extends Component {
    public function with()
    {
        $order = Order::where('uid', request('id'))
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            abort(404);
        }

        $r_order_id = null;

        // Razorpay payment status check (with null safety)
        if ($order->payment_method == 'online' && $order->payment_status == 'unpaid') {
            $api = new Api(config('razorpay.key'), config('razorpay.secret'));
            $r_order_id = $order->r_order_id;

            if ($r_order_id) {
                try {
                    $response = $api->order->fetch($r_order_id);
                    Log::info('Razorpay Response', ['order_id' => $r_order_id, 'response' => $response]);

                    if (isset($response['amount'], $response['amount_paid']) && $response['amount'] == $response['amount_paid']) {
                        $order->payment_status = 'paid';
                        $order->save();
                    }
                } catch (\Exception $e) {
                    Log::error('Razorpay API Error', ['error' => $e->getMessage()]);
                }
            }
        }

        return compact('order', 'r_order_id');
    }
};
?>

<x-app-layout>
    @volt('view.orders')
        <section>
            <x-slot:title> View Order - {{ $order->uid }}</x-slot:title>

            <x-profile.layout isactive="orders">
                <x-header :title="'Order / ' . $order->uid" />

                <div class="grid grid-cols-1 lg:grid-cols-[auto_300px] gap-5">
                    <!-- Left Column -->
                    <div class="space-y-5">
                        <!-- Payment Card -->
                        <x-card title="Payment">
                            <div class="flex gap-2">
                                <x-badge :value="$order->payment_method ?? 'N/A'" />
                                <x-badge :value="$order->payment_status ?? 'N/A'" />
                            </div>

                            @if ($order->payment_method == 'online' && $order->payment_status == 'unpaid')
                                <div class="mt-5">
                                    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                                    <x-button type="submit" id="rzp-button1" class="btn-primary">Pay</x-button>

                                    <script>
                                        var options = {
                                            "key": "{{ env('RAZORPAY_KEY') }}",
                                            "amount": "{{ ($order->total_amount ?? 0) * 100 }}",
                                            "currency": "INR",
                                            "name": "{{ env('APP_NAME') }}",
                                            "description": "Payment for {{ $order->uid }}",
                                            "image": "{{ Setting::get('logo') }}",
                                            "order_id": "{{ $order->r_order_id }}",
                                            "config": {
                                                "display": {
                                                    "blocks": {
                                                        "banks": {
                                                            "name": "All Payment Options",
                                                            "instruments": [{
                                                                    "method": "upi",
                                                                    "flows": ["collect"]
                                                                },
                                                                {
                                                                    "method": "card"
                                                                },
                                                                {
                                                                    "method": "wallet"
                                                                },
                                                                {
                                                                    "method": "netbanking"
                                                                }
                                                            ]
                                                        }
                                                    },
                                                    "sequence": ["block.banks"],
                                                    "preferences": {
                                                        "show_default_blocks": false
                                                    }
                                                }
                                            },
                                            "prefill": {
                                                "name": "{{ $order->address['full_name'] ?? '' }}",
                                                "email": "{{ $order->address['email'] ?? '' }}",
                                                "contact": "{{ $order->address['phone'] ?? '' }}"
                                            },
                                            "notes": {
                                                "address": "{{ env('APP_NAME') }}"
                                            },
                                            "theme": {
                                                "color": "#3399cc"
                                            }
                                        };

                                        var rzp1 = new Razorpay(options);

                                        rzp1.on('payment.failed', function(response) {
                                            console.log(response);
                                        });

                                        rzp1.on('payment.success', function(response) {
                                            window.location.reload();
                                        });

                                        document.getElementById('rzp-button1').onclick = function(e) {
                                            rzp1.open();
                                            e.preventDefault();
                                        }
                                    </script>
                                </div>
                            @endif
                        </x-card>

                        <!-- Status Card -->
                        <x-card title="Status">
                            <x-badge :value="$order->status ?? 'N/A'" />
                        </x-card>

                        <!-- Items Card -->
                        <x-card title="Items" subtitle="Items you have ordered">
                            <div class="grid lg:grid-cols-5 md:grid-cols-3 grid-cols-2 gap-5">
                                @foreach ($order->items as $item)
                                    @php
                                        $product = is_array($item['product'] ?? null)
                                            ? (object) $item['product']
                                            : null;
                                    @endphp
                                    @if ($product)
                                        <x-products.cards.simple :product="$product" :cta="false" />
                                    @endif
                                @endforeach
                            </div>
                        </x-card>

                        <!-- Tracking Card (Conditional) -->
                        @if ($order->tracking_id || $order->tracking_link)
                            <x-card title="Tracking">
                                <div class="space-y-5">
                                    @if ($order->tracking_id)
                                        <x-button :link="$order->tracking_id">{{ $order->tracking_id }}</x-button>
                                    @endif
                                    @if ($order->tracking_link)
                                        <x-button :link="$order->tracking_link">Track</x-button>
                                    @endif
                                </div>
                            </x-card>
                        @endif

                        <!-- Address Card -->
                        <x-card title="Address" subtitle="Delivery Address">
                            @php
                                $address = $order->address ?? [];
                            @endphp
                            <div class="space-y-2">
                                <p><strong>Full Name:</strong> {{ $address['full_name'] ?? 'N/A' }}</p>
                                <p><strong>Phone:</strong> {{ $address['phone'] ?? 'N/A' }}</p>
                                @isset($address['email'])
                                    <p><strong>Email:</strong> {{ $address['email'] }}</p>
                                @endisset
                                <p><strong>Address:</strong> {{ $address['address_line_1'] ?? 'N/A' }}</p>
                                @isset($address['address_line_2'])
                                    <p><strong>Address Line 2:</strong> {{ $address['address_line_2'] }}</p>
                                @endisset
                                <p><strong>City:</strong> {{ $address['city'] ?? 'N/A' }}</p>
                                <p><strong>State:</strong> {{ $address['state'] ?? 'N/A' }}</p>
                                <p><strong>Country:</strong> {{ $address['country'] ?? 'N/A' }}</p>
                                <p><strong>Postal Code:</strong> {{ $address['postal_code'] ?? 'N/A' }}</p>
                            </div>
                        </x-card>
                    </div>

                    <!-- Right Column (Order Summary) -->
                    <div class="space-y-5">
                        <x-card>
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">Order Summary</p>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Subtotal</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-white">
                                            ₹{{ number_format(($order->total_amount ?? 0) - ($order->shipping_cost ?? 0) - ($order->tax ?? 0), 2) }}
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Tax</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-white">
                                            ₹{{ number_format($order->tax ?? 0, 2) }}
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Shipping</dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-white">
                                            ₹{{ number_format($order->shipping_cost ?? 0, 2) }}
                                        </dd>
                                    </dl>
                                    <dl class="flex items-center justify-between gap-4">
                                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Payment Method
                                        </dt>
                                        <dd class="text-base font-medium text-gray-900 dark:text-white">
                                            {{ $order->payment_method ?? 'N/A' }}
                                        </dd>
                                    </dl>
                                </div>
                                <dl
                                    class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                                    <dt class="text-base font-bold text-gray-900 dark:text-white">Total</dt>
                                    <dd class="text-base font-bold text-gray-900 dark:text-white">
                                        ₹{{ number_format($order->total_amount ?? 0, 2) }}
                                    </dd>
                                </dl>
                            </div>
                        </x-card>
                    </div>
                </div>
            </x-profile.layout>
        </section>
    @endvolt
</x-app-layout>
