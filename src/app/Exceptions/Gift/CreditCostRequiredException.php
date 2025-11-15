<?php

namespace App\Exceptions\Gift;

use Exception;

/**
 * Exception thrown when a gift cost in credits is required but not provided.
 */
class CreditCostRequiredException extends Exception
{
    protected $message = 'Gift cost in credits is required.';
}
