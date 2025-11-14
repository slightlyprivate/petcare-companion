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
    ],
    // Display texts for authentication errors and messages
    'otp' => [
        'invalid' => 'The provided one-time passcode is invalid.',
        'expired' => 'The one-time passcode has expired.',
        'sent' => 'A one-time passcode has been sent.',
    ],
    'unauthorized' => 'You are not authorized to perform this action.',
];
