<div x-data="{
    show: false
}">
    <nav class=" container mx-auto px-3 grid  grid-cols-2 lg:grid-cols-3 gap-5">
        <x-application-logo class="object-contain p-5" />

        <div class="hidden lg:block"></div>

        <div class="flex">


            <div class="flex items-center gap-1  ml-auto">


                <div class=" hidden lg:block">
                    <livewire:products.search-suggestions />
                </div>






                <x-button icon="o-shopping-bag" :link="route('carts')" class="z-30 relative p-3" no-wire-navigate>
                    @auth
                        <x-badge :value="auth()->user()?->cartsCount()" class="bg-[#8A5324] text-white absolute -right-2 -top-2 z-30" />
                    @endauth
                </x-button>


                <x-dropdown class="z-50">
                    <x-slot:trigger>
                        <x-button icon="o-user" class="!size-6  " />
                    </x-slot:trigger>
                    @if (auth()->user()?->role == 'admin')
                        <x-menu-item title="Admin Panel" link="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')"
                            icon="o-user-circle" no-wire-navigate />
                    @endif
                    <x-menu-item :title="auth()->user() ? 'Profile' : 'Login'" link="{{ route('profile') }}" :active="request()->routeIs('profile')" icon="o-user"
                        no-wire-navigate />
                    <x-menu-item title="Orders" link="{{ route('orders') }}" :active="request()->routeIs('orders')" icon="o-cube"
                        no-wire-navigate />
                    <x-menu-item title="Carts" link="{{ route('carts') }}" :active="request()->routeIs('carts')" icon="o-shopping-bag"
                        no-wire-navigate />
                    <x-menu-item title="Addresses" link="{{ route('addresses') }}" :active="request()->routeIs('addresses')" icon="o-map-pin"
                        no-wire-navigate />
                    @auth
                        <x-menu-item title="Logout" link="{{ route('logout') }}" icon="o-arrow-left-end-on-rectangle"
                            onclick="return confirm('are you sure?')" no-wire-navigate />
                    @endauth
                </x-dropdown>
            </div>
        </div>
    </nav>
    <div class="block  lg:hidden px-3">
        <livewire:products.search-suggestions />
    </div>


</div>
