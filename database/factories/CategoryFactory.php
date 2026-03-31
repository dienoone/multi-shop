<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Electronics',
            'Clothing',
            'Shoes',
            'Accessories',
            'Home & Garden',
            'Sports',
            'Books',
            'Toys',
            'Beauty',
            'Automotive',
            'Food',
            'Jewelry',
            'Furniture',
            'Kitchenware',
            'Pet Supplies',
            'Office Supplies',
            'Art & Crafts',
            'Health & Wellness',
            'Computers',
            'Video Games',
            'Musical Instruments',
            'Camping & Hiking',
            'Tools & Hardware',
            'Baby & Kids',
            'Groceries',
            'Collectibles',
        ]);

        return [
            'tenant_id'   => Tenant::factory(),
            'name'        => $name,
            'slug'        => Str::slug($name),
            'description' => fake()->sentence(),
            'is_active'   => true,
            'sort_order'  => fake()->numberBetween(0, 100),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
