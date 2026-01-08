<?php
use function Laravel\Folio\name;
name('admin.shipping.zones.edit');

?>

<x-admin-layout title="shipping / zones / edit">



    <livewire:shipping.zones.save :id="request('id')" />

</x-admin-layout>
