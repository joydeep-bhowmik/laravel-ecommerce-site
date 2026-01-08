<?php

use App\Models\Category;
$categories = Category::all();
?>
<div class=" container mx-auto p-3">
    <x-header title="Collections" size="text-3xl font-normal">

        <x-slot:actions>
            <x-button class=" btn-ghost opacity-55 underline " :link="route('search')" no-wire-navigate>See All</x-button>

        </x-slot:actions>
    </x-header>

    <div class=" flex gap-5 overflow-x-auto -mt-5">
        <a href="{{ route('search') }}" class="block text-center">
            <center>
                <div class="btn-circle grid place-items-center size-20 overflow-hidden rounded-full bg-[#FFEBD8] p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class=" size-10">
                        <path
                            d="M6.75 2.5C9.09721 2.5 11 4.40279 11 6.75V11H6.75C4.40279 11 2.5 9.09721 2.5 6.75C2.5 4.40279 4.40279 2.5 6.75 2.5ZM9 9V6.75C9 5.50736 7.99264 4.5 6.75 4.5C5.50736 4.5 4.5 5.50736 4.5 6.75C4.5 7.99264 5.50736 9 6.75 9H9ZM6.75 13H11V17.25C11 19.5972 9.09721 21.5 6.75 21.5C4.40279 21.5 2.5 19.5972 2.5 17.25C2.5 14.9028 4.40279 13 6.75 13ZM6.75 15C5.50736 15 4.5 16.0074 4.5 17.25C4.5 18.4926 5.50736 19.5 6.75 19.5C7.99264 19.5 9 18.4926 9 17.25V15H6.75ZM17.25 2.5C19.5972 2.5 21.5 4.40279 21.5 6.75C21.5 9.09721 19.5972 11 17.25 11H13V6.75C13 4.40279 14.9028 2.5 17.25 2.5ZM17.25 9C18.4926 9 19.5 7.99264 19.5 6.75C19.5 5.50736 18.4926 4.5 17.25 4.5C16.0074 4.5 15 5.50736 15 6.75V9H17.25ZM13 13H17.25C19.5972 13 21.5 14.9028 21.5 17.25C21.5 19.5972 19.5972 21.5 17.25 21.5C14.9028 21.5 13 19.5972 13 17.25V13ZM15 15V17.25C15 18.4926 16.0074 19.5 17.25 19.5C18.4926 19.5 19.5 18.4926 19.5 17.25C19.5 16.0074 18.4926 15 17.25 15H15Z">
                        </path>
                    </svg>
                </div>

                <span class=" capitalize whitespace-nowrap text-xs"> Categories</span>
            </center>
        </a>

        @foreach ($categories as $item)
            <div class=" text-center block">
                <center>
                    <a href="{{ route('search') }}?category={{ $item->id }}"
                        class="btn-circle grid place-items-center  size-20 overflow-hidden rounded-full bg-[#FFEBD8] p-3">
                        <img src="{{ $item->thumbnailUrl }}" alt="" class="size-10">
                    </a>
                    <span class=" capitalize whitespace-nowrap text-xs"> {{ $item->name }} </span>
                </center>
            </div>
        @endforeach
    </div>
</div>
