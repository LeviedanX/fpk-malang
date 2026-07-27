<?php

return [
    'admin_session' => [
        'absolute_lifetime' => (int) env('ADMIN_SESSION_ABSOLUTE_LIFETIME', 8 * 60 * 60),
        'rotation_interval' => (int) env('ADMIN_SESSION_ROTATION_INTERVAL', 15 * 60),
        'single_login' => env('ADMIN_SESSION_SINGLE_LOGIN', true),
        'bind_ip' => env('ADMIN_SESSION_BIND_IP', false),
    ],
];
