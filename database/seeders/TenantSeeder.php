<?php

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tenant 1 — Nike (fixed, for easy Postman testing) ────────────
        $nike = Tenant::create([
            'name'      => 'Nike Store',
            'subdomain' => 'nike',
            'email'     => 'contact@nike.test',
            'plan'      => 'pro',
            'is_active' => true,
        ]);

        $this->createOwner($nike, 'Nike Owner', 'owner@nike.test');
        $this->seedStore($nike);

        // ── Tenant 2 — Adidas (fixed) ─────────────────────────────────────
        $adidas = Tenant::create([
            'name'      => 'Adidas Store',
            'subdomain' => 'adidas',
            'email'     => 'contact@adidas.test',
            'plan'      => 'starter',
            'is_active' => true,
        ]);

        $this->createOwner($adidas, 'Adidas Owner', 'owner@adidas.test');
        $this->seedStore($adidas);

        // ── 3 random tenants ──────────────────────────────────────────────
        Tenant::factory(3)->create()->each(function (Tenant $tenant) {
            $this->createOwner($tenant);
            $this->seedStore($tenant);
        });
    }

    private function createOwner(Tenant $tenant, ?string $name = null, ?string $email = null): User
    {
        $owner = User::create([
            'tenant_id'         => $tenant->id,
            'name'              => $name  ?? fake()->name(),
            'email'             => $email ?? fake()->unique()->safeEmail(),
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $owner->assignRole(RoleType::Store->value);

        return $owner;
    }

    private function seedStore(Tenant $tenant): void
    {
        // Bind tenant so BelongsToTenant auto-fills tenant_id
        app()->instance('currentTenant', $tenant);

        // Create 4 categories for this tenant
        $categories = Category::factory(4)->create([
            'tenant_id' => $tenant->id,
        ]);

        // Create 20 products spread across the categories
        $categories->each(function (Category $category) use ($tenant) {
            Product::factory(5)->create([
                'tenant_id'   => $tenant->id,
                'category_id' => $category->id,
            ]);
        });

        // Also seed a few special state products
        Product::factory(2)->outOfStock()->create([
            'tenant_id'   => $tenant->id,
            'category_id' => $categories->first()->id,
        ]);

        Product::factory(2)->withDiscount()->create([
            'tenant_id'   => $tenant->id,
            'category_id' => $categories->last()->id,
        ]);
    }
}
