# Eloquent Rest Bridge
A connection driver for the illuminate database manager that forwards sql statements over http to a service running on the target host machine.

This significantly reduces the transfer size of traditional connections made over a tcp connection.

The host machine will execute the sql statements and return the results back in json format that gets parsed back into the database manager as if it was a direct database connection.

SSL is supported but not required as all request and response bodies are encrypted by AES with a pre-shared key.

This is only really useful for retrieving results through a limited bandwidth connection, or through firewalls.
The reduced transfer size allows response times to be much quicker than tunneling raw database commands through a port over SSH.

## Installation

    composer require andrewlamers/eloquent-rest-bridge


### Laravel Service Provider

Include this in your config/app.php list of service providers.

    Andrewlamers\EloquentRestBridge\ServiceProvider::class

### Laravel configuration file
There is an included configuration file for laravel that allows you to modify configuration of the rest bridge.

To move the configuration file to your projects config/ folder use the following command.

    php artisan vendor:publish --provider="Andrewlamers\EloquentRestBridge\ServiceProvider"

### Configuration Options

    return array(
        'encryption' => [

            /*
             * Available ciphers are AES-256-CBC and AES-128-CBC
             */
            'cipher' => env('REST_BRIDGE_CIPHER', 'AES-256-CBC'),

            /*
             * The key must match the cipher. AES-256 must be a 32 bit key, and AES-128 must be a 16 byte key
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
            'base_path' => storage_path('logs/')
        ],
        'daemon' => [
            'enabled' => false,
            'route' => '_rest_bridge/handler'
        ]
    );

### Example database configuration
In your database configuration the 'rest' driver will be available. It needs to know the connection you want to use.

    'my-db-connection' => [
        'driver'   => 'sqlsrv',
        'host'     => env('SQLSRV_HOST', ''),
        'database' => env('SQLSRV_DATABASE', ''),
        'username' => env('SQLSRV_USERNAME', ''),
        'password' => env('SQLSRV_PASSWORD', ''),
        'charset'  => 'utf8',
        'prefix'   => '',
    ],
    'my-rest-connection' => [
        'driver' => 'rest',
        'connection' => 'my-db-connection'
    ]

The rest driver will forward the configuration options to the daemon listening for commands.