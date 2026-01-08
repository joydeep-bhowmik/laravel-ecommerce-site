<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Coupon;
use App\Models\ShippingZone;
use App\Traits\HasWishLists;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasWishLists;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Relationship with Cart
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
    public function cartsCount(): int
    {
        return $this->carts()?->count();
    }
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress()
    {
        return $this->addresses()->where('is_default', true)->first();
    }

    public function totalCartWeight()
    {
        $volumetricFactor = 5000; // Adjust based on your shipping provider

        return $this->carts->sum(function ($cartItem) use ($volumetricFactor) {
            $actualWeight = $cartItem->weight;
            $volumetricWeight = ($cartItem->length * $cartItem->width * $cartItem->height) / $volumetricFactor;

            return max($actualWeight, $volumetricWeight); // Take the greater weight
        });
    }

    public function shippingCost(string|null $address_id = null)
    {
        $address = Address::find($address_id) ?? $this->defaultAddress();

        if (!$address) {
            return [
                'message' => 'No default address set',
                'shipping_cost' => null,
            ];
        }

        return ShippingZone::getShippingCost(
            $address->country,
            $address->state,
            $address->postal_code,
            $this->totalCartWeight()
        );
    }

    /**
     * Add an item to the cart
     */
    public function addToCart($productId, $sizeName, $quantity)
    {
        $product = Product::find($productId);

        if (!$product) {
            return ['status' => false, 'message' => 'Product not found'];
        }

        // Convert JSON sizes to a collection
        $sizes = collect($product->sizes);
        $size = $sizes->firstWhere('name', $sizeName);

        if (!$size) {
            return ['status' => false, 'message' => 'Size not found'];
        }

        // Ensure requested quantity does not exceed available quantity
        if ($quantity > $size['quantity']) {
            return ['status' => false, 'message' => 'Requested quantity exceeds available stock'];
        }

        // Check if the product already exists in the cart
        $cartItem = $this->carts()->where('product_id', $productId)->where('size_name', $sizeName)->first();

        if ($cartItem) {
            // Update quantity if already in cart
            $newQuantity = $cartItem->quantity + $quantity;

            if ($newQuantity > $size['quantity']) {
                return ['status' => false, 'message' => 'Total quantity exceeds available stock'];
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Add new cart item
            $this->carts()->create([
                'product_id' => $productId,
                'size_name' => $size['name'],
                'price' => $size['price'],
                'quantity' => $quantity,
                'length' => $size['length'] ?: null,
                'width' => $size['width'] ?: null,
                'height' => $size['height'] ?: null,
                'weight' => $size['weight'] ?: null,
            ]);

        }

        return ['status' => true, 'message' => 'Product added to cart'];
    }

    /**
     * Retrieve all cart items for the user
     */
    public function getCartItems()
    {
        return $this->carts()->with('product')->get();
    }
    /**
     * This PHP function calculates the total sum of all cart item totals.
     *
     * @return float The `cartTotal()` function is returning the sum of all cart item totals.
     */
    public function cartTotal(): float
    {
        return $this->carts()->get()->sum->total(); // Sum all cart item totals
    }

    /**
     * Update a cart item quantity
     */
    public function updateCartItem($cartId, $quantity)
    {
        $cartItem = $this->carts()->find($cartId);

        if (!$cartItem) {
            return ['status' => false, 'message' => 'Cart item not found'];
        }

        // Get the product's available quantity
        $product = $cartItem->product;
        $sizes = collect($product->sizes);
        $size = $sizes->firstWhere('name', $cartItem->size_name);

        if (!$size) {
            return ['status' => false, 'message' => 'Size not found in product'];
        }

        if ($quantity > $size['quantity']) {
            return ['status' => false, 'message' => 'Requested quantity exceeds available stock'];
        }

        $cartItem->update(['quantity' => $quantity]);

        return ['status' => true, 'message' => 'Cart item updated successfully'];
    }

    /**
     * Delete a cart item
     */
    public function removeCartItem($cartId)
    {
        $cartItem = $this->carts()->find($cartId);

        if (!$cartItem) {
            return ['status' => false, 'message' => 'Cart item not found'];
        }

        $cartItem->delete();

        return ['status' => true, 'message' => 'Cart item removed'];
    }

    /**
     * Clear the entire cart for the user
     */
    public function clearcarts()
    {
        $this->carts()->delete();
        return ['status' => true, 'message' => 'Cart cleared successfully'];
    }


    public function coupons()
    {
        return $this->belongsToMany(Coupon::class)->withPivot('redeemed_at')->withTimestamps();
    }

    public function hasRedeemedCoupon($coupon)
    {
        return $this->coupons()->where('coupon_id', $coupon->id)->exists();
    }
}
