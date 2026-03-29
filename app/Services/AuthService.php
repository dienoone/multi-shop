<?php

namespace App\Services;

use App\Contracts\Repositories\AuthRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use App\DTOs\Auth\SocialUserDTO;
use App\Enums\RoleType;

class AuthService implements AuthServiceInterface
{
    private const ALLOWED_PROVIDERS = ['google', 'github', 'facebook'];

    public function __construct(
        protected AuthRepositoryInterface $authRepository
    ) {}

    public function register(array $data): User
    {
        $user = $this->authRepository->create([
            'name' => data_get($data, 'name'),
            'email' => data_get($data, 'email'),
            'password' => Hash::make(data_get($data, 'password'))
        ]);

        $user->assignRole(RoleType::Customer->value);
        $user->sendEmailVerificationNotification();

        return $user;
    }

    public function login(array $data): array
    {
        $user = $this->authRepository->findByEmail(data_get($data, 'email'));

        throw_if(
            !$user || !Hash::check(data_get($data, 'password'), $user->password),
            AuthenticationException::class,
            'Invalid credentials'
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function me(User $user): User
    {
        return $user->load('socialAccounts');
    }

    public function verifyEmail(int $id, string $hash): bool
    {
        $user = $this->authRepository->findById($id);

        throw_if(
            !$user,
            AuthorizationException::class,
            'User not found.'
        );

        throw_if(
            !hash_equals(sha1($user->email), $hash),
            AuthorizationException::class,
            'Invalid verification link.'
        );

        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $this->authRepository->markEmailAsVerified($user);

        return true;
    }

    public function resendVerification(User $user): void
    {

        throw_if(
            $user->hasVerifiedEmail(),
            \Exception::class,
            'Email is already verified.'
        );

        $user->sendEmailVerificationNotification();
    }

    public function forgotPassword(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);

        throw_if(
            $status !== Password::RESET_LINK_SENT,
            ValidationException::class,
            ['email' => 'Unable to send reset link. Please try again.',]
        );
    }

    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $this->authRepository->updatePassword($user, $password);

                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'token' => match ($status) {
                    Password::INVALID_TOKEN => 'This reset token is invalid or has expired.',
                    Password::INVALID_USER  => 'We could not find a user with that email.',
                    default                 => 'Unable to reset password. Please try again.',
                },
            ]);
        }
    }

    public function changePassword(User $user, array $data): void
    {
        // Social-only users have no password — block this endpoint for them
        if (is_null($user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Your account uses social login and has no password to change.',
            ]);
        }

        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        if (Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'New password must be different from your current password.',
            ]);
        }

        $this->authRepository->updatePassword($user, $data['password']);

        // Revoke all OTHER tokens so other devices are forced to re-login
        // but keep the current token alive so this session stays active
        $currentTokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();
    }

    public function handleSocialToken(string $provider, string $accessToken): array
    {
        $this->validateProvider($provider);

        try {
            // Use the access_token the frontend already obtained
            // Socialite fetches the user profile from the provider using it
            $socialiteUser = Socialite::driver($provider)->stateless()->userFromToken($accessToken);
        } catch (\Exception $e) {
            throw new AuthenticationException(
                'Invalid or expired access token for ' . ucfirst($provider) . '.'
            );
        }

        $dto   = SocialUserDTO::fromSocialite($provider, $socialiteUser);
        $user  = $this->resolveUserFromSocial($dto);
        $user->assignRole(RoleType::Customer->value);
        $token = $user->createToken('auth_token_' . $provider)->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    private function resolveUserFromSocial(SocialUserDTO $dto): User
    {
        // Case 1: this exact provider account already linked to a user
        $socialAccount = $this->authRepository->findSocialAccount($dto->provider, $dto->providerId);

        if ($socialAccount) {
            // Update avatar in case it changed on the provider side
            $this->authRepository->updateSocialAccount($socialAccount, $dto->avatar ?? '');
            return $socialAccount->user;
        }

        // Case 2: email exists — user registered normally or via another provider
        // Link this new provider to their existing account (don't overwrite anything)
        $user = $this->authRepository->findByEmail($dto->email);

        if ($user) {
            $this->authRepository->createSocialAccount($user, $dto);

            // Update avatar only if they don't already have one
            // If you need to update any other fields ...
            // if (!$user->avatar && $dto->avatar) {
            //     $this->authRepository->update($user, ['avatar' => $dto->avatar]);
            // }

            return $user->fresh();
        }

        // Case 3: brand new user — create account, auto-verify, and link provider
        $user = $this->authRepository->create([
            'name'              => $dto->name,
            'email'             => $dto->email,
            'password'          => null,
            'email_verified_at' => now(),
        ]);

        $this->authRepository->createSocialAccount($user, $dto);

        return $user;
    }

    private function validateProvider(string $provider): void
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS)) {
            throw ValidationException::withMessages([
                'provider' => 'Unsupported provider. Allowed: ' . implode(', ', self::ALLOWED_PROVIDERS),
            ]);
        }
    }
}
