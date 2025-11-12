<?php

namespace App\Enums;

/**
 * User roles within the application.
 * 
 * @group Enums
 */
enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';

    /**
     * Determine if the role represents an administrator.
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }
}
