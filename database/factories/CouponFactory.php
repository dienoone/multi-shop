<?php

namespace Database\Factories;

use App\Enums\DiscountType;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(DiscountType::cases());

        return [
            'tenant_id'                => Tenant::factory(),
            'code'                     => strtoupper(fake()->unique()->bothify('????##')),
            'discount_type'            => $type->value,
            'discount_value'           => $type === DiscountType::Percentage ? fake()->numberBetween(5, 50) : fake()->randomFloat(2, 5, 50),
            'minimum_order_amount'     => fake()->randomElement([0, 20, 50, 100]),
            'maximum_discount_amount'  => $type === DiscountType::Percentage ? fake()->randomElement([null, 30, 50]) : null,
            'usage_limit'              => fake()->randomElement([null, 10, 50, 100]),
            'used_count'               => 0,
            'expires_at'               => fake()->randomElement([
                null,
                now()->addDays(30),
                now()->addMonths(3),
            ]),
            'is_active'                => true,
        ];
    }

    public function percentage(): static
    {
        return $this->state([
            'discount_type'  => DiscountType::Percentage->value,
            'discount_value' => fake()->numberBetween(5, 50),
        ]);
    }

    public function fixed(): static
    {
        return $this->state([
            'discount_type'  => DiscountType::Fixed->value,
            'discount_value' => fake()->randomFloat(2, 5, 50),
        ]);
    }

    public function expired(): static
    {
        return $this->state([
            'expires_at' => now()->subDay(),
        ]);
    }
}
