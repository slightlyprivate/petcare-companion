<?php

return [

    'login' => [
        'email' => [
            'subject' => 'Successful Login to PetCare Companion',
            'intro'   => 'You successfully logged in at :time.',
        ],
        'sms' => [
            'body' => 'Login at :time. If this wasn\'t you, contact support.',
        ],
        'success' => 'Login successful.',
        'failure' => 'Login failed.',
    ],

    'otp' => [
        'email' => [
            'subject' => 'Your Authentication Code',
            'intro'   => 'Your one-time passcode is :code, valid for 5 minutes.',
        ],
        'sms' => [
            'body' => 'Your code is :code. Valid 5 minutes.',
        ],
        'success' => 'OTP sent successfully.',
        'failure' => 'Failed to send OTP.',
        'errors' => [
            'invalid' => 'The one-time passcode is invalid.',
            'expired' => 'The one-time passcode has expired.',
        ],
    ],

    'delete' => [
        'initiated' => [
            'email' => [
                'subject' => 'Data Deletion Started',
                'intro'   => 'We received your request to delete your data.',
                'outro'   => 'We will notify you upon completion.',
            ],
            'sms' => [
                'body' => 'Deletion request received. We\'ll notify you when complete.',
            ],
            'success' => 'Data deletion initiated successfully.',
            'failure' => 'Failed to initiate data deletion.',
        ],
        'completed' => [
            'email' => [
                'subject' => 'Data Deletion Complete',
                'intro'   => 'Your data has been permanently deleted.',
                'outro'   => 'Thank you for using PetCare Companion.',
            ],
            'sms' => [
                'body' => 'Your data has been deleted.',
            ],
            'success' => 'Data deletion completed successfully.',
            'failure' => 'Failed to complete data deletion.',
        ],
    ],

    'errors' => [
        'unauthorized' => 'You are not authorized to perform this action.',
    ],

];
