<?php

namespace App\DTOs\Auth;

use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialUserDTO
{
    public function __construct(
        public readonly string  $provider,
        public readonly string  $providerId,
        public readonly string  $name,
        public readonly string  $email,
        public readonly ?string $avatar,
    ) {}

    public static function fromSocialite(string $provider, SocialiteUser $user): self
    {
        return new self(
            provider: $provider,
            providerId: $user->getId(),
            name: $user->getName() ?? $user->getNickname() ?? 'Unknown',
            email: $user->getEmail(),
            avatar: $user->getAvatar(),
        );
    }
}
