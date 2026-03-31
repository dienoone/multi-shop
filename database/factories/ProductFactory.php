<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name  = fake()->unique()->words(3, true);
        $price = fake()->randomFloat(2, 5, 500);

        return [
            'tenant_id'      => Tenant::factory(),
            'category_id'    => Category::factory(),
            'name'           => ucwords($name),
            'slug'           => Str::slug($name),
            'description'    => fake()->paragraphs(2, true),
            'price'          => $price,
            'compare_price'  => fake()->boolean(40)
                ? round($price * fake()->randomFloat(2, 1.1, 1.5), 2)
                : null,
            'stock_quantity' => fake()->numberBetween(0, 200),
            'sku'            => strtoupper(fake()->unique()->bothify('SKU-####-????')),
            'is_active'      => true,
            'images'         => [],
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(['stock_quantity' => 0]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withDiscount(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'compare_price' => round($attributes['price'] * 1.3, 2),
            ];
        });
    }
}
