<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | The old app stored every upload under public/uploads/store and served
    | it directly, so `public` is the default disk here (same as the old
    | config/filesystems.php). `local` (storage/app/private) is for
    | non-public files; `storage` is the stock Laravel public disk.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        // Old behaviour (atsskill-old config/filesystems.php): uploads live
        // in public/uploads/store and are served at /uploads/store/*.
        // App\Services\ImageService and the Media model rely on this.
        'public' => [
            'driver' => 'local',
            'root' => public_path('uploads/store'),
            'url' => '/uploads/store',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // Stock Laravel public disk (storage/app/public, via storage:link).
        'storage' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
