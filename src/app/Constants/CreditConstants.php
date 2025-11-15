<?php

namespace App\Constants;

/**
 * Credit system constants.
 *
 * This file defines the standardized conversion rates for the credit system.
 * All credits-to-cents conversions should use these constants for consistency.
 *
 * @group Constants
 */
class CreditConstants
{
    /**
     * The credit to dollar ratio.
     *
     * Standard: 5 credits = $1.00 (100 cents)
     * This means 1 credit = 20 cents
     *
     * This constant is used throughout the application for:
     * - Converting credits to stripe checkout amounts
     * - Displaying total gift values to users
     * - Calculating donation totals in public directory views
     */
    public const int CREDITS_PER_DOLLAR = 5;

    /**
     * Get the dollar amount for a given number of credits.
     *
     * @param  int  $credits  The number of credits to convert
     * @return float The equivalent dollar amount (e.g., 5 credits = 1.00)
     */
    public static function toDollars(int $credits): float
    {
        return $credits / self::CREDITS_PER_DOLLAR;
    }

    /**
     * Get the cent amount for a given number of credits.
     *
     * @param  int  $credits  The number of credits to convert
     * @return int The equivalent amount in cents (e.g., 5 credits = 100 cents)
     */
    public static function toCents(int $credits): int
    {
        // 1 dollar = 100 cents; cents per credit = 100 / CREDITS_PER_DOLLAR
        return (int) round($credits * (100 / self::CREDITS_PER_DOLLAR));
    }

    /**
     * Get the number of credits for a given dollar amount.
     *
     * @param  float  $dollars  The dollar amount to convert
     * @return int The equivalent number of credits
     */
    public static function fromDollars(float $dollars): int
    {
        return (int) round($dollars * self::CREDITS_PER_DOLLAR);
    }

    /**
     * Get the number of credits for a given cent amount.
     *
     * @param  int  $cents  The cent amount to convert
     * @return int The equivalent number of credits
     */
    public static function fromCents(int $cents): int
    {
        // credits per cent = CREDITS_PER_DOLLAR / 100
        return (int) round($cents * (self::CREDITS_PER_DOLLAR / 100));
    }
}
