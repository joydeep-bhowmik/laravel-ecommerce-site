<?php
use function Laravel\Folio\name;
name('admin.attributes.edit');

?>

<x-admin-layout title="attributes / edit">

    <livewire:attributes.save :id="request('id')" />
</x-admin-layout>
