<?php

namespace App\Enums;

enum PermissionType: string
{
    // Tenant management (super_admin only)
    case TenantsCreate  = 'tenants.create';
    case TenantsEdit    = 'tenants.edit';
    case TenantsDelete  = 'tenants.delete';
    case TenantsView    = 'tenants.view';

        // Products
    case ProductsView   = 'products.view';
    case ProductsCreate = 'products.create';
    case ProductsEdit   = 'products.edit';
    case ProductsDelete = 'products.delete';

        // Categories
    case CategoriesCreate = 'categories.create';
    case CategoriesEdit   = 'categories.edit';
    case CategoriesDelete = 'categories.delete';

        // Orders
    case OrdersPlace        = 'orders.place';
    case OrdersViewOwn      = 'orders.view-own';
    case OrdersViewAll      = 'orders.view-all';
    case OrdersUpdateStatus = 'orders.update-status';

        // Cart
    case CartManage = 'cart.manage';

        // Coupons
    case CouponsManage = 'coupons.manage';
    case CouponsApply  = 'coupons.apply';

        // Reviews
    case ReviewsCreate  = 'reviews.create';
    case ReviewsEditOwn = 'reviews.edit-own';

        // Profile
    case ProfileEdit = 'profile.edit';

        // Dashboard
    case DashboardView = 'dashboard.view';

        // Store settings
    case StoreSettings = 'store.settings';
}
