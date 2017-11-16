<?php

namespace Andrewlamers\EloquentRestBridge\Logger;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log
{
    protected $queryLog;
    protected $requestLog;
    protected $responseLog;
    protected $enabled = true;

    public function __construct($enabled = true) {

        $this->enabled = $enabled;

        $this->queryLog = new Logger('query');
        $this->queryLog->pushHandler(new StreamHandler(storage_path('logs/eloquent-rest-handler-query.log')), Logger::DEBUG);

        $this->requestLog = new Logger('request');
        $this->requestLog->pushHandler(new StreamHandler(storage_path('logs/eloquent-rest-handler-request.log')), Logger::DEBUG);

        $this->responseLog = new Logger('response');
        $this->responseLog->pushHandler(new StreamHandler(storage_path('logs/eloquent-rest-handler-response.log')), Logger::DEBUG);
    }

    public function log($type, $message, $level) {
        if($this->enabled) {
            $this->{$type . 'Log'}->log($level, $message);
        }
    }

    public function query($query, $level = Logger::DEBUG) {
        $this->log("query", json_encode($query), $level);
    }

    public function request($request, $level = Logger::DEBUG) {
        $log = $request;
        if(!is_string($log)) {
            $log = [
                'headers' => $request->getHeaders(),
                'uri' => $request->getRequestTarget(),
                'method' => $request->getMethod(),
                'body' => (string)$request->getBody()
            ];

            $log = json_encode($log);
        }

        $this->log("request", $log, $level);
    }

    public function response($response, $level = Logger::DEBUG) {
        $log = $response;
        if(!is_string($log)) {
            $response = clone $response;
            $log = [
                'headers' => $response->getHeaders(),
                'body' => (string)$response->getBody()
            ];

            $log = json_encode($log);
        }
        $this->log("response", $log, $level);
    }
}