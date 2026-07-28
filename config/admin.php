<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Initial administrator provisioning
    |--------------------------------------------------------------------------
    |
    | These values are intentionally blank by default. Production seeding fails
    | closed until explicit credentials are supplied. Existing accounts are
    | never silently assigned a new password by the database seeder.
    |
    */
    'initial' => [
        'name' => env('ADMIN_NAME'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'activity_log_retention_days' => (int) env('ADMIN_ACTIVITY_LOG_RETENTION_DAYS', 180),
];
