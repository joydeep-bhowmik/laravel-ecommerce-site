@props(['name'])

<?php

use App\Models\Banner;

$banner = Banner::where('name', $name)->first();

if ($banner) {
    $banners = $banner
        ->getMedia('banners')
        ->sortBy('order_column')
        ->map(
            fn($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
            ],
        )
        ->toArray();
}
?>
@if ($banner)
    <swiper-container {{ $attributes->merge(['class' => '!z-0']) }}>

        @foreach ($banners as $b)
            <swiper-slide>
                <img src="{{ $b['url'] }}" alt="Banner Image" class="w-full h-auto rounded-lg shadow-md">
            </swiper-slide>
        @endforeach

    </swiper-container>
@endif
