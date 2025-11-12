<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\AuthRequest;
use App\Services\Auth\AuthUserService;

/**
 * Controller for handling authentication requests.
 * 
 * @group Authentication
 */
class AuthRequestController extends AuthController
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
     * Handle OTP request.
     */
    public function requestOtp(AuthRequest $request)
    {
        $email = $request->email;
        $this->userService->processAuthenticationRequest($email);

        return response()->json(['message' => 'OTP sent']);
    }
}
