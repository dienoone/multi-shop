<?php

namespace App\Repositories;

use App\Contracts\Repositories\CouponRepositoryInterface;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class CouponRepository implements CouponRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        return Coupon::query()
            ->when(
                isset($filters['search']),
                fn($q) => $q->where('code', 'like', "%{$filters['search']}%")
            )
            ->when(
                isset($filters['is_active']),
                fn($q) => $q->where('is_active', $filters['is_active'])
            )
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Coupon
    {
        return Coupon::find($id);
    }

    public function findByCode(string $code): ?Coupon
    {
        return Coupon::where('code', strtoupper($code))->first();
    }

    public function create(array $data): Coupon
    {
        $data['code'] = strtoupper($data['code']);
        return Coupon::create($data);
    }

    public function update(Coupon $coupon, array $data): Coupon
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }
        $coupon->update($data);
        return $coupon->fresh();
    }

    public function delete(Coupon $coupon): bool
    {
        return $coupon->delete();
    }

    public function hasUserUsedCoupon(Coupon $coupon, User $user): bool
    {
        return CouponUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function recordUsage(Coupon $coupon, User $user, Order $order, float $discountAmount): CouponUsage
    {
        return CouponUsage::create([
            'coupon_id'       => $coupon->id,
            'user_id'         => $user->id,
            'order_id'        => $order->id,
            'discount_amount' => $discountAmount,
        ]);
    }

    public function incrementUsedCount(Coupon $coupon): void
    {
        $coupon->increment('used_count');
    }
}
