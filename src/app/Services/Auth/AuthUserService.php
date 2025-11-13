<?php

namespace App\Services\Auth;

use App\Exceptions\OtpVerificationFailedException;
use App\Helpers\NotificationHelper;
use App\Models\User;
use App\Notifications\LoginSuccessNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Service for handling user authentication via OTP.
 *
 * @group Authentication
 */
class AuthUserService
{
    /** @var OtpService */
    protected $otpService;

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $this->otpService = new OtpService;
    }

    /**
     * Process authentication request by sending an OTP to the user's email.
     */
    public function processAuthenticationRequest(string $email): void
    {
        // Logic to process authentication request, e.g., send OTP
        $otp = $this->otpService->generateCode($email);
        // Send OTP via email (implementation not shown)
        $this->otpService->sendOtpEmail($email, $otp->code);
    }

    /**
     * Validate user by email and OTP code.
     */
    public function validate(string $email, string $code): array
    {
        // Validation logic here
        $otpService = new OtpService;
        $otp = $otpService->validate($email, $code);

        if (! $otp) {
            throw new OtpVerificationFailedException;
        }

        $user = $this->create($email);
        $token = $user->createToken('auth_token')->plainTextToken;

        // Send login success notification if enabled
        if (NotificationHelper::isNotificationEnabled($user, 'login')) {
            Notification::send($user, new LoginSuccessNotification($user));
        }

        return [$user, $token];
    }

    /**
     * Create or retrieve a user by email.
     */
    public function create(string $email): User
    {
        return User::firstOrCreate(['email' => $email]);
    }
}
