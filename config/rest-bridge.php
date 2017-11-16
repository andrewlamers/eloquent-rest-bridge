<?php
return array(
    'encryption' => [
        'cipher' => env('REST_BRIDGE_CIPHER', 'AES-256-CBC'),
        'key'    => env('REST_BRIDGE_KEY')
    ],
    'url'        => env('REST_BRIDGE_URL'),
    'log' => [
        'enabled' => false,
        'driver' => 'file',
        'path' => storage_path('logs/rest-bridge-sql.log')
    ]
);