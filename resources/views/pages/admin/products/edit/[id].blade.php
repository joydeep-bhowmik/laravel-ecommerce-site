<?php
use function Laravel\Folio\name;
name('admin.products.edit');

?>

<x-admin-layout title="Products / edit">
    <livewire:products.save :id="request('id')" />
</x-admin-layout>
