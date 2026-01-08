<?php
use App\Models\User;
use App\Models\Order;
use Livewire\Volt\Component;
use function Laravel\Folio\{name};

name('admin.dashboard');

new class extends Component {
    public array $userChart = [];
    public array $orderChart = [];
    public array $revenueChart = [];
    public function mount()
    {
        // User Growth (Last 6 Months)
        $months = collect(range(5, 0))->map(fn($i) => now()->subMonths($i)->format('M'));
        $userData = $months->map(fn($month) => User::whereMonth('created_at', now()->subMonths($months->search($month))->month)->count());

        $this->userChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => 'New Users',
                        'data' => $userData,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    ],
                ],
            ],
        ];

        // Order Status Distribution (Pie Chart)
        $orderStatuses = ['onprocess', 'delayed', 'shipped', 'cancelled', 'delivered'];
        $orderData = collect($orderStatuses)->map(fn($status) => Order::where('status', $status)->count());

        $this->orderChart = [
            'type' => 'pie',
            'data' => [
                'labels' => $orderStatuses,
                'datasets' => [
                    [
                        'data' => $orderData,
                        'backgroundColor' => ['#f1c40f', '#e67e22', '#3498db', '#e74c3c', '#2ecc71'],
                    ],
                ],
            ],
        ];

        // Monthly Revenue (Last 6 Months)
        $revenueData = $months->map(
            fn($month) => Order::where('status', 'delivered')
                ->whereMonth('created_at', now()->subMonths($months->search($month))->month)
                ->sum('total_amount'),
        );

        $this->revenueChart = [
            'type' => 'line',
            'data' => [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => 'Revenue',
                        'data' => $revenueData,
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                        'fill' => false,
                    ],
                ],
            ],
        ];
    }

    function save() {}
};
?>
<x-admin-layout title="Dashboard">

    @volt('admin.dashboaard')
        <div>
            <x-header title="Dashboard" />

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
                <x-stat title="Total Users" value="{{ User::count() }}" icon="o-users" tooltip="Total registered users" />



                <x-stat title="New Users" description="This Month"
                    value="{{ User::whereMonth('created_at', now()->month)->count() }}"
                    tooltip-bottom="Newly registered users" icon="o-user-plus" />

                <x-stat title="Total Orders" icon="o-clipboard-document-list" value="{{ Order::count() }}"
                    tooltip="Total number of orders" />

                <x-stat title="Pending Orders" icon="o-clock" value="{{ Order::where('status', 'onprocess')->count() }}"
                    tooltip-left="Orders still in process" />

                <x-stat title="Completed Orders" icon="o-check-circle"
                    value="{{ Order::where('status', 'delivered')->count() }}"
                    tooltip-right="Orders successfully delivered" />

                <x-stat title="Cancelled Orders" icon="o-x-circle"
                    value="{{ Order::where('status', 'cancelled')->count() }}" class="text-red-500" color="text-red-700"
                    tooltip="Orders that were cancelled" />

                <x-stat title="Total Revenue" icon="o-currency-rupee"
                    value="{{ number_format(Order::where('status', 'delivered')->sum('total_amount'), 2) }}"
                    tooltip="Total revenue from completed orders" />

                <x-stat title="Avg Order Value" icon="o-calculator" description="delivered orders"
                    value="{{ number_format(Order::where('status', 'delivered')->avg('total_amount'), 2) }}"
                    tooltip="Average revenue per completed order" />

            </div>



            <div class="grid gap-5 mt-5">


                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-2">User Growth (Last 6 Months)</h3>
                        <x-chart wire:model="userChart" />
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-2">Order Status Distribution</h3>
                        <x-chart wire:model="orderChart" />
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-2">Revenue Growth</h3>
                        <x-chart wire:model="revenueChart" />
                    </div>
                </div>
            </div>


        </div>
    @endvolt
</x-admin-layout>
