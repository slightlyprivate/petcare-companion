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
     * The value of 1 credit in cents (USD).
     *
     * Standard: 1 credit = $1.00 = 100 cents
     *
     * This constant is used throughout the application for:
     * - Converting credits to stripe checkout amounts
     * - Displaying total gift values to users
     * - Calculating donation totals in public directory views
     */
    public const int CREDIT_VALUE_IN_CENTS = 100;

    /**
     * Get the dollar amount for a given number of credits.
     *
     * @param  int  $credits  The number of credits to convert
     * @return float The equivalent dollar amount
     */
    public static function toDollars(int $credits): float
    {
        return $credits * self::CREDIT_VALUE_IN_CENTS / 100;
    }

    /**
     * Get the cent amount for a given number of credits.
     *
     * @param  int  $credits  The number of credits to convert
     * @return int The equivalent amount in cents
     */
    public static function toCents(int $credits): int
    {
        return $credits * self::CREDIT_VALUE_IN_CENTS;
    }
}
