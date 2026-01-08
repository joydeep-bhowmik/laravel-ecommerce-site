<?php

// app/Http/Controllers/API/ProductController.php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('sizes')
            ->where('is_active', true)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'base_price' => $product->price,
                    'original_price' => $product->original_price,
                    'firstImageUrl' => $product->images->first()->url ?? null,
                    'sizes' => $product->sizes->map(function ($size) {
                        return [
                            'name' => $size->name,
                            'quantity' => $size->quantity
                        ];
                    })
                ];
            });

        return response()->json($products);
    }

    public function show($slug)
    {
        $product = Product::with('sizes', 'images')
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'base_price' => $product->price,
            'original_price' => $product->original_price,
            'images' => $product->images->pluck('url'),
            'sizes' => $product->sizes->map(function ($size) {
                return [
                    'name' => $size->name,
                    'quantity' => $size->quantity
                ];
            })
        ]);
    }
}