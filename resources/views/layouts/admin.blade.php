@props(['title' => env('APP_NAME')])

<!DOCTYPE html>
<html lang="en" data-theme="light" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ ucwords($title) }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />

    {{-- Sortable.js --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.1/Sortable.min.js"></script>

    {{--  Currency  --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js">
    </script>


    <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
    <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

</head>

<body>

    <body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">

        {{-- NAVBAR mobile only --}}
        <x-nav sticky class="lg:hidden">
            <x-slot:brand>
                <div class="ml-5 pt-5">
                    <x-application-logo class="h-10" />
                </div>
            </x-slot:brand>
            <x-slot:actions>
                <label for="main-drawer" class="lg:hidden mr-3">
                    <x-icon name="o-bars-3" class="cursor-pointer" />
                </label>
            </x-slot:actions>
        </x-nav>

        {{-- MAIN --}}
        <x-main full-width>
            {{-- SIDEBAR --}}
            <x-slot:sidebar drawer="main-drawer" class="bg-base-100 lg:bg-inherit">

                {{-- BRAND --}}
                <div class="ml-5 pt-5"><x-application-logo class="h-10" /></div>

                {{-- MENU --}}
                <x-menu>

                    {{-- User --}}
                    @if ($user = auth()->user())
                        <x-menu-separator />

                        <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover
                            class="-mx-2 !-my-2 rounded">
                            <x-slot:actions>
                                <x-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff"
                                    no-wire-navigate link="/logout" />
                            </x-slot:actions>
                        </x-list-item>

                        <x-menu-separator />
                    @endif


                    <x-menu-item title="Hello" icon="o-sparkles" link="/" />

                    <x-menu-item title="Dashboard" icon="o-chart-pie" :link="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" />
                    <x-menu-sub title="Branding" icon="o-star">

                        <x-menu-item title="Settings" :link="route('admin.branding.settings')" :active="request()->routeIs('admin.branding.settings')" />

                        <x-menu-sub title="Banners">
                            <x-menu-item title="Create" :link="route('admin.branding.banners.create')" :active="request()->routeIs('admin.branding.banners.create')" />
                            <x-menu-item title="All" :link="route('admin.branding.banners.index')" :active="request()->routeIs('admin.branding.banners.index')" />


                        </x-menu-sub>

                    </x-menu-sub>

                    <x-menu-sub title="Products" icon="o-cube">
                        <x-menu-item title="Create" :link="route('admin.products.create')" :active="request()->routeIs('admin.products.create')" />
                        <x-menu-item title="All" :link="route('admin.products.index')" />

                    </x-menu-sub>

                    <x-menu-item title="Orders" :link="route('admin.orders.index')" icon="o-shopping-bag" />

                    <x-menu-sub title="Attributes" icon="o-list-bullet">
                        <x-menu-item title="Create" :link="route('admin.attributes.create')" :active="request()->routeIs('admin.attributes.create')" />
                        <x-menu-item title="All" :link="route('admin.attributes.index')" :active="request()->routeIs('admin.attributes.index')" />
                    </x-menu-sub>


                    <x-menu-sub title="Categories" icon="o-folder">
                        <x-menu-item title="Create" :link="route('admin.categories.create')" :active="request()->routeIs('admin.categories.create')" />
                        <x-menu-item title="All" :link="route('admin.categories.index')" :active="request()->routeIs('admin.categories.index')" />
                    </x-menu-sub>


                    <x-menu-sub title="Coupons" icon="o-tag">
                        <x-menu-item title="Create" :link="route('admin.coupons.create')" :active="request()->routeIs('admin.coupons.create')" />
                        <x-menu-item title="All" :link="route('admin.coupons.index')" :active="request()->routeIs('admin.coupons.index')" />
                    </x-menu-sub>

                    <x-menu-sub title="Shipping" icon="o-truck">
                        <x-menu-sub title="Zones">
                            <x-menu-item title="Create" :link="route('admin.shipping.zones.create')" :active="request()->routeIs('admin.shipping.zones.create')" />
                            <x-menu-item title="All" :link="route('admin.shipping.zones.index')" :active="request()->routeIs('admin.shipping.zones.index')" />
                        </x-menu-sub>

                    </x-menu-sub>

                    <x-menu-sub title="Users" icon="o-user">

                        <x-menu-item title="All" :link="route('admin.users.index')" :active="request()->routeIs('admin.users.index')" />
                    </x-menu-sub>


                    <x-menu-item title="Artisan" :link="route('admin.artisan')" :active="request()->routeIs('admin.artisan')" icon="o-command-line" />


                    <x-menu-sub title="Pages" icon="o-newspaper">
                        <x-menu-item title="Create" :link="route('admin.pages.create')" :active="request()->routeIs('admin.pages.create')" />
                        <x-menu-item title="All" :link="route('admin.pages.index')" :active="request()->routeIs('admin.pages.index')" />
                    </x-menu-sub>


                </x-menu>
            </x-slot:sidebar>

            {{-- The `$slot` goes here --}}
            <x-slot:content>
                <div class=" container">
                    {{ $slot }}
                </div>
            </x-slot:content>
        </x-main>

        {{-- Toast --}}

        <x-ui.toast />





    </body>
    @livewireScripts


</html>
