<?php
use function Laravel\Folio\name;
name('admin.branding.banners.edit');

?>

<x-admin-layout title="banners / Edit">



    <livewire:banners.save :id="request('id')" />

</x-admin-layout>
