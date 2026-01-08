<?php
use App\Models\Coupon;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Laravel\Folio\name;

name('admin.coupons.index');

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'latest'; // Default sorting

    function sort($type)
    {
        $this->sortBy = $type;
    }

    function with()
    {
        $query = Coupon::query();

        // Apply Search Filter (Code or Description)
        if ($this->search) {
            $query->where('code', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
        }

        // Apply Sorting
        match ($this->sortBy) {
            'latest' => $query->latest(),
            'oldest' => $query->oldest(),
            'code-asc' => $query->orderBy('code', 'asc'),
            'code-desc' => $query->orderBy('code', 'desc'),
            'expiry-asc' => $query->orderBy('end_date', 'asc'),
            'expiry-desc' => $query->orderBy('end_date', 'desc'),
            default => $query->latest(),
        };

        $data = $query->paginate(10);
        $headers = [['key' => 'code', 'label' => 'Code', 'class' => 'w-32'], ['key' => 'description', 'label' => 'Description', 'class' => 'w-72'], ['key' => 'end_date', 'label' => 'Expiry Date'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action', 'label' => 'Action']];

        return compact('data', 'headers');
    }
};
?>

<x-admin-layout title="Coupons / all">

    @volt('admin.coupons.index')
        <div>
            <x-header title="Coupons">
                <x-slot:middle class="!justify-end">
                    <div class="flex items-center gap-2">
                        <x-loading wire:loading />
                        <x-input wire:model.live.debounce.500ms="search" icon="o-magnifying-glass"
                            placeholder="Search by Code or Description..." />
                    </div>
                </x-slot:middle>
                <x-slot:actions>
                    <x-dropdown>
                        <x-menu-item title="Latest" icon="o-clock" wire:click="sort('latest')" :active="$sortBy === 'latest'" />
                        <x-menu-item title="Oldest" icon="o-calendar" wire:click="sort('oldest')" :active="$sortBy === 'oldest'" />
                        <x-menu-item title="Code A-Z" icon="o-arrow-up" wire:click="sort('code-asc')" :active="$sortBy === 'code-asc'" />
                        <x-menu-item title="Code Z-A" icon="o-arrow-down" wire:click="sort('code-desc')"
                            :active="$sortBy === 'code-desc'" />
                        <x-menu-item title="Expiry Soonest" icon="o-arrow-trending-up" wire:click="sort('expiry-asc')"
                            :active="$sortBy === 'expiry-asc'" />
                        <x-menu-item title="Expiry Farthest" icon="o-arrow-trending-down" wire:click="sort('expiry-desc')"
                            :active="$sortBy === 'expiry-desc'" />
                    </x-dropdown>

                    <x-button :link="route('admin.coupons.create')" icon="o-plus" />
                </x-slot:actions>
            </x-header>
            <x-card>
                @if ($data->count())
                    <x-table :headers="$headers" :rows="$data" with-pagination>
                        @scope('cell_end_date', $coupon)
                            {{ $coupon->end_date?->format('M d, Y H:i') }}
                            @if ($coupon->end_date?->isPast())
                                <x-badge value="Expired" class="badge-error" />
                            @endif
                        @endscope

                        @scope('cell_status', $coupon)
                            @if ($coupon->end_date?->isPast())
                                <x-badge value="Expired" class="badge-error" />
                            @else
                                <x-badge value="Active" class="badge-success" />
                            @endif
                        @endscope

                        @scope('cell_action', $coupon)
                            <x-button :link="route('admin.coupons.edit', ['id' => $coupon->id])" class="btn-primary">
                                Edit
                            </x-button>
                        @endscope
                    </x-table>
                @else
                    <center>No coupons found</center>
                @endif
            </x-card>
        </div>
    @endvolt
</x-admin-layout>
