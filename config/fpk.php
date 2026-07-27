<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'articles_public' => 9,
        'articles_admin' => 15,
        'agendas_admin' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    |
    | Central limits for image uploads. Sizes are in kilobytes. Directories are
    | relative to the "public" storage disk.
    |
    */
    'uploads' => [
        'disk' => 'public',
        'max_size' => 2048, // 2 MB
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'max_width' => 4000,
        'max_height' => 4000,
        'optimize_max_width' => 1600,
        'optimize_quality' => 80,
        'optimize_max_widths' => [
            'branding' => 512,
            'auth' => 1920,
            'gallery' => 1600,
            'profile' => 1920,
            'articles' => 1200,
            'agendas' => 1200,
        ],
        'directories' => [
            'branding' => 'branding',
            'auth' => 'auth',
            'gallery' => 'gallery',
            'profile' => 'profile',
            'articles' => 'articles',
            'agendas' => 'agendas',
            'management' => 'management',
            'audio' => 'audio',
        ],
        'audio_mimes' => ['mp3', 'wav', 'ogg', 'm4a'],
        'audio_max_size' => 10240, // 10 MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Public listings
    |--------------------------------------------------------------------------
    */
    'home' => [
        'latest_articles' => 3,
        'past_agendas' => 6,
    ],
];
