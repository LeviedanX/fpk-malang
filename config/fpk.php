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
        'optimize_max_width' => 1600, // downscale target when an image driver is available
        'directories' => [
            'branding' => 'branding',
            'profile' => 'profile',
            'articles' => 'articles',
            'agendas' => 'agendas',
            'management' => 'management',
        ],
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
