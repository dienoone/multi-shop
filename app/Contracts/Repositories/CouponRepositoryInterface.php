<?php

namespace App\Contracts\Repositories;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface CouponRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator;
    public function findById(int $id): ?Coupon;
    public function findByCode(string $code): ?Coupon;
    public function create(array $data): Coupon;
    public function update(Coupon $coupon, array $data): Coupon;
    public function delete(Coupon $coupon): bool;
    public function hasUserUsedCoupon(Coupon $coupon, User $user): bool;
    public function recordUsage(Coupon $coupon, User $user, Order $order, float $discountAmount): CouponUsage;
    public function incrementUsedCount(Coupon $coupon): void;
}
