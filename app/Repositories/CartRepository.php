<?php

namespace App\Repositories;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;

class CartRepository implements CartRepositoryInterface
{
    public function findOrCreateCart(User $user): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $user->id]
        )->load('items.product.category');
    }

    public function findCart(User $user): ?Cart
    {
        return Cart::where('user_id', $user->id)->first();
    }

    public function findCartItem(Cart $cart, int $cartItemId): ?CartItem
    {
        return $cart->items()->with('product')->find($cartItemId);
    }

    public function findCartItemByProduct(Cart $cart, int $productId): ?CartItem
    {
        return $cart->items()->where('product_id', $productId)->first();
    }

    public function createCartItem(Cart $cart, array $data): CartItem
    {
        return $cart->items()->create($data)->load('product');
    }

    public function updateCartItem(CartItem $item, array $data): CartItem
    {
        $item->update($data);
        return $item->fresh('product');
    }

    public function deleteCartItem(CartItem $item): bool
    {
        return $item->delete();
    }

    public function clearCart(Cart $cart): bool
    {
        return (bool) $cart->items()->delete();
    }
}
