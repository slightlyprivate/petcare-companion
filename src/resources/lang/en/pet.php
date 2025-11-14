<?php

return [
    // Notifications Texts for Pet Updates
    'notify' => [
        // Pet Created Notification
        'created' => [
            'subject' => 'Your pet :pet_name has been created',
            'message' => 'Your pet :pet_name has been successfully added to your account.',
            'sms' => 'Your pet :pet_name has been added. Check your account for more info.',
        ],
        // Pet Updated Notification
        'updated' => [
            'subject' => 'Your pet :pet_name has been updated',
            'message' => 'The details for your pet :pet_name have been successfully updated.',
            'sms' => 'Your pet :pet_name details have been updated. Check your account for more info.',
        ],
        // Pet Deleted Notification
        'deleted' => [
            'subject' => 'Your pet :pet_name has been deleted',
            'message' => 'Your pet :pet_name has been removed from your account.',
            'sms' => 'Your pet :pet_name has been deleted. Contact support if this was a mistake.',
        ],
    ],
    // Display texts for pet restoration actions
    'restore' => [
        'success' => 'The pet has been successfully restored.',
        'failure' => 'Failed to restore the pet. Please try again.',
    ],
];
