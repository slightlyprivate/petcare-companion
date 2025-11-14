<?php

namespace App\Services\Auth;

use App\Helpers\NotificationHelper;
use App\Models\Otp;
use App\Models\User;
use App\Notifications\Auth\OtpSentNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Service for handling OTP operations.
 *
 * @group Authentication
 */
class OtpService
{
    /**
     * Create a new OTP record.
     */
    public function create(array $data): Otp
    {
        return Otp::create($data);
    }

    /**
     * Generate and store an OTP code for a given email.
     */
    public function generateCode(string $email): Otp
    {
        // Check if there's already a valid OTP for this email
        $existingOtp = Otp::where('email', $email)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingOtp) {
            return $existingOtp;
        }

        $code = random_int(100000, 999999);
        $otp = Otp::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        return $otp;
    }

    /**
     * Send OTP code via email.
     */
    public function sendOtpEmail(string $email, string $code): void
    {
        // Find or create a user for this email to send notifications
        $user = User::firstOrCreate(
            ['email' => $email],
            ['role' => 'user']
        );

        // Check if user has OTP notifications enabled before sending
        if (NotificationHelper::isNotificationEnabled($user, 'otp')) {
            Notification::send($user, new OtpSentNotification($code, $email));
        }
    }

    /**
     * Validate an OTP code for a given email.
     */
    public function validate(string $email, string $code): ?Otp
    {
        return Otp::where('email', $email)
            ->where('code', $code)
            ->where('expires_at', '>=', now())
            ->first();
    }
}
