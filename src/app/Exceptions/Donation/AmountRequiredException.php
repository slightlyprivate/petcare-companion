<?php

namespace App\Exceptions\Donation;

use Exception;

/**
 * Exception thrown when a donation amount is required but not provided.
 */
class AmountRequiredException extends Exception
{
    protected $message = 'Donation amount is required.';
}
