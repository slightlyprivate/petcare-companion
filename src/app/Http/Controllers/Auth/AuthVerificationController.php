<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\AuthVerificationRequest;
use App\Services\Auth\AuthUserService;

/**
 * Controller for handling authentication requests.
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
        [$user, $token] = $this->userService->validate($request->email, $request->code);

        return response()->json(['token' => $token, 'user' => $user]);
    }
}
