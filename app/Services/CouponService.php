<?php

namespace App\Services;

use App\Contracts\Repositories\CouponRepositoryInterface;
use App\Contracts\Services\CouponServiceInterface;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CouponService implements CouponServiceInterface
{
    public function __construct(
        protected CouponRepositoryInterface $couponRepository
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->couponRepository->all($filters);
    }

    public function findById(int $id): Coupon
    {
        $coupon = $this->couponRepository->findById($id);

        throw_if(!$coupon, ModelNotFoundException::class, 'Coupon not found.');

        return $coupon;
    }

    public function create(array $data): Coupon
    {
        return $this->couponRepository->create($data);
    }

    public function update(Coupon $coupon, array $data): Coupon
    {
        return $this->couponRepository->update($coupon, $data);
    }

    public function delete(Coupon $coupon): void
    {
        $this->couponRepository->delete($coupon);
    }

    // Validates the coupon against all rules — throws ValidationException if any fail
    public function validate(string $code, User $user, float $subtotal): Coupon
    {
        $coupon = $this->couponRepository->findByCode($code);

        throw_if(
            !$coupon || !$coupon->is_active,
            ValidationException::withMessages([
                'coupon_code' => 'This coupon code is invalid.',
            ])
        );

        throw_if(
            $coupon->isExpired(),
            ValidationException::withMessages([
                'coupon_code' => 'This coupon has expired.',
            ])
        );

        throw_if(
            $coupon->isExhausted(),
            ValidationException::withMessages([
                'coupon_code' => 'This coupon has reached its usage limit.',
            ])
        );

        throw_if(
            $this->couponRepository->hasUserUsedCoupon($coupon, $user),
            ValidationException::withMessages([
                'coupon_code' => 'You have already used this coupon.',
            ])
        );

        throw_if(
            $subtotal < $coupon->minimum_order_amount,
            ValidationException::withMessages([
                'coupon_code' => "A minimum order of {$coupon->minimum_order_amount} is required for this coupon.",
            ])
        );

        return $coupon;
    }

    // Returns the calculated discount amount — does NOT record usage
    // Usage is recorded in OrderService after the order is created
    public function apply(Coupon $coupon, User $user, float $subtotal): float
    {
        return $coupon->calculateDiscount($subtotal);
    }
}
