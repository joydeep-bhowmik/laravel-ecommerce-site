<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body>

    <button x-data @click="$dispatch('toast',{title:'Post saved',subtitle:`Post saved sucessfully`})">default</button>

    <button x-data
        @click="$dispatch('toast',{title:'Post edited',subtitle:`Post edited sucessfully`,variant:`success`})">success</button>



    <button x-data
        @click="$dispatch('toast',{title:'Post edited',subtitle:`Post edited sucessfully`,variant:`error`})">error</button>


    <button x-data
        @click="$dispatch('toast',{title:'Post edited',subtitle:`Post edited sucessfully`,variant:`warning`})">warning</button>


    <button x-data
        @click="$dispatch('toast',{title:`After you have installed PHP, Composer, and the Laravel installer, you're ready to`,subtitle:`After you have installed PHP, Composer, and the Laravel installer, you're ready to`,variant:`info`})">info</button>


    <x-ctoast />
</body>

@livewireScripts

</html>
