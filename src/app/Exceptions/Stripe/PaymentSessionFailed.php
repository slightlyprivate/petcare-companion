<?php

namespace App\Exceptions\Stripe;

use Exception;

/**
 * Exception thrown when a Stripe payment session fails to be created.
 */
class PaymentSessionFailed extends Exception
{
    // Custom exception for Stripe payment session failures
    protected $message = 'Failed to create payment session.';
}
