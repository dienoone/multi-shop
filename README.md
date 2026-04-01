# MultiShop — Multi-Tenant E-Commerce REST API

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Stripe-Payment-635BFF?style=for-the-badge&logo=stripe&logoColor=white" />
  <img src="https://img.shields.io/badge/Sanctum-Auth-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
</p>

A production-ready multi-tenant SaaS e-commerce backend built with Laravel 11. Each merchant gets a fully isolated store accessible via their own subdomain, sharing the same application and database infrastructure.

---

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
- [API Reference](#api-reference)
- [Project Structure](#project-structure)
- [Design Decisions](#design-decisions)

---

## Overview

MultiShop allows a super admin to spin up isolated online stores for merchants. Each store operates independently — products, orders, customers, and coupons are all scoped to the tenant automatically. A customer registered on `nike.multishop.test` cannot see data from `adidas.multishop.test`.

The system handles the full e-commerce lifecycle:

```
Tenant created → Products added → Customer registers → Adds to cart
→ Applies coupon → Places order → Stripe charges card
→ Webhook confirms payment → Order status updated
```

---

## Architecture

### Multi-Tenancy Strategy

Single-database multi-tenancy with subdomain-based tenant identification.

```
nike.multishop.test/api/products    → resolves to Tenant { id: 1 }
adidas.multishop.test/api/products  → resolves to Tenant { id: 2 }
```

Every tenant-scoped model uses a `BelongsToTenant` trait that registers a **Global Scope** — automatically injecting `WHERE tenant_id = X` into every query. This means tenant isolation is enforced at the model layer, not the controller layer.

```php
// You write this:
Product::all()

// Laravel executes this:
SELECT * FROM products WHERE tenant_id = 1
```

### Request Lifecycle

```
Request
  → IdentifyTenant middleware   (reads subdomain, binds tenant to container)
  → EnsureTenantActive          (blocks inactive stores)
  → Auth middleware             (Sanctum token validation)
  → Permission middleware       (Spatie role/permission check)
  → Controller
  → Service                     (business logic)
  → Repository                  (database layer)
  → Response (ApiResponse trait — consistent JSON envelope)
```

### Design Patterns

| Pattern            | Where used                                                |
| ------------------ | --------------------------------------------------------- |
| Repository pattern | All data access abstracted behind interfaces              |
| Service layer      | Business logic isolated from controllers                  |
| Interface binding  | All services and repositories bound via service container |
| Global Scopes      | Automatic tenant isolation on all models                  |
| DTO                | Social auth data transfer (SocialUserDTO)                 |
| Resource classes   | Consistent API response transformation                    |
| Form Requests      | Validation logic extracted from controllers               |

---

## Features

### Platform (Super Admin)

- Create, update, activate/deactivate, and delete tenant stores
- Each tenant creation auto-creates a store owner account
- Filter tenants by plan, status, and search

### Authentication

- Register, login, logout with Laravel Sanctum API tokens
- Email verification flow
- Forgot / reset password
- Change password (with current password check)
- Social login via OAuth (Google, GitHub, Facebook) using Laravel Socialite
- Automatic account linking when same email registers via multiple providers

### Store Management (Store Admin)

- Full product CRUD with categories, stock tracking, SKU, pricing
- Discount pricing with compare price and auto-calculated discount percentage
- Category management with sort ordering
- Coupon management (fixed and percentage discounts)
- Order management with status transition validation
- Store dashboard statistics

### Customer Experience

- Browse products with filters (category, price range, stock, search)
- Persistent shopping cart stored in database (survives browser close)
- Add, update quantity, remove items, clear cart
- Apply coupon codes with live discount preview before checkout
- Place orders with shipping address capture
- View order history and track individual order status
- Cancel orders (only when pending or confirmed)

### Payments (Stripe)

- Stripe Payment Intents API — card data never touches your server
- Payment Intent created on order placement, `client_secret` returned to frontend
- Webhook handler verifies Stripe signature and confirms orders on successful payment
- Payment failure handling with status tracking

### Coupons

- Fixed amount and percentage discounts
- Minimum order amount requirement
- Maximum discount cap (for percentage coupons)
- Global usage limits
- Per-customer usage tracking (one use per customer)
- Expiry dates
- Live coupon validation endpoint before checkout

---

## Tech Stack

| Layer          | Technology                |
| -------------- | ------------------------- |
| Framework      | Laravel 11                |
| Language       | PHP 8.3                   |
| Authentication | Laravel Sanctum           |
| Authorization  | Spatie Laravel Permission |
| Social Auth    | Laravel Socialite         |
| Payments       | Stripe PHP SDK            |
| Database       | MySQL 8                   |
| Cache / Queue  | Redis                     |
| API Standards  | RESTful JSON API          |

---

## Getting Started

### Requirements

- PHP 8.2+
- MySQL 8+
- Redis
- Composer
- Stripe account (free test mode)
- Stripe CLI (for local webhook testing)

### Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/multishop.git
cd multishop

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Environment Setup

```env
APP_NAME=MultiShop
APP_URL=http://multishop.test
APP_BASE_DOMAIN=multishop.test

DB_CONNECTION=mysql
DB_DATABASE=multishop
DB_USERNAME=root
DB_PASSWORD=

STRIPE_KEY=pk_test_your_key_here
STRIPE_SECRET=sk_test_your_secret_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Social Auth (optional)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
```

### Local Domain Setup (Linux)

```bash
# Install dnsmasq
sudo pacman -S dnsmasq   # Arch Linux
sudo apt install dnsmasq  # Ubuntu/Debian

# Add wildcard rule — covers ALL subdomains automatically
echo 'address=/.multishop.test/127.0.0.1' | sudo tee /etc/dnsmasq.d/multishop.conf

sudo systemctl enable --now dnsmasq
```

### Database Setup

```bash
php artisan migrate --seed
```

This creates:

- Super admin account: `admin@multishop.test` / `password`
- Two demo stores: `nike.multishop.test` and `adidas.multishop.test`
- Store owner accounts: `owner@nike.test` / `password`
- Sample products, categories, and coupons for each store

### Running Locally

```bash
# Terminal 1 — Laravel server
php artisan serve --host=multishop.test --port=80

# Terminal 2 — Queue worker (for emails)
php artisan queue:work

# Terminal 3 — Stripe webhook forwarding
stripe listen --forward-to multishop.test/api/webhook/stripe
```

---

## API Reference

### Base URLs

```
Super admin:  http://multishop.test/api/v1/superadmin/...
Store routes: http://{subdomain}.multishop.test/api/v1/...
```

### Authentication

All protected routes require:

```
Authorization: Bearer {token}
```

### Endpoints Summary

#### Auth

| Method | Endpoint                         | Access        |
| ------ | -------------------------------- | ------------- |
| POST   | `/auth/register`                 | Public        |
| POST   | `/auth/login`                    | Public        |
| POST   | `/auth/logout`                   | Authenticated |
| GET    | `/auth/me`                       | Authenticated |
| POST   | `/auth/email/resend`             | Authenticated |
| GET    | `/auth/email/verify/{id}/{hash}` | Public        |
| POST   | `/auth/password/forgot`          | Public        |
| POST   | `/auth/password/reset`           | Public        |
| POST   | `/auth/password/change`          | Authenticated |
| POST   | `/auth/social/{provider}/token`  | Public        |

#### Catalog

| Method | Endpoint           | Access      |
| ------ | ------------------ | ----------- |
| GET    | `/categories`      | Public      |
| GET    | `/categories/{id}` | Public      |
| POST   | `/categories`      | Store Admin |
| PUT    | `/categories/{id}` | Store Admin |
| DELETE | `/categories/{id}` | Store Admin |
| GET    | `/products`        | Public      |
| GET    | `/products/{id}`   | Public      |
| POST   | `/products`        | Store Admin |
| PUT    | `/products/{id}`   | Store Admin |
| DELETE | `/products/{id}`   | Store Admin |

#### Cart

| Method | Endpoint           | Access   |
| ------ | ------------------ | -------- |
| GET    | `/cart`            | Customer |
| POST   | `/cart/items`      | Customer |
| PUT    | `/cart/items/{id}` | Customer |
| DELETE | `/cart/items/{id}` | Customer |
| DELETE | `/cart`            | Customer |

#### Orders

| Method | Endpoint                    | Access      |
| ------ | --------------------------- | ----------- |
| GET    | `/orders`                   | Customer    |
| POST   | `/orders`                   | Customer    |
| GET    | `/orders/{id}`              | Customer    |
| POST   | `/orders/{id}/cancel`       | Customer    |
| GET    | `/admin/orders`             | Store Admin |
| GET    | `/admin/orders/{id}`        | Store Admin |
| PATCH  | `/admin/orders/{id}/status` | Store Admin |

#### Coupons

| Method | Endpoint              | Access      |
| ------ | --------------------- | ----------- |
| POST   | `/coupons/apply`      | Customer    |
| GET    | `/admin/coupons`      | Store Admin |
| POST   | `/admin/coupons`      | Store Admin |
| GET    | `/admin/coupons/{id}` | Store Admin |
| PUT    | `/admin/coupons/{id}` | Store Admin |
| DELETE | `/admin/coupons/{id}` | Store Admin |

#### Super Admin

| Method | Endpoint              | Access      |
| ------ | --------------------- | ----------- |
| GET    | `/admin/tenants`      | Super Admin |
| POST   | `/admin/tenants`      | Super Admin |
| GET    | `/admin/tenants/{id}` | Super Admin |
| PUT    | `/admin/tenants/{id}` | Super Admin |
| DELETE | `/admin/tenants/{id}` | Super Admin |

### Standard Response Envelope

Every response follows the same structure:

```json
{
    "success": true,
    "message": "Products retrieved successfully.",
    "data": {},
    "meta": null,
    "error": null
}
```

Paginated responses include meta:

```json
{
    "success": true,
    "message": "Success",
    "data": [],
    "meta": {
        "type": "length_aware",
        "total": 48,
        "per_page": 15,
        "current_page": 1,
        "last_page": 4,
        "has_more": true
    },
    "error": null
}
```

Error responses:

```json
{
    "success": false,
    "message": "Validation failed",
    "data": null,
    "meta": null,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Validation failed",
        "error_id": "uuid-here",
        "errors": {
            "email": ["The email field is required."]
        },
        "timestamp": "2024-01-01T00:00:00.000000Z"
    }
}
```

---

## Project Structure

```
app/
├── Contracts/
│   ├── Repositories/          Interface for every repository
│   └── Services/              Interface for every service
├── DTOs/
│   └── Auth/SocialUserDTO.php
├── Enums/
│   ├── RoleType.php
│   ├── PermissionType.php
│   ├── OrderStatus.php
│   ├── DiscountType.php
│   └── PaymentStatus.php
├── Exceptions/
│   └── ApiExceptionHandler.php
├── Helpers/
│   └── RouteHelper.php
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── Auth/
│   │   ├── Store/
│   │   ├── SuperAdmin/
│   │   └── Webhook/
│   ├── Middleware/
│   │   ├── IdentifyTenant.php
│   │   └── EnsureTenantActive.php
│   ├── Requests/
│   └── Resources/
├── Models/
│   ├── Tenant.php
│   ├── User.php
│   ├── SocialAccount.php
│   ├── Category.php
│   ├── Product.php
│   ├── Cart.php
│   ├── CartItem.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Coupon.php
│   └── CouponUsage.php
├── Providers/
│   └── AppBindingServiceProvider.php
├── Repositories/
├── Services/
│   └── StripeService.php
└── Traits/
    ├── ApiResponse.php
    └── BelongsToTenant.php
```

---

## Design Decisions

**Why single-database multi-tenancy?**
Simpler infrastructure, easier backups, and sufficient for this scale. The `BelongsToTenant` global scope makes it invisible to the rest of the codebase — you add one trait to a model and tenant isolation is handled automatically everywhere.

**Why Repository pattern?**
Decouples the data layer from business logic. Services depend on interfaces, not concrete implementations — making the code testable and swappable (e.g. switching from MySQL to MongoDB only requires a new repository class).

**Why store cart in the database instead of sessions?**
Session carts are lost when the user clears their browser or switches devices. A database cart persists across sessions and devices, which is what users expect from a real e-commerce experience.

**Why snapshot product data in order items?**
If a product name or price changes after an order is placed, the order history must still show what the customer actually paid. Snapshotting `product_name`, `product_sku`, and `unit_price` into `order_items` at the time of purchase preserves this historical accuracy.

**Why Payment Intents instead of Charges API?**
Payment Intents is the modern Stripe API — it handles SCA (Strong Customer Authentication) required in Europe, supports 3D Secure automatically, and never requires card details to touch your server. The older Charges API is deprecated for new integrations.

---

## Postman Collection

Import `multishop.postman_collection.json` from the repository root.

Set these collection variables before testing:

- `base_url` → `http://multishop.test/api/v1`
- `tenant_url` → `http://nike.multishop.test/api/v1`

Run the Login requests first — tokens are saved automatically to collection variables.

---
