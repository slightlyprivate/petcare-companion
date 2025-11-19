<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Export Configuration
    |--------------------------------------------------------------------------
    |
    | Controls the lifetime of signed download links for user data exports.
    | The value is expressed in hours to allow fine-grained tuning between
    | environments without requiring code changes.
    |
    */

    'link_ttl_hours' => env('USER_EXPORT_LINK_TTL_HOURS', 48),
];
