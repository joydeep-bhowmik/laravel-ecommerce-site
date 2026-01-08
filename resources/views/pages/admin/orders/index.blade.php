<?php
use App\Models\Order;
use Livewire\Volt\Component;
use Livewire\WithPagination;

use function Laravel\Folio\name;

name('admin.orders.index');

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'latest'; // Default sorting: latest orders

    function sort($type)
    {
        $this->sortBy = $type;
    }

    function with()
    {
        $query = Order::with('user');

        // Apply Search Filter (Order ID or User Email)
        if ($this->search) {
            $query->where('uid', 'like', "%{$this->search}%")->orWhereHas('user', function ($q) {
                $q->where('email', 'like', "%{$this->search}%");
            });
        }

        // Apply Sorting (Date or Price)
        match ($this->sortBy) {
            'latest' => $query->latest(),
            'oldest' => $query->oldest(),
            'high-to-low' => $query->orderBy('total_amount', 'desc'),
            'low-to-high' => $query->orderBy('total_amount', 'asc'),
            default => $query->latest(),
        };

        $orders = $query->paginate(10);
        $headers = [['key' => 'uid', 'label' => 'Order ID'], ['key' => 'user_id', 'label' => 'User'], ['key' => 'total_amount', 'label' => 'Total Amount'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action', 'label' => 'Action']];

        return compact('orders', 'headers');
    }
};
?>

<x-admin-layout title="Orders / all">
    @volt('admin.orders.index')
        <div>

            <x-header title="Orders" subtitle="Search & Filter Orders">
                <x-slot:middle class="!justify-end">
                    <div class="flex items-center gap-2">
                        <x-loading wire:loading />
                        <x-input wire:model.live.debounce.500ms="search" icon="o-magnifying-glass"
                            placeholder="Search by ID or Email..." />
                    </div>
                </x-slot:middle>
                <x-slot:actions>
                    <x-dropdown>
                        <x-menu-item title="Latest" icon="o-clock" wire:click="sort('latest')" :active="$sortBy === 'latest'" />

                        <x-menu-item title="Oldest" icon="o-calendar" wire:click="sort('oldest')" :active="$sortBy === 'oldest'" />

                        <x-menu-item title="Price: High to Low" icon="o-arrow-trending-down"
                            wire:click="sort('high-to-low') :active="$sortBy === 'high-to-low'" />

                        <x-menu-item title="Price: Low to High" icon="o-arrow-trending-up" wire:click="sort('low-to-high')"
                            :active="$sortBy === 'low-to-high'" />
                    </x-dropdown>
                </x-slot:actions>
            </x-header>
            <x-card>
                @if ($orders->count())
                    <x-table :headers="$headers" :rows="$orders" model="Order" with-pagination>
                        @scope('cell_user_id', $row)
                            @php
                                $user = $row->user;
                            @endphp
                            {{ $user->name }} , {{ $user->email }}
                        @endscope
                        @scope('cell_action', $row)
                            <x-button :link="route('admin.orders.view', ['id' => $row->uid])" class="btn-primary" icon="o-eye">
                                View
                            </x-button>
                        @endscope
                    </x-table>
                @else
                    <center>No orders found</center>
                @endif
            </x-card>
        </div>
    @endvolt
</x-admin-layout>
