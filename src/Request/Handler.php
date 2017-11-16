<?php

namespace Andrewlamers\EloquentRestBridge\Request;

use Andrewlamers\EloquentRestBridge\Logger\Log;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class Handler
{
    public function __construct($config)
    {
        $this->response = Response::getFacadeRoot();
        $this->request = HttpRequest::capture();
        $this->connectionFactory = new ConnectionFactory(app());
        $this->config = $config;
        $this->appConfig = app('config');
        $this->log = new Log();
    }

    public function getPdo($config)
    {
        return $this->connectionFactory->make($config['proxied'], '__rest_bridge_temp');
    }

    public function response()
    {
        $response = [
            'results'   => NULL,
            'exception' => NULL
        ];

        $req = new Request();

        try {
            $options = collect($req->parsePayload($this->request->getContent()));
            $config = $options->get('config');
            $pdo = $this->getPdo($config);
            $query = $options->get('query');
            $bindings = $options->get('bindings');
            $type = $options->get('type');
            $results = NULL;

            $this->log->query(['query' => $query, 'bindings' => $bindings, 'type' => $type, 'config' => array_except($config, ['proxied.password', 'password'])]);

            switch ($type) {
                case "select":
                    $results = $pdo->select($query, $bindings);
                    break;
                case "insert":
                    $pdo->insert($query, $bindings);
                    $id = $pdo->getPdo()->lastInsertId();
                    $results = [
                        'lastInsertId' => $id
                    ];
                    break;
                case "update":
                    $results = $pdo->update($query, $bindings);
                    break;
                case "delete":
                    $results = $pdo->delete($query, $bindings);
                    break;
            }

            $response['results'] = $results;

        } catch (\Exception $e) {
            $response['exception'] = [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'code'    => $e->getCode()
            ];
        }

        try {
            $prepared = $req->preparePayload($response);

            return $this->response->make($prepared, 200, ['Content-type' => 'application/octet-stream']);

        } catch(\Exception $e) {
            return $this->response->make(false, 500);
        }
    }
}