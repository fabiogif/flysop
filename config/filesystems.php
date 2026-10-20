<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    // FILESYSTEM_DRIVER era o nome da variável no Laravel 7; o .env deste projeto usa
    // FILESYSTEM_DISK (Laravel 8+). Com o nome antigo, a var nunca era lida e o disco
    // caía sempre no fallback 's3' sem credenciais — todo upload de anexo falhava.
    'default' => env('FILESYSTEM_DISK', 'local'),
    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],
        // Compatível com S3 e com o Tigris (Fly.io): os nomes do Tigris (AWS_REGION,
        // AWS_ENDPOINT_URL_S3, BUCKET_NAME) têm prioridade sobre os nomes antigos da AWS.
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_REGION', env('AWS_DEFAULT_REGION', 'auto')),
            'bucket' => env('BUCKET_NAME', env('AWS_BUCKET')),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT_URL_S3', env('AWS_ENDPOINT')),
            'use_path_style_endpoint' => (bool) env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],
    ],
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];