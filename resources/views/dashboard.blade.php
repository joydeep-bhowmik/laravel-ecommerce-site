<x-guest-layout title="Dashboard">
    <x-card :title="'Welcome Admin ' . auth()->user()->name" subtitle='Where would you like to go?'>

        <div class="grid grid-cols-2 gap-2">

            <x-button link='/' icon="o-globe-alt">Website</x-button>
            <x-button class="btn-primary" :link="route('admin.dashboard')" icon="o-chart-pie">Admin Panel</x-button>
        </div>
    </x-card>
</x-guest-layout>
