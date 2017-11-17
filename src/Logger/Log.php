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
    protected $config;

    public function __construct() {

        $this->config = app('config')->get('rest-bridge.log');
        $this->enabled = $this->config['enabled'];

        $this->queryLog = new Logger('query');
        $this->queryLog->pushHandler(new StreamHandler($this->getLogPath('query')), Logger::DEBUG);

        $this->requestLog = new Logger('request');
        $this->requestLog->pushHandler(new StreamHandler($this->getLogPath('request')), Logger::DEBUG);

        $this->responseLog = new Logger('response');
        $this->responseLog->pushHandler(new StreamHandler($this->getLogPath('response')), Logger::DEBUG);
    }

    public function getLogPath($file) {
        $file = 'eloquent-rest-bridge-'.$file.".log";
        $path = array_filter(explode(DIRECTORY_SEPARATOR, array_get($this->config, 'base_path')));
        $path[] = $file;

        return "/".implode(DIRECTORY_SEPARATOR, $path);
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