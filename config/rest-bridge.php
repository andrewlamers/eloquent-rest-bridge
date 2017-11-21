<?php
return array(
    'encryption' => [

        /*
         * Available ciphers are AES-256-CBC and AES-128-CBC
         */
        'cipher' => env('REST_BRIDGE_CIPHER', 'AES-256-CBC'),

        /*
         * The key must match the cipher. AES-256 must be a 32 byte key, and AES-128 must be a 16 byte key
         * You can use the php artisan key:generate --show command to generate a key. Do not use the same key as your application.
         * The encrypter will look for the key in the environment value with the key name specified here.
        */
        'key'    => 'REST_BRIDGE_KEY'
    ],
    /*
     * The url that is listening for sql commands to return results from.
     */
    'url'        => env('REST_BRIDGE_URL'),
    'log' => [
        /*
        * Enable logging of requests, responses, and sql commands.
        */
        'enabled' => false,
        /*
         * Base path for the log files. It will create 3 log files for requests, responses, and sql queries.
         */
        'base_path' => storage_path('logs/'),
        'queries' => [
            'select',
            'update',
            'delete'
        ]
    ],
    'daemon' => [
        'enabled' => false,
        'route' => '_rest_bridge/handler'
    ],
    'request_metadata' => function() {
        
    }
);