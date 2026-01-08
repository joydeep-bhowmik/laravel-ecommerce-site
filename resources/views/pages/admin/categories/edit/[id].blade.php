<?php
use function Laravel\Folio\name;
name('admin.categories.edit');

?>

<x-admin-layout title="categories / edit">

    <livewire:categories.save :id="request('id')" />
</x-admin-layout>
