<?php

namespace App\Services\User;

/**
 * Service for managing user data operations.
 */
class UserService
{
    /**
     * Export user data for the given user ID.
     */
    public function exportData(int $userId): array
    {
        $output = [];

        // TODO: Logic to gather and return user data for export
        // Will queue a job that will generate an archive to
        // storage and notify user with download link via email
        return $output;
    }

    public function deleteData(int $userId): void
    {
        // TODO: Logic to delete user data
        // Will queue a job that will delete user data
        // and notify user via email upon completion
    }
}
