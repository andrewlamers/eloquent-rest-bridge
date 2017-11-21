<?php

namespace Andrewlamers\EloquentRestBridge;

use Andrewlamers\EloquentRestBridge\Connections\ConnectionFactory;
use Andrewlamers\EloquentRestBridge\Connections\MysqlConnection;
use Andrewlamers\EloquentRestBridge\Connections\PostgresConnection;
use Andrewlamers\EloquentRestBridge\Connections\SQLiteConnection;
use Andrewlamers\EloquentRestBridge\Connections\SqlServerConnection;
use Andrewlamers\EloquentRestBridge\Request\Handler;
use Illuminate\Contracts\Foundation\Application;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/rest-bridge.php';
        $this->mergeConfigFrom($configPath, 'rest-bridge');

        $this->app->singleton('restHandler', function (Application $app) {
            $config = $app['config']->get('rest-bridge');

            return new Handler($config);
        });

        // Add database driver.
        $this->app->resolving('db', function ($db) {
            $db->extend('rest', function ($config, $name) use ($db) {
                $factory = new ConnectionFactory($db, $config);

                return $factory->getProxyDriver();
            });
        });
    }

    public function boot()
    {

        $configPath = __DIR__ . '/../config/rest-bridge.php';
        $this->publishes([$configPath => config_path('rest-bridge.php')], 'config');

        if(config('rest-bridge.daemon.enabled')) {
            $routeConfig = ['namespace' => 'Andrewlamers\EloquentRestBridge\Controllers'];
            $this->app['router']->group($routeConfig, function ($router) {
                $router->post('/' . config('rest-bridge.daemon.route'), [
                    'uses' => 'RestController@handler',
                    'as'   => 'rest-bridge.handler'
                ]);
            });
        }
    }

    public function provides()
    {
        return array('restHandler');
    }
}