@props(['order'])
<x-card class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="p-6 space-y-4">
        <!-- Order ID and Date -->
        <div class="flex justify-between items-start">
            <div class="text-lg font-semibold text-gray-700">
                <strong>Order ID:</strong> {{ $order->uid }}
            </div>
            <div class="text-sm text-gray-500">
                {{ $order->created_at->format('M d, Y h:i A') }}
            </div>
        </div>

        <!-- Items -->
        <div class="text-gray-600">
            <strong>Items:</strong>
            <div class="mt-2 truncate">
                @foreach ($order->items as $item)
                    <x-badge :value="$item['product']['name'] . ' x ' . $item['quantity']" />
                @endforeach
            </div>
        </div>

        <!-- Total Amount -->
        <div class="text-lg font-semibold text-gray-700">
            <strong>Total:</strong> {{ number_format($order->total_amount, 2) }}
        </div>

        <!-- View Order Button -->
        <x-button class="w-full" :link="route('orders.view', ['id' => $order->uid])" no-wire-navigate>
            View Order
        </x-button>
    </div>
</x-card>
