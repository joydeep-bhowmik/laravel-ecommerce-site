<?php use App\Models\Setting; ?>
<a href="{{ url('/') }}">

    <img {{ $attributes->merge(['src' => asset('/storage/' . Setting::get('logo')), 'class' => 'h-20']) }}>
</a>
