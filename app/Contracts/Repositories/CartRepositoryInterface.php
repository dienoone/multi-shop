<?php

namespace App\Contracts\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;

interface CartRepositoryInterface
{
    public function findOrCreateCart(User $user): Cart;
    public function findCart(User $user): ?Cart;
    public function findCartItem(Cart $cart, int $cartItemId): ?CartItem;
    public function findCartItemByProduct(Cart $cart, int $productId): ?CartItem;
    public function createCartItem(Cart $cart, array $data): CartItem;
    public function updateCartItem(CartItem $item, array $data): CartItem;
    public function deleteCartItem(CartItem $item): bool;
    public function clearCart(Cart $cart): bool;
}
