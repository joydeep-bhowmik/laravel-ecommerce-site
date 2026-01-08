<?php
namespace App\Traits;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasCarts
{
    /**
     * The function `saveCart` saves a product to the user's cart with a specified quantity.
     *
     * @param string product_id The `product_id` parameter is a string that represents the unique
     * identifier of a product that you want to save in the user's cart. It is used to identify the
     * specific product that the user wants to add to their cart.
     * @param int quantity The `saveCart` function takes two parameters:
     *
     * @return The function `saveCart` is returning the result of the `save()` method called on the
     * `` object. This method will save the cart object to the database and return a boolean value
     * indicating whether the save operation was successful or not.
     */
    public function saveCart(string $product_id, int $quantity = 1)
    {

        $product = Product::find($product_id);

        $user = auth()->user();

        if ($quantity < 1 || ! $product || ! $user) {
            return;
        }

        $cart = Cart::where('product_id', $product->id)->where('user_id', $user->id)->first() ?? new Cart();

        $cart->user_id = $user->id;

        $cart->quantity = $quantity;

        $cart->product_id = $product_id;

        return $cart->save();

    }

    /**
     * The function `deleteCart` deletes a product from the user's cart if the product and user exist.
     *
     * @param string product_id The `deleteCart` function takes a `product_id` as a parameter. This
     * `product_id` is used to find the corresponding product in the database that needs to be removed
     * from the user's cart.
     *
     * @return The `deleteCart` function returns the result of the `delete` method called on the
     * `` object if `` is not null. If `` is null, then null will be returned.
     */
    public function deleteCart(string $product_id)
    {
        $product = Product::find($product_id);

        $user = auth()->user();

        if (! $product || ! $user) {
            return;
        }

        $cart = Cart::where('product_id', $product_id)->where('user_id', $user->id)->first();

        return $cart?->delete();
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);

    }
}
