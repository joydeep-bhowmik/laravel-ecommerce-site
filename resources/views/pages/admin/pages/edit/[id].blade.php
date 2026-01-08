<?php
use function Laravel\Folio\name;
name('admin.pages.edit');
?>

<x-admin-layout title="Pages / edit">
    <livewire:pages.save :id="request('id')" />
</x-admin-layout>
