<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('HasWishLists Trait', function () {

    beforeEach(function () {
        $this->user    = User::factory()->create();
        $this->product = Product::factory()->create();
        $this->actingAs($this->user);
    });

    it('can add a product to wishlist', function () {
        $result = $this->user->addToWishlist($this->product->id);

        // Assert the wishlist entry exists
        $this->assertDatabaseHas('wishlists', [
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        expect($result)->toBeTrue();
    });

    it('returns null when a product id is invalid', function () {
        $invalidProductId = 9999; // ID that doesn't exist in the products table

        // Assert that the product ID does not exist
        $this->assertDatabaseMissing('products', ['id' => $invalidProductId]);

        $result = $this->user->addToWishlist($invalidProductId);

        // Assert no wishlist entry is created
        $this->assertDatabaseMissing('wishlists', [
            'user_id'    => $this->user->id,
            'product_id' => $invalidProductId,
        ]);

        expect($result)->toBeNull();
    });

    it('can delete a product from wishlist', function () {
        // Add the product to the wishlist first
        $this->user->addToWishlist($this->product->id);

        // Assert the wishlist entry exists
        $this->assertDatabaseHas('wishlists', [
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $result = $this->user->removeFromWishlist($this->product->id);

        // Assert the wishlist entry is removed
        $this->assertDatabaseMissing('wishlists', [
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        expect($result)->toBeTrue();
    });

    it("doesn't interfere with another user's wishlist", function () {
        $anotherUser    = User::factory()->create();
        $anotherProduct = Product::factory()->create();

        // Add a product to the current user's wishlist
        $this->user->addToWishlist($this->product->id);

        // Act as another user and add a product to their wishlist
        $this->actingAs($anotherUser);
        $anotherUser->addToWishlist($anotherProduct->id);

        // Assert each user's wishlist entry exists
        $this->assertDatabaseHas('wishlists', [
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);
        $this->assertDatabaseHas('wishlists', [
            'user_id'    => $anotherUser->id,
            'product_id' => $anotherProduct->id,
        ]);

        // Remove product from the current user's wishlist
        $this->actingAs($this->user);
        $this->user->removeFromWishlist($this->product->id);

        // Assert only the current user's wishlist is affected
        $this->assertDatabaseMissing('wishlists', [
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ]);
        $this->assertDatabaseHas('wishlists', [
            'user_id'    => $anotherUser->id,
            'product_id' => $anotherProduct->id,
        ]);
    });

    it('can retrieve wihslist via the HasMany relationship', function () {
        $productId = $this->product->id;

        // Add the product to the user's cart
        $this->user->addToWishlist($productId);

        // Eager load the carts relationship
        $user = $this->user->load('wishlists');

        // Retrieve carts through the relationship
        $userWishlists = $user->wishlists;

        // Assert that the user has the correct cart entry
        $this->assertCount(1, $userWishlists);
        $this->assertEquals($productId, $userWishlists->first()->product_id);
    });

});
