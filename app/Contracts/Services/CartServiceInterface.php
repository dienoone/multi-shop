<?php

namespace App\Contracts\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;

interface CartServiceInterface
{
    public function getCart(User $user): Cart;
    public function addItem(User $user, int $productId, int $quantity): CartItem;
    public function updateItem(User $user, int $cartItemId, int $quantity): CartItem;
    public function removeItem(User $user, int $cartItemId): void;
    public function clearCart(User $user): void;
}
