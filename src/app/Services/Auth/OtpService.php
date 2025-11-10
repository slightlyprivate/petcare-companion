<?php

namespace App\Services\Auth;

use App\Models\Otp;

/**
 * Service for handling OTP operations.
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
        // Send email (replace with notification or mailable)
        \Illuminate\Support\Facades\Mail::raw("Your OTP is $code", function ($message) use ($email) {
            $message->to($email)->subject('Your OTP Code');
        });
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
