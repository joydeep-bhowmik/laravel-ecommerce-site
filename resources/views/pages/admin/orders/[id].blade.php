<?php

use App\Models\Order;
use App\Traits\Toast;
use Razorpay\Api\Api;
use Livewire\Volt\Component;
use Livewire\WithPagination;

use Illuminate\Validation\Rule;
use function Laravel\Folio\name;
use App\Notifications\OrderStatusUpdated;
name('admin.orders.view');

new class extends Component {
    use Toast;
    public string|null $id = null;
    public string|null $payment_status = null;
    public string|null $status = null;
    public string|null $tracking_id = null;
    public string|null $tracking_link = null;
    public string|null $delivered_at = null;
    public string|null $notes = null;
    function mount()
    {
        $order = Order::where('uid', request('id'))->first();

        if (!$order) {
            return abort(404);
        }

        $this->id = $order->id;
        $this->payment_status = $order->payment_status;
        $this->status = $order->status;
        $this->tracking_id = $order->tracking_id;
        $this->tracking_link = $order->tracking_link;
        $this->notes = $order->notes;

        $this->id = $order->id;
    }

    function save()
    {
        // Validate Input
        $validated = $this->validate([
            'payment_status' => ['required', Rule::in(['unpaid', 'paid'])],
            'status' => ['required', Rule::in(['onprocess', 'delayed', 'shipped', 'cancelled', 'delivered'])],
            'tracking_id' => ['nullable', 'string'],
            'tracking_link' => ['nullable', 'url'], // Ensures valid URL'
            'notes' => ['nullable'],
        ]);

        $order = Order::find($this->id)->load('user');

        if (!$order) {
            session()->flash('error', 'Order not found.');
            return;
        }

        // Check if the status has changed
        $statusChanged = $order->status !== $validated['status'];

        // Update order fields with validated data
        $order->payment_status = $validated['payment_status'];
        $order->status = $validated['status'];
        $order->tracking_id = $validated['tracking_id'];
        $order->tracking_link = $validated['tracking_link'];
        $order->notes = $validated['notes'];

        // Set delivered_at timestamp if status is changed to delivered
        if ($validated['status'] === 'delivered' && !$order->delivered_at) {
            $order->delivered_at = Carbon::now();
        }

        $order->save();

        // Send notification if the status has changed
        if ($statusChanged) {
            $order->user->notify(new OrderStatusUpdated($order));
        }

        $this->success('Saved', 'Order Saved successfully');
    }
    function with()
    {
        $order = Order::find($this->id);
        return compact('order');
    }
};
?>

<x-admin-layout>
    @volt('admin.view.orders')
        <section>
            <x-slot:title> View Order - {{ $order->uid }}</x-slot:title>


            <x-header :title="'Order / ' . $order->uid">
                <x-slot:actions>

                </x-slot:actions>
            </x-header>

            <div class=" grid grid-cols-1 lg:grid-cols-[auto_300px] gap-5">

                <div class="space-y-5">

                    <x-card title="Payment">
                        <x-badge :value="$order->payment_method" />

                        <x-badge :value="$order->payment_status" />

                    </x-card>

                    <x-card title="Status">

                        <x-badge :value="$order->status" />
                    </x-card>

                    <x-card title="Items" subtitle="Items you have ordered">

                        <div class="grid lg:grid-cols-5 md:grid-cols-3 grid-cols-2 gap-5">
                            @foreach ($order->items as $item)
                                @php
                                    $product = (object) $item['product'];
                                @endphp
                                <x-products.cards.simple :$product />
                            @endforeach
                        </div>
                    </x-card>

                    <x-card title="Address" subtitle="Delivery Address">
                        @php
                            $address = $order->address;

                        @endphp
                        <div class="space-y-2">
                            <p><strong>Full Name:</strong> {{ $address['full_name'] }}</p>
                            <p><strong>Phone:</strong> {{ $address['phone'] }}</p>
                            @if ($address['email'])
                                <p><strong>Email:</strong> {{ $address['email'] }}</p>
                            @endif
                            <p><strong>Address:</strong> {{ $address['address_line_1'] }}</p>
                            @if ($address['address_line_1'])
                                <p><strong>Address Line 2:</strong> {{ $address['address_line_2'] }}</p>
                            @endif
                            <p><strong>City:</strong> {{ $address['city'] }}</p>
                            <p><strong>State:</strong> {{ $address['state'] }}</p>
                            <p><strong>Country:</strong> {{ $address['country'] }}</p>
                            <p><strong>Postal Code:</strong> {{ $address['postal_code'] }}</p>
                        </div>
                    </x-card>

                </div>

                <div class=" space-y-5">
                    <x-card>
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">Order Summary</p>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Subtotal</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">
                                        ₹{{ number_format($order->total_amount - $order->tshipping_cost - $order->tax, 2) }}
                                    </dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Tax</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">
                                        ₹{{ number_format($order->tax, 2) }}</dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Shipping</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">

                                        <p>{{ $order->shipping_cost }}</p>



                                    </dd>
                                </dl>
                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Payment method
                                    </dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">
                                        {{ $order->payment_method }}</dd>
                                </dl>
                            </div>

                            <dl
                                class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                                <dt class="text-base font-bold text-gray-900 dark:text-white">Total</dt>
                                <dd class="text-base font-bold text-gray-900 dark:text-white">
                                    ₹{{ number_format($order->total_amount, 2) }}</dd>
                            </dl>
                        </div>


                    </x-card>
                    <x-card title="Update Order">
                        <x-form wire:submit="save">


                            <div class="space-y-5">

                                @php
                                    $payment_status_options = [
                                        ['id' => 'unpaid', 'name' => 'Unpaid'],
                                        ['id' => 'paid', 'name' => 'Paid'],
                                    ];
                                @endphp

                                <x-select wire:model="payment_status" label="Payment Status" :options="$payment_status_options"
                                    placeholder="Select" placeholder-value="0" />
                                @php
                                    $order_status_options = [
                                        ['id' => 'onprocess', 'name' => 'On Process'],
                                        ['id' => 'delayed', 'name' => 'Delayed'],
                                        ['id' => 'shipped', 'name' => 'Shipped'],
                                        ['id' => 'cancelled', 'name' => 'Cancelled'],
                                        ['id' => 'delivered', 'name' => 'Delivered'],
                                    ];
                                @endphp
                                <x-select wire:model="status" label="Order Status"
                                    hint="Changing order status will send an email to user about the update"
                                    :options="$order_status_options" placeholder="Select" placeholder-value="0" />

                                <x-input type="text" wire:model="tracking_id" label="Tracking ID" />

                                <x-input type="text" wire:model="tracking_link" label="Tracking Link" />

                                <x-textarea wire:model='notes' label="Notes" />

                                <x-button type="submit" class=" btn-primary w-full">Update
                                    Order</x-button>
                            </div>
                        </x-form>
                    </x-card>

                </div>

            </div>


        </section>
    @endvolt
</x-admin-layout>
