<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug'              => $this->faker->unique()->slug,
            'name'              => $this->faker->words(3, true),
            'description'       => $this->faker->sentence,
            'similiar_products' => json_encode($this->faker->randomElements([1, 2, 3, 4, 5], 3)),
            'sizes'             => json_encode($this->faker->randomElements(['S', 'M', 'L', 'XL'], 2)),
        ];
    }
}
