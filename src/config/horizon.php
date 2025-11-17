<?php

return [
    'domain' => null,

    // Path the Horizon dashboard will be available at.
    'path' => 'horizon',

    // Redis connection Horizon should use (must be a key in database.redis)
    'use' => env('HORIZON_REDIS_CONNECTION', 'default'),

    // Redis connection name for Horizon metrics
    'prefix' => env('HORIZON_PREFIX', 'horizon-'),

    'middleware' => ['web'],

    'waits' => [
        'redis:default' => 60,
    ],

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 43200,
    ],

    'fast_termination' => false,

    'memory_limit' => 128,

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 10,
                'tries' => 3,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 1,
                'tries' => 3,
            ],
        ],
    ],
];
