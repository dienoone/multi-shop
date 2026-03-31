<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Contracts\Services\CartServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartServiceInterface $cartService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->user());

        return $this->success(new CartResource($cart));
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $item = $this->cartService->addItem(
            $request->user(),
            $request->validated('product_id'),
            $request->validated('quantity')
        );

        return $this->created(
            new CartItemResource($item),
            'Item added to cart.'
        );
    }

    public function updateItem(UpdateCartItemRequest $request, int $cartItemId): JsonResponse
    {
        $item = $this->cartService->updateItem(
            $request->user(),
            $cartItemId,
            $request->validated('quantity')
        );

        return $this->success(
            new CartItemResource($item),
            'Cart item updated.'
        );
    }

    public function removeItem(Request $request, int $cartItemId): JsonResponse
    {
        $this->cartService->removeItem($request->user(), $cartItemId);

        return $this->noContent('Item removed from cart.');
    }

    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clearCart($request->user());

        return $this->noContent('Cart cleared.');
    }
}
