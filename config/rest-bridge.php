<?php
return array(
    'encryption' => [
        'cipher' => env('REST_BRIDGE_CIPHER', 'AES-256-CBC'),
        'key'    => env('REST_BRIDGE_KEY')
    ],
    'url'        => env('REST_BRIDGE_URL')
);