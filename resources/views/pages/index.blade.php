<?php
use function Laravel\Folio\name;

name('home');
?>
<x-app-layout title="Home">

    <div class=" container mx-auto">
        <x-banners name="hero_banner" effect="slide" navigation="false" pagination="false" autoplay-delay="5000"
            autoplay-disable-on-interaction="false" space-between="30" loop="true" pagination />
    </div>

    <div class=" container mx-auto p-3">
        <x-collections />
    </div>

    <div class=" container mx-auto p-3 mt-5">
        <x-products.recomended />
    </div>


    <div class=" container mx-auto p-3 mt-5">
        <x-products.category-wise />
    </div>

    <div class=" container mx-auto mt-5">
        <x-banners name="footer_banner" />
    </div>

</x-app-layout>
