<?php

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'tenant_id'         => null,
            'name'              => 'Super Admin',
            'email'             => 'admin@multishop.test',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $admin->assignRole(RoleType::SuperAdmin->value);

        $this->command->info('Super admin created: admin@multishop.test / password');
    }
}
