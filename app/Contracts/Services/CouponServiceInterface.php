<?php

namespace App\Contracts\Services;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface CouponServiceInterface
{
    public function list(array $filters = []): LengthAwarePaginator;
    public function findById(int $id): Coupon;
    public function create(array $data): Coupon;
    public function update(Coupon $coupon, array $data): Coupon;
    public function delete(Coupon $coupon): void;
    public function validate(string $code, User $user, float $subtotal): Coupon;
    public function apply(Coupon $coupon, User $user, float $subtotal): float;
}
