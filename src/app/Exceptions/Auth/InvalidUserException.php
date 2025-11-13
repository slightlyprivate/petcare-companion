<?php

namespace App\Exceptions\Auth;

use Exception;

/**
 * Exception thrown when a user is invalid.
 */
class InvalidUserException extends Exception
{
    protected $message = 'The specified user is invalid.';
}
