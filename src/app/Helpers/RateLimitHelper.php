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
        self::configurePetCareRateLimits();
    }

    /**
     * Configure authentication rate limiters.
     *
     * Prevents brute force attacks on OTP and verification endpoints.
     */
    private static function configureAuthRateLimits(): void
    {
        $env = app()->environment(['local', 'testing']) ? 'development' : 'production';

        RateLimiter::for('auth.otp', function () use ($env) {
            $limit = config('rate-limits.auth.otp.'.$env);

            return Limit::perHour($limit)->by(request()->ip());
        });

        RateLimiter::for('auth.verify', function () use ($env) {
            $limit = config('rate-limits.auth.verify.'.$env);

            return Limit::perHour($limit)->by(request()->ip());
        });
    }

    /**
     * Configure pet operation rate limiters.
     */
    private static function configurePetRateLimits(): void
    {
        $env = app()->environment(['local', 'testing']) ? 'development' : 'production';

        RateLimiter::for('pet.write', function () use ($env) {
            $limit = config('rate-limits.pet.write.'.$env);

            return Limit::perHour($limit)->by(request()->user()->id);
        });
    }

    /**
     * Configure appointment operation rate limiters.
     */
    private static function configureAppointmentRateLimits(): void
    {
        $env = app()->environment(['local', 'testing']) ? 'development' : 'production';

        RateLimiter::for('appointment.write', function () use ($env) {
            $limit = config('rate-limits.appointment.write.'.$env);

            return Limit::perHour($limit)->by(request()->user()->id);
        });
    }

    /**
     * Configure gift operation rate limiters.
     *
     * Uses strict limits to prevent spam and abuse.
     */
    private static function configureGiftRateLimits(): void
    {
        $env = app()->environment(['local', 'testing']) ? 'development' : 'production';

        RateLimiter::for('gift.write', function () use ($env) {
            $limit = config('rate-limits.gift.write.'.$env);

            return Limit::perHour($limit)->by(request()->user()->id);
        });
    }

    /**
     * Configure credit operation rate limiters.
     *
     * Controls the rate of credit purchases to prevent abuse.
     */
    private static function configureCreditRateLimits(): void
    {
        $env = app()->environment(['local', 'testing']) ? 'development' : 'production';

        RateLimiter::for('credit.write', function () use ($env) {
            $limit = config('rate-limits.credit.write.'.$env);

            return Limit::perHour($limit)->by(request()->user()->id);
        });
    }

    /**
     * Configure admin operation rate limiters.
     *
     * Controls the rate of admin-only operations like gift type management.
     */
    private static function configureAdminRateLimits(): void
    {
        $env = app()->environment(['local', 'testing']) ? 'development' : 'production';

        RateLimiter::for('admin.write', function () use ($env) {
            $limit = config('rate-limits.admin.write.'.$env);

            return Limit::perHour($limit)->by(request()->user()->id);
        });
    }

    /**
     * Configure user data operation rate limiters.
     *
     * Uses very strict limits for destructive operations like export and deletion.
     */
    private static function configureUserDataRateLimits(): void
    {
        $env = app()->environment(['local', 'testing']) ? 'development' : 'production';

        RateLimiter::for('user.data.export', function () use ($env) {
            $limit = config('rate-limits.user_data.export.'.$env);

            return Limit::perDay($limit)->by(request()->user()->id);
        });

        RateLimiter::for('user.data.delete', function () use ($env) {
            $limit = config('rate-limits.user_data.delete.'.$env);

            return Limit::perDay($limit)->by(request()->user()->id);
        });
    }

    /**
     * Configure notification preference rate limiters.
     */
    private static function configureNotificationRateLimits(): void
    {
        $env = app()->environment(['local', 'testing']) ? 'development' : 'production';

        RateLimiter::for('notification.write', function () use ($env) {
            $limit = config('rate-limits.notification.write.'.$env);

            return Limit::perHour($limit)->by(request()->user()->id);
        });
    }

    /**
     * Configure webhook rate limiters.
     *
     * Allows reasonable rates for legitimate external services.
     */
    private static function configureWebhookRateLimits(): void
    {
        $env = app()->environment(['local', 'testing']) ? 'development' : 'production';

        RateLimiter::for('webhook.stripe', function () use ($env) {
            $limit = config('rate-limits.webhook.stripe.'.$env);

            return Limit::perMinute($limit)->by(request()->ip());
        });
    }

    /**
     * Configure pet-care rate limiters.
     *
     * For future pivot endpoints like activities, routines, caregiver operations.
     */
    private static function configurePetCareRateLimits(): void
    {
        $env = app()->environment(['local', 'testing']) ? 'development' : 'production';

        RateLimiter::for('pet-care', function () use ($env) {
            $limit = config('rate-limits.pet-care.default.'.$env);

            return Limit::perHour($limit)->by(request()->user()->id);
        });
    }
}
