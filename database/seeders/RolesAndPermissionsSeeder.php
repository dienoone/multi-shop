<?php

namespace Database\Seeders;

use App\Enums\PermissionType;
use App\Enums\RoleType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect(PermissionType::cases())
            ->map(fn($p) => Permission::firstOrCreate(['name' => $p->value]));

        // SUPER ADMIN
        $superAdmin = Role::firstOrCreate(['name' => RoleType::SuperAdmin->value]);
        $superAdmin->syncPermissions($permissions);

        // STORE
        $storeAdmin = Role::firstOrCreate(['name' => RoleType::Store->value]);
        $storeAdmin->syncPermissions([
            PermissionType::ProductsView->value,
            PermissionType::ProductsCreate->value,
            PermissionType::ProductsEdit->value,
            PermissionType::ProductsDelete->value,
            PermissionType::CategoriesCreate->value,
            PermissionType::CategoriesEdit->value,
            PermissionType::CategoriesDelete->value,
            PermissionType::OrdersViewAll->value,
            PermissionType::OrdersUpdateStatus->value,
            PermissionType::CouponsManage->value,
            PermissionType::DashboardView->value,
            PermissionType::StoreSettings->value,
            PermissionType::ProfileEdit->value,
        ]);

        // CUSTOMER
        $customer = Role::firstOrCreate(['name' => RoleType::Customer->value]);
        $customer->syncPermissions([
            PermissionType::ProductsView->value,
            PermissionType::CartManage->value,
            PermissionType::OrdersPlace->value,
            PermissionType::OrdersViewOwn->value,
            PermissionType::ReviewsCreate->value,
            PermissionType::ReviewsEditOwn->value,
            PermissionType::CouponsApply->value,
            PermissionType::ProfileEdit->value,
        ]);
    }
}
