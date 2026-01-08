<?php
namespace App\Traits;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasWishLists
{
    /**
     * The function addToWishlist adds a product to a user's wishlist in PHP.
     *
     * @param string product_id The `addToWishlist` function takes a `product_id` as a parameter, which
     * is a string representing the ID of the product that the user wants to add to their wishlist. The
     * function then finds the product with the given ID, retrieves the authenticated user, creates a
     * new Wishlist instance, assigns
     *
     * @return The `addToWishlist` function is returning a boolean value indicating whether the
     * operation of adding a product to the wishlist was successful or not. If the product is not
     * found, the function will return `null`. Otherwise, it will return `true` if the product was
     * successfully added to the wishlist, and `false` if there was an issue with saving the wishlist
     * item.
     */
    public function addToWishlist(string $product_id)
    {
        $product = Product::find($product_id);

        $user = auth()->user();

        if (! $product) {
            return;
        }

        $wishList = new Wishlist();

        $wishList->user_id = $user->id;

        $wishList->product_id = $product->id;

        return $wishList->save();
    }

    /**
     * The function `removeFromWishList` removes a product from a user's wishlist in PHP.
     *
     * @param string product_id The `removeFromWishList` function takes a `product_id` as a parameter.
     * This function is responsible for removing a product from the user's wishlist.
     *
     * @return The function `removeFromWishList` returns a boolean value. It returns `true` if the
     * product with the specified `product_id` was found in the user's wishlist and successfully
     * removed, otherwise it returns `false`.
     */
    public function removeFromWishList(string $product_id)
    {
        $user = auth()->user();

        $wishList = Wishlist::where('product_id', $product_id)->where('user_id', $user?->id)->first();

        return $wishList && $wishList->delete();
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);

    }
}
