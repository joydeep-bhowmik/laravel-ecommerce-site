<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'size_name',
        'price',
        'quantity',
        'length',
        'width',
        'height',
        'weight',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function total()
    {
        // Fetch product
        $product = $this->product;

        if (! $product || ! isset($product->sizes)) {
            return 0; // Return 0 if no product or sizes exist
        }

        // Decode sizes JSON column
        $sizes = $product->sizes;

        // Find the size that matches the cart's size_name
        $size = collect($sizes)->firstWhere('name', $this->size_name);

        if (! $size) {
            return 0; // If size not found, return 0
        }

        return $size['price'] * $this->quantity;
    }
}
