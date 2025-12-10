<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\AuthVerificationRequest;
use App\Services\Auth\AuthUserService;
use Illuminate\Http\JsonResponse;

/**
 * Controller for handling authentication requests.
 *
 * @unauthenticated
 *
 * @group Authentication
 */
class AuthVerificationController extends AuthController
{
    /** @var AuthUserService */
    protected $userService;

    /**
     * Create a new controller instance.
     */
    public function __construct(AuthUserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Verify OTP and authenticate user.
     *
     * React client contract:
     * - Login: POST /api/auth/verify with {"email","code","device_name"?} returns
     *   {"token": "<string>", "token_type": "Bearer", "user": {...}}.
     * - Authenticated requests: send Authorization: Bearer <token> to all API routes.
     * - Logout: POST /api/auth/logout with that header revokes only the current token (204).
     * - Auth status: GET /api/auth/status with Authorization returns
     *   {"authenticated": true, "user": {...}}; 401 otherwise.
     */
    public function verifyOtp(AuthVerificationRequest $request): JsonResponse
    {
        $user = $this->userService->validate($request->email, $request->code);

        // Pure token-based authentication: do not perform session login or regenerate session.
        // Session-based flows have been removed to avoid issuing session cookies alongside API tokens.

        $deviceName = trim((string) $request->input('device_name', '')) ?: 'petcare-client';

        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
}
