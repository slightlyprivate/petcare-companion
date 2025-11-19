<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Preference Defaults
    |--------------------------------------------------------------------------
    |
    | Defaults are split between notification toggles and outbound channels so
    | the application can apply consistent behaviour when a user has not yet
    | customised their settings.
    |
    */

    'defaults' => [
        'notifications' => [
            'otp' => true,
            'login' => true,
            'gift' => false,
            'pet_update' => false,
            'pet_create' => false,
            'pet_delete' => false,
        ],
        'channels' => [
            'sms' => false,
            'email' => true,
        ],
    ],
];
