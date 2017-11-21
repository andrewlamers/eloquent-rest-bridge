<?php

namespace Andrewlamers\EloquentRestBridge\Connections;

use Andrewlamers\EloquentRestBridge\Request\Request;
use \Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Closure;

trait ConnectionTrait
{
    protected $http;
    protected $connector;
    protected $lastInsertId;

    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->http = new Request($config);
        $this->config = $config;

        parent::__construct($pdo, $database, $tablePrefix, $config);
    }

    public function sendRequest($query, $bindings, $type)
    {
        $start = microtime(true);

        $data = [
            'query'    => $query,
            'bindings' => $bindings,
            'config'   => $this->config,
            'type'     => $type
        ];

        $data = $this->http->request($data);

        if (count($data['results']) < 1) {
            return NULL;
        }

        $this->logQuery($query, $bindings, $this->getElapsedTime($start));

        return $data['results'];
    }

    public function select($query, $bindings = [], $useReadPdo = TRUE)
    {
        //parent::select($query, $bindings, $useReadPdo);
        return $this->sendRequest($query, $bindings, "select");
    }

    public function update($query, $bindings = [])
    {
        //parent::update($query, $bindings);
        return $this->sendRequest($query, $bindings, "update");
    }

    public function delete($query, $bindings = [])
    {
        //parent::delete($query, $bindings);
        return $this->sendRequest($query, $bindings, "delete");
    }

    public function insert($query, $bindings = [])
    {
        //parent::insert($query, $bindings);
        $response = $this->sendRequest($query, $bindings, "insert");
        $this->setLastInsertId(array_get($response, 'lastInsertId', NULL));
    }

    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }

    public function setLastInsertId($id)
    {
        if (is_numeric($id)) {
            $id = (int)$id;
        }
        $this->lastInsertId = $id;
    }
}