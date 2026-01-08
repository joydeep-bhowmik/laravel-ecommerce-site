<?php
namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),                 // Creates a related product
            'user_id'    => User::factory(),                    // Creates a related user
            'quantity'   => $this->faker->numberBetween(1, 10), // Random quantity between 1 and 10
        ];
    }
}
