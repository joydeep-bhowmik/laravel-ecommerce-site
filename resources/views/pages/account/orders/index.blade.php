<?php

use App\Models\Order;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Laravel\Folio\name;
name('orders');
new class extends Component {
    function with()
    {
        $orders = Order::where('user_id', auth()->id())->paginate();

        return compact('orders');
    }
};
?>

<x-app-layout title="Orders">
    @volt('orders')
        <section>

            <x-profile.layout isactive="orders">
                <x-header title="Orders" />

                <div>
                    @if ($orders && $orders->count())
                        <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-5">
                            @foreach ($orders as $order)
                                <x-orders.cards.simple :$order />
                            @endforeach
                        </div>
                    @else
                        <center>Nothing here</center>
                    @endif
                </div>

                <div class="mt-5">
                    {{ $orders->links() }}
                </div>
            </x-profile.layout>

        </section>
    @endvolt
</x-app-layout>
