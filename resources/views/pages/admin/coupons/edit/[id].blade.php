<?php
use function Laravel\Folio\name;
name('admin.coupons.edit');
?>

<x-admin-layout title="Coupons / Edit">
    <livewire:coupons.save :id="request('id')" />
</x-admin-layout>
