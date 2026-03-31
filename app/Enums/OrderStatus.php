<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Confirmed  = 'confirmed';
    case Processing = 'processing';
    case Shipped    = 'shipped';
    case Delivered  = 'delivered';
    case Cancelled  = 'cancelled';

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed]);
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending    => [self::Confirmed,  self::Cancelled],
            self::Confirmed  => [self::Processing, self::Cancelled],
            self::Processing => [self::Shipped],
            self::Shipped    => [self::Delivered],
            self::Delivered  => [],
            self::Cancelled  => [],
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions());
    }
}
