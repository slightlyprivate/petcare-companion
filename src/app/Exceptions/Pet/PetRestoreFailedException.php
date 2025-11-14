<?php

namespace App\Exceptions\Pet;

use Exception;

/**
 * Exception thrown when pet restoration fails.
 */
class PetRestoreFailedException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('pet.restore.failure'));
    }
}
