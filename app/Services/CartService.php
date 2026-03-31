<?php

namespace App\Services;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Services\CartServiceInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class CartService implements CartServiceInterface
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository
    ) {}

    public function getCart(User $user): Cart
    {
        return $this->cartRepository->findOrCreateCart($user);
    }

    public function addItem(User $user, int $productId, int $quantity): CartItem
    {
        $product = Product::find($productId);

        throw_if(
            !$product,
            ModelNotFoundException::class,
            'Product not found.'
        );

        throw_if(
            !$product->is_active,
            ValidationException::withMessages([
                'product_id' => 'This product is not available.',
            ])
        );

        throw_if(
            $product->stock_quantity < $quantity,
            ValidationException::withMessages([
                'quantity' => "Only {$product->stock_quantity} items available in stock.",
            ])
        );

        $cart = $this->cartRepository->findOrCreateCart($user);

        // If item already in cart — increment quantity instead of duplicating
        $existingItem = $this->cartRepository->findCartItemByProduct($cart, $productId);

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;

            throw_if(
                $product->stock_quantity < $newQuantity,
                ValidationException::withMessages([
                    'quantity' => "Only {$product->stock_quantity} items available in stock.",
                ])
            );

            return $this->cartRepository->updateCartItem(
                $existingItem,
                ['quantity' => $newQuantity]
            );
        }

        return $this->cartRepository->createCartItem($cart, [
            'product_id' => $productId,
            'quantity'   => $quantity,
            'unit_price' => $product->price,
        ]);
    }

    public function updateItem(User $user, int $cartItemId, int $quantity): CartItem
    {
        $item = $this->resolveCartItem($user, $cartItemId);

        throw_if(
            $item->product->stock_quantity < $quantity,
            ValidationException::withMessages([
                'quantity' => "Only {$item->product->stock_quantity} items available in stock.",
            ])
        );

        return $this->cartRepository->updateCartItem($item, ['quantity' => $quantity]);
    }

    public function removeItem(User $user, int $cartItemId): void
    {
        $item = $this->resolveCartItem($user, $cartItemId);
        $this->cartRepository->deleteCartItem($item);
    }

    public function clearCart(User $user): void
    {
        $cart = $this->cartRepository->findCart($user);

        if ($cart) {
            $this->cartRepository->clearCart($cart);
        }
    }

    private function resolveCartItem(User $user, int $cartItemId): CartItem
    {
        $cart = $this->cartRepository->findCart($user);

        throw_if(
            !$cart,
            ModelNotFoundException::class,
            'Cart not found.'
        );

        $item = $this->cartRepository->findCartItem($cart, $cartItemId);

        throw_if(
            !$item,
            ModelNotFoundException::class,
            'Cart item not found.'
        );

        return $item;
    }
}
