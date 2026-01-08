<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('HasCarts Trait', function () {
    beforeEach(function () {
        // Create a user and authenticate them
        $this->user    = User::factory()->create();
        $this->product = Product::factory()->create();
        $this->actingAs($this->user);
    });

    it('saves a product to the cart with the specified quantity', function () {
        $productId = $this->product->id;
        $quantity  = 2;

        // Call the saveCart method
        $this->user->saveCart($productId, $quantity);

        // Assert the cart record exists
        $this->assertDatabaseHas('carts', [
            'user_id'    => $this->user->id,
            'product_id' => $productId,
            'quantity'   => $quantity,
        ]);
    });

    it('updates the quantity if the product is already in the cart', function () {
        $productId = $this->product->id;

        // Add an existing cart entry
        Cart::factory()->create([
            'user_id'    => $this->user->id,
            'product_id' => $productId,
            'quantity'   => 1,
        ]);

        $newQuantity = 5;

        // Call the saveCart method
        $this->user->saveCart($productId, $newQuantity);

        // Assert the cart record is updated
        $this->assertDatabaseHas('carts', [
            'user_id'    => $this->user->id,
            'product_id' => $productId,
            'quantity'   => $newQuantity,
        ]);
    });

    it('does not save a product if quantity is less than 1', function () {
        $productId = $this->product->id;

        // Call the saveCart method with quantity 0
        $this->user->saveCart($productId, 0);

        // Assert no cart record exists
        $this->assertDatabaseMissing('carts', [
            'user_id'    => $this->user->id,
            'product_id' => $productId,
        ]);
    });

    it('deletes a product from the cart', function () {
        $productId = $this->product->id;

        // Add an existing cart entry
        Cart::factory()->create([
            'user_id'    => $this->user->id,
            'product_id' => $productId,
        ]);

        // Call the delete method
        $this->user->delete($productId);

        // Assert the cart record no longer exists
        $this->assertDatabaseMissing('carts', [
            'user_id'    => $this->user->id,
            'product_id' => $productId,
        ]);
    });

    it('does nothing when deleting a product that is not in the cart', function () {
        $productId = $this->product->id;

        // Call the delete method
        $result = $this->user->deleteCart($productId);

        // Assert the result is null (null safety check)
        expect($result)->toBeNull();
    });

    it('handles null safety when product ID is not available', function () {
        $invalidProductId = 9999; // An ID that doesn't exist in the products table

        // Assert that the product ID does not exist in the products table
        $this->assertDatabaseMissing('products', ['id' => $invalidProductId]);

        // Call the saveCart method with a non-existent product ID
        $result = $this->user->saveCart($invalidProductId, 1);

        // Assert no cart record is created
        $this->assertDatabaseCount('carts', 0);
        expect($result)->toBeNull();

        // Call the delete method with a non-existent product ID
        $deleteResult = $this->user->deleteCart($invalidProductId);

        // Assert null safety
        expect($deleteResult)->toBeNull();
    });

    it('does not add or delete products in other users\' carts', function () {
        $otherUser = User::factory()->create(); // Create another user
        $productId = $this->product->id;

        // Add a cart entry for the other user
        Cart::factory()->create([
            'user_id'    => $otherUser->id,
            'product_id' => $productId,
            'quantity'   => 3,
        ]);

        // Call the saveCart method for the authenticated user
        $this->user->saveCart($productId, 5);

        // Assert the authenticated user's cart is updated
        $this->assertDatabaseHas('carts', [
            'user_id'    => $this->user->id,
            'product_id' => $productId,
            'quantity'   => 5,
        ]);

        // Assert the other user's cart remains unchanged
        $this->assertDatabaseHas('carts', [
            'user_id'    => $otherUser->id,
            'product_id' => $productId,
            'quantity'   => 3,
        ]);

        // Call the deleteCart method for the authenticated user
        $this->user->deleteCart($productId);

        // Assert the authenticated user's cart is deleted
        $this->assertDatabaseMissing('carts', [
            'user_id'    => $this->user->id,
            'product_id' => $productId,
        ]);

        // Assert the other user's cart remains unchanged
        $this->assertDatabaseHas('carts', [
            'user_id'    => $otherUser->id,
            'product_id' => $productId,
        ]);
    });

    it('can retrieve carts via the carts relationship', function () {
        $productId = $this->product->id;
        $quantity  = 2;

        // Add the product to the user's cart
        $this->user->saveCart($productId, $quantity);

        // Eager load the carts relationship
        $user = $this->user->load('carts');

        // Retrieve carts through the relationship
        $userCarts = $user->carts;

        // Assert that the user has the correct cart entry
        $this->assertCount(1, $userCarts);
        $this->assertEquals($productId, $userCarts->first()->product_id);
        $this->assertEquals($quantity, $userCarts->first()->quantity);
    });

});
