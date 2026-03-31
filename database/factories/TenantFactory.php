<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name'      => $name,
            'subdomain' => str($name)->slug()->toString() . '-' . fake()->unique()->numberBetween(1, 999),
            'email'     => fake()->unique()->companyEmail(),
            'plan'      => fake()->randomElement(['free', 'starter', 'pro']),
            'is_active' => true,
            'settings'  => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function plan(string $plan): static
    {
        return $this->state(['plan' => $plan]);
    }
}
