<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Activity Log Settings
    |--------------------------------------------------------------------------
    |
    | This configuration file controls how the Spatie Activity Log package
    | captures and stores audit events for sensitive and user-triggered
    | operations (create, update, delete, donate).
    |
    */

    'database_connection' => null,
    'table_name' => 'activity_log',
    'model' => \Spatie\Activitylog\Models\Activity::class,

    /*
    |--------------------------------------------------------------------------
    | Causer Configuration
    |--------------------------------------------------------------------------
    |
    | Captures the authenticated user performing the action for audit trail.
    | This model should implement the Authenticatable contract.
    |
    */

    'default_causer_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Activity Log Events
    |--------------------------------------------------------------------------
    |
    | Define which events should trigger activity logging. Built-in events:
    | - created: Model was inserted into database
    | - updated: Model was modified
    | - deleted: Model was removed
    |
    | Custom events can be logged via:
    |   activity()->event('gift_completed')->log('...');
    |
    */

    'events' => [
        'created',
        'updated',
        'deleted',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Attributes
    |--------------------------------------------------------------------------
    |
    | Attributes listed here will not be included in the activity log.
    | Use this to exclude timestamps, internal flags, or sensitive data.
    |
    */

    'ignore_user_agents' => [
        'bot',
        'crawler',
        'spider',
        'curl',
        'wget',
        'python',
    ],

    /*
    |--------------------------------------------------------------------------
    | Attribute Masking
    |--------------------------------------------------------------------------
    |
    | Use regex patterns to mask sensitive attributes in logs.
    | Example: 'password' => '****' will redact passwords in the activity log.
    |
    */

    'masked_attributes' => [
        'password',
        'secret',
        'token',
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Activity Middleware
    |--------------------------------------------------------------------------
    |
    | Enable this to automatically capture the authenticated user for all
    | activities. Should be registered in your HTTP middleware stack.
    |
    */

    'enable_logging' => true,

];
