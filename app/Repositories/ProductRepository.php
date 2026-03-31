<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        return Product::query()
            ->with('category')
            ->when(
                isset($filters['search']),
                fn($q) => $q->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('sku', 'like', "%{$filters['search']}%");
                })
            )
            ->when(
                isset($filters['category_id']),
                fn($q) => $q->where('category_id', $filters['category_id'])
            )
            ->when(
                isset($filters['is_active']),
                fn($q) => $q->where('is_active', $filters['is_active'])
            )
            ->when(
                isset($filters['in_stock']),
                fn($q) => $q->where('stock_quantity', '>', 0)
            )
            ->when(
                isset($filters['min_price']),
                fn($q) => $q->where('price', '>=', $filters['min_price'])
            )
            ->when(
                isset($filters['max_price']),
                fn($q) => $q->where('price', '<=', $filters['max_price'])
            )
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Product
    {
        return Product::with('category')->find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh('category');
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
