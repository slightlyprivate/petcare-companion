<?php

namespace App\Exceptions\Auth;

use Exception;

/**
 * Exception thrown when an unauthorized access is attempted.
 */
class UnauthorizedException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('auth.unauthorized'));
    }
}
