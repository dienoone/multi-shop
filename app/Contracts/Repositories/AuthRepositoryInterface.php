<?php

namespace App\Contracts\Repositories;

use App\DTOs\Auth\SocialUserDTO;
use App\Models\SocialAccount;
use App\Models\User;

interface AuthRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
    public function create(array $data): ?User;
    public function update(User $user, array $data): ?User;
    public function markEmailAsVerified(User $user): bool;
    public function updatePassword(User $user, string $password): bool;
    public function findSocialAccount(string $provider, string $providerId): ?SocialAccount;
    public function createSocialAccount(User $user, SocialUserDTO $dto): SocialAccount;
    public function updateSocialAccount(SocialAccount $account, string $avatar): SocialAccount;
}
