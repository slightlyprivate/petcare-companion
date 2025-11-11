<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when OTP verification fails.
 */
class OtpVerificationFailedException extends Exception
{
    // Exception for OTP verification failures.
    protected $message = 'Invalid or expired code.';

    // Status code for HTTP response.
    protected $code = 401;

    /**
     * Construct the exception.
     */
    public function __construct()
    {
        parent::__construct($this->message);
    }
}
