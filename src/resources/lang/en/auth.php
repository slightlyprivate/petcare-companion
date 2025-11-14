<?php

return [
    // Notifications Texts for Authentication Events
    'notify' => [
        // Successful Login Notification
        'login' => [
            'subject' => 'Successful Login to PetCare Companion',
            'message' => 'You have successfully logged in to PetCare Companion at :time.',
            'sms' => 'Welcome back to PetCare Companion! You successfully logged in at :time. If this wasn\'t you, contact support.',
        ],
        // OTP Sent Notification
        'otp' => [
            'subject' => 'Your PetCare Companion Authentication Code',
            'message' => 'Your one-time passcode is :code. It is valid for 5 minutes.',
            'sms' => 'Your PetCare Companion code is :code. Valid for 5 minutes.',
        ],
        // Data Deletion Initiated Notification
        'delete-data-initiated' => [
            'subject' => 'Your PetCare Companion Data Deletion Request Initiated',
            'message' => 'We have received your data deletion request. We are processing it and will notify you once it is complete.',
            'sms' => 'Your PetCare Companion data deletion request has been received. We will notify you once it is complete.',
        ],
        // Data Deletion Confirmation Notification
        'delete-data' => [
            'subject' => 'Your PetCare Companion Data Deletion Request',
            'message' => 'Your data deletion request has been processed. Your data has been permanently removed from our system.',
            'sms' => 'Your PetCare Companion data deletion request has been completed. Your data is no longer stored with us.',
        ],
    ],
    // Display texts for authentication errors and messages
    'otp' => [
        'invalid' => 'The provided one-time passcode is invalid.',
        'expired' => 'The one-time passcode has expired.',
        'sent' => 'A one-time passcode has been sent.',
    ],
    'unauthorized' => 'You are not authorized to perform this action.',
];
