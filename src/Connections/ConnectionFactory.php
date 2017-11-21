<?php

namespace Andrewlamers\EloquentRestBridge\Connections;

use Illuminate\Support\Arr;

class ConnectionFactory
{
    protected $db;
    protected $config;
    protected $proxyConnection;

    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config = $config;
        $this->getProxyConnection();
    }

    public function getConnection()
    {
        return $this->getProxyDriver();
    }

    public function getProxyConnection()
    {
        $this->proxyConnection = $this->db->connection($this->getConfig('connection'));
    }

    public function getProxyDriver()
    {
        $config = $this->proxyConnection->getConfig();
        $this->config['proxied'] = $config;
        $database = $this->proxyConnection->getDatabaseName();
        $pdo = new \PDO('sqlite::memory:');
        $prefix = $this->proxyConnection->getTablePrefix();

        switch ($config['driver']) {
            case "mysql":
                return new MysqlConnection($pdo, $database, $prefix, $this->config);
                break;
            case "sqlsrv":
                return new SqlServerConnection($pdo, $database, $prefix, $this->config);
                break;
            case "postgres":
                return new PostgresConnection($pdo, $database, $prefix, $this->config);
                break;
            case "sqlite":
                return new SqlServerConnection($pdo, $database, $prefix, $this->config);
                break;
        }

        return NULL;
    }

    public function getConfig($key)
    {
        return Arr::get($this->config, $key, NULL);
    }
}