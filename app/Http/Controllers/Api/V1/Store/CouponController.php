<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Contracts\Services\CouponServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Coupon\ApplyCouponRequest;
use App\Http\Requests\Coupon\StoreCouponRequest;
use App\Http\Requests\Coupon\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(
        protected CouponServiceInterface $couponService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $coupons = $this->couponService->list(
            $request->only(['search', 'is_active', 'per_page'])
        );

        return $this->paginated(CouponResource::collection($coupons));
    }

    public function store(StoreCouponRequest $request): JsonResponse
    {
        $coupon = $this->couponService->create($request->validated());

        return $this->created(
            new CouponResource($coupon),
            'Coupon created successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $coupon = $this->couponService->findById($id);

        return $this->success(new CouponResource($coupon));
    }

    public function update(UpdateCouponRequest $request, int $id): JsonResponse
    {
        $coupon = $this->couponService->findById($id);
        $coupon = $this->couponService->update($coupon, $request->validated());

        return $this->success(
            new CouponResource($coupon),
            'Coupon updated successfully.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $coupon = $this->couponService->findById($id);
        $this->couponService->delete($coupon);

        return $this->noContent('Coupon deleted successfully.');
    }

    public function apply(ApplyCouponRequest $request): JsonResponse
    {
        $cart = $request->user()
            ->carts()
            ->with('items')
            ->where('tenant_id', app('currentTenant')->id)
            ->first();

        $subtotal = $cart
            ? $cart->items->sum(fn($item) => $item->unit_price * $item->quantity)
            : 0;

        $coupon         = $this->couponService->validate(
            $request->validated('coupon_code'),
            $request->user(),
            $subtotal
        );
        $discountAmount = $this->couponService->apply($coupon, $request->user(), $subtotal);

        return $this->success([
            'coupon'          => new CouponResource($coupon),
            'discount_amount' => round($discountAmount, 2),
            'subtotal'        => round($subtotal, 2),
            'new_total'       => round($subtotal - $discountAmount, 2),
        ], 'Coupon applied successfully.');
    }
}
