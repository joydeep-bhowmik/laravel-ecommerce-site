<?php

use App\Models\Page;
use Livewire\Volt\Component;
use function Laravel\Folio\name;

name('pages.view');

new class extends Component {
    function with()
    {
        $page = Page::where('slug', request('slug'))->first();
        if (!$page) {
            abort(404);
        }
        $parsedown = new Parsedown();

        return compact('page', 'parsedown');
    }
};
?>
<x-app-layout>

    @volt('pages.view')
        <div>
            <x-slot:title>{{ $page->name }}</x-slot:title>

            <div class=" container mx-auto">


                <div class="prose mt-10 mx-auto">
                    <x-header :title="$page->name" />

                    {!! $parsedown->text($page->body) !!}
                </div>
            </div>
        </div>
    @endvolt

</x-app-layout>
