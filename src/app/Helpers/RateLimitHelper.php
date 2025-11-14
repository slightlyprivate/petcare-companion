<?php

namespace App\Helpers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate limiting configuration helper.
 *
 * Centralizes all rate limiting definitions for the application,
 * including authentication, resource operations, and webhooks.
 *
 * @group Helpers
 */
class RateLimitHelper
{
    /**
     * Configure all rate limiters for the application.
     */
    public static function configure(): void
    {
        self::configureAuthRateLimits();
        self::configurePetRateLimits();
        self::configureAppointmentRateLimits();
        self::configureGiftRateLimits();
        self::configureCreditRateLimits();
        self::configureAdminRateLimits();
        self::configureUserDataRateLimits();
        self::configureNotificationRateLimits();
        self::configureWebhookRateLimits();
    }

    /**
     * Configure authentication rate limiters.
     *
     * Prevents brute force attacks on OTP and verification endpoints.
     */
    private static function configureAuthRateLimits(): void
    {
        RateLimiter::for('auth.otp', function () {
            // Allow 5 OTP requests per hour per IP (prevent brute force)
            return Limit::perHour(5)->by(request()->ip());
        });

        RateLimiter::for('auth.verify', function () {
            // Allow 10 verification attempts per hour per IP
            return Limit::perHour(10)->by(request()->ip());
        });
    }

    /**
     * Configure pet operation rate limiters.
     */
    private static function configurePetRateLimits(): void
    {
        RateLimiter::for('pet.write', function () {
            // Allow 20 pet writes (create/update/delete) per hour per user
            return Limit::perHour(20)->by(request()->user()->id);
        });
    }

    /**
     * Configure appointment operation rate limiters.
     */
    private static function configureAppointmentRateLimits(): void
    {
        RateLimiter::for('appointment.write', function () {
            // Allow 30 appointment writes per hour per user
            return Limit::perHour(30)->by(request()->user()->id);
        });
    }

    /**
     * Configure gift operation rate limiters.
     *
     * Uses strict limits to prevent spam and abuse.
     */
    private static function configureGiftRateLimits(): void
    {
        RateLimiter::for('gift.write', function () {
            // Allow 5 gifts per hour per user
            return Limit::perHour(5)->by(request()->user()->id);
        });
    }

    /**
     * Configure credit operation rate limiters.
     *
     * Controls the rate of credit purchases to prevent abuse.
     */
    private static function configureCreditRateLimits(): void
    {
        RateLimiter::for('credit.write', function () {
            // Allow 10 credit purchases per hour per user
            return Limit::perHour(10)->by(request()->user()->id);
        });
    }

    /**
     * Configure admin operation rate limiters.
     *
     * Controls the rate of admin-only operations like gift type management.
     */
    private static function configureAdminRateLimits(): void
    {
        RateLimiter::for('admin.write', function () {
            // Allow 50 admin writes per hour per admin user
            return Limit::perHour(50)->by(request()->user()->id);
        });
    }

    /**
     * Configure user data operation rate limiters.
     *
     * Uses very strict limits for destructive operations like export and deletion.
     */
    private static function configureUserDataRateLimits(): void
    {
        RateLimiter::for('user.data.export', function () {
            // Allow 2 data exports per day per user
            return Limit::perDay(2)->by(request()->user()->id);
        });

        RateLimiter::for('user.data.delete', function () {
            // Allow 1 deletion request per day per user (destructive action)
            return Limit::perDay(1)->by(request()->user()->id);
        });
    }

    /**
     * Configure notification preference rate limiters.
     */
    private static function configureNotificationRateLimits(): void
    {
        RateLimiter::for('notification.write', function () {
            // Allow 10 preference updates per hour per user
            return Limit::perHour(10)->by(request()->user()->id);
        });
    }

    /**
     * Configure webhook rate limiters.
     *
     * Allows reasonable rates for legitimate external services.
     */
    private static function configureWebhookRateLimits(): void
    {
        RateLimiter::for('webhook.stripe', function () {
            // Allow 100 webhook requests per minute per IP
            // Stripe webhooks have their own signature verification
            return Limit::perMinute(100)->by(request()->ip());
        });
    }
}
