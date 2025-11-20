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
            'gift' => true,
            'gift_send' => true,
            'pet_update' => true,
            'pet_create' => true,
            'pet_delete' => true,
        ],
        'channels' => [
            'sms' => false,
            'email' => true,
        ],
    ],
];
