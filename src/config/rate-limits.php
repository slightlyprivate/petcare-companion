<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Define rate limits for various operations. Limits are per hour unless
    | specified otherwise. In development/testing, higher limits are used.
    |
    */

    'auth' => [
        'otp' => [
            'production' => 5,
            'development' => 100,
        ],
        'verify' => [
            'production' => 10,
            'development' => 100,
        ],
    ],

    'pet' => [
        'write' => [
            'production' => 20,
            'development' => 100,
        ],
    ],

    'appointment' => [
        'write' => [
            'production' => 30,
            'development' => 100,
        ],
    ],

    'gift' => [
        'write' => [
            'production' => 5,
            'development' => 50,
        ],
    ],

    'credit' => [
        'write' => [
            'production' => 10,
            'development' => 50,
        ],
    ],

    'admin' => [
        'write' => [
            'production' => 50,
            'development' => 200,
        ],
    ],

    'user_data' => [
        'export' => [
            'production' => 2, // per day
            'development' => 10,
        ],
        'delete' => [
            'production' => 1, // per day
            'development' => 5,
        ],
    ],

    'notification' => [
        'write' => [
            'production' => 10,
            'development' => 50,
        ],
    ],

    'webhook' => [
        'stripe' => [
            'production' => 100, // per minute
            'development' => 500,
        ],
    ],

    'pet-care' => [
        'default' => [
            'production' => 120, // per hour for activities, routines, etc
            'development' => 500,
        ],
    ],
];
