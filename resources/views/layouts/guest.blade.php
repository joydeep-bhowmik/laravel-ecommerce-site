@props(['title' => env('APP_NAME')])
<!DOCTYPE html>
<html data-theme="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans  antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="p-5">
            <a href="/" wire:navigate>
                <x-application-logo class=" h-20 fill-current " />
            </a>
        </div>

        <x-card class=" w-full max-w-md">{{ $slot }}</x-card>
        <x-ui.toast />
    </div>
</body>

</html>
