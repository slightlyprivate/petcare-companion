<?php

namespace App\Support\Messages;

/**
 * Centralized messages for the application.
 */
class AuthMessages
{
    /**
     * Message indicating invalid one-time password.
     */
    public static function otpInvalid(): string
    {
        return __('auth.otp.invalid');
    }

    /**
     * Message indicating expired one-time password.
     */
    public static function otpExpired(): string
    {
        return __('auth.otp.expired');
    }

    /**
     * Message indicating that a one-time password has been sent.
     */
    public static function otpSent(): string
    {
        return __('auth.otp.sent');
    }

    /**
     * Message indicating unauthorized action.
     */
    public static function unauthorizedAction(): string
    {
        return __('auth.unauthorized');
    }
}
