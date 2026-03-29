<?php

namespace App\Repositories;

use App\Contracts\Repositories\AuthRepositoryInterface;
use App\DTOs\Auth\SocialUserDTO;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function create(array $data): ?User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): ?User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function markEmailAsVerified(User $user): bool
    {
        return $user->markEmailAsVerified();
    }

    public function updatePassword(User $user, string $password): bool
    {
        return $user->update([
            'password' => Hash::make($password),
        ]);
    }

    public function findSocialAccount(string $provider, string $providerId): ?SocialAccount
    {
        return SocialAccount::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->with('user')
            ->first();
    }

    public function createSocialAccount(User $user, SocialUserDTO $dto): SocialAccount
    {
        return SocialAccount::create([
            'user_id'     => $user->id,
            'provider'    => $dto->provider,
            'provider_id' => $dto->providerId,
            'avatar'      => $dto->avatar,
        ]);
    }

    public function updateSocialAccount(SocialAccount $account, string $avatar): SocialAccount
    {
        $account->update(['avatar' => $avatar]);
        return $account->fresh();
    }
}
