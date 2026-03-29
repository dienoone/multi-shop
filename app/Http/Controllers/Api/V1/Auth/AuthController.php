<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Contracts\Services\AuthServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SocialTokenRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthServiceInterface $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return $this->created(
            new UserResource($user),
            'Account created successfully.'
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->success([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Logged in successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->noContent('Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->me($request->user());

        return $this->success(new UserResource($user));
    }

    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        $wasVerifiedNow = $this->authService->verifyEmail($id, $hash);

        $message = $wasVerifiedNow
            ? 'Email verified successfully.'
            : 'Email was already verified.';

        return $this->success(null, $message);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $this->authService->resendVerification($request->user());

        return $this->success(null, 'Verification email sent. Please check your inbox.');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->validated('email'));

        return $this->success(
            null,
            'If this email is registered, you will receive a password reset link shortly.'
        );
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->validated());

        return $this->success(null, 'Password reset successfully. Please log in with your new password.');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request->validated());

        return $this->success(null, 'Password changed successfully.');
    }

    public function socialToken(SocialTokenRequest $request, string $provider): JsonResponse
    {
        $result = $this->authService->handleSocialToken(
            $provider,
            $request->validated('access_token')
        );

        return $this->success([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Logged in with ' . ucfirst($provider) . ' successfully.');
    }
}
