<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\AuthVerificationRequest;
use App\Services\Auth\AuthUserService;

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
     */
    public function verifyOtp(AuthVerificationRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $this->userService->validate($request->email, $request->code);

        // Log the user in using Laravel's session-based auth (Sanctum SPA mode)
        auth()->login($user);

        // Regenerate session to prevent session fixation attacks (if session is available)
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json(['user' => $user]);
    }
}
