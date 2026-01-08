@props(['isactive'])
@php
    $isactive = trim($isactive);
@endphp
<div>
    <div class="grid grid-cols-1 lg:grid-cols-[300px_auto] container mx-auto p-3">
        <div class="">

            <!-- Desktop Menu -->
            <div class="hidden lg:block">
                <x-menu class="lg:mt-24">
                    <x-menu-item title="Profile" link="{{ route('profile') }}" :active="$isactive == 'profile'" icon="o-user" />
                    <x-menu-item title="Orders" icon="o-cube" link="{{ route('orders') }}" :active="$isactive == 'orders'" />
                    <x-menu-item title="Carts" icon="o-shopping-bag" link="{{ route('carts') }}" :active="$isactive == 'carts'" />
                    <x-menu-item title="Addresses" link="{{ route('addresses') }}" :active="$isactive == 'addresses'" icon="o-map-pin" />
                </x-menu>
            </div>



        </div>
        <div class=" min-h-screen lg:py-16 py-6">
            {{ $slot }}
        </div>
    </div>
</div>
