<?php

return [

    'created' => [
        'email' => [
            'subject' => 'New Pet Created: :pet_name',
            'intro' => ':pet_name has been added to your account.',
        ],
        'sms' => [
            'body' => ':pet_name has been added to your account.',
        ],
        'success' => 'The pet has been created successfully.',
        'failure' => 'Unable to create the pet.',
    ],

    'updated' => [
        'email' => [
            'subject' => 'Pet Updated: :pet_name',
            'intro' => 'Details for :pet_name have been updated.',
        ],
        'sms' => [
            'body' => ':pet_name has been updated.',
        ],
        'success' => 'The pet has been updated successfully.',
        'failure' => 'Unable to update the pet.',
    ],

    'deleted' => [
        'email' => [
            'subject' => 'Pet Deleted: :pet_name',
            'intro' => ':pet_name has been removed from your account.',
        ],
        'sms' => [
            'body' => ':pet_name has been deleted.',
        ],
        'success' => 'The pet has been deleted successfully.',
        'failure' => 'Unable to delete the pet.',
    ],

    'restore' => [
        'email' => [
            'subject' => 'Pet Restored: :pet_name',
            'intro' => ':pet_name has been restored to your account.',
        ],
        'sms' => [
            'body' => ':pet_name has been restored.',
        ],
        'success' => 'The pet has been restored successfully.',
        'failure' => 'Unable to restore the pet.',
    ],

];
