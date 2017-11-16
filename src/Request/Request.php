<?php

namespace Andrewlamers\EloquentRestBridge\Request;

use Andrewlamers\EloquentRestBridge\Exceptions\RestException;
use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\stream_for;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Request
{
    protected $config;
    protected $client;
    protected $encrypter;
    protected $_encrypt;
    protected $_compress;
    protected $_encoding;

    public function __construct(array $config = [])
    {
        $this->config = app('config');
        $this->config->set('rest-bridge', array_merge($this->config->get('rest-bridge'), ['request' => $config]));

        $this->encrypter = new Encrypter($this->getKey(), $this->config->get('rest-bridge.encryption.cipher'));
        $this->client = new Client(['timeout' => 5]);
    }

    public function getKey()
    {
        $key = $this->config->get('rest-bridge.encryption.key');

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return $key;
    }

    public function compress($payload)
    {
        return gzencode($payload);
    }

    public function decompress($payload)
    {
        return gzdecode($payload);
    }

    public function encrypt($data)
    {
        return $this->encrypter->encrypt($data, false);
    }

    public function decrypt($data)
    {
        return $this->encrypter->decrypt($data, false);
    }

    public function request($payload, $uri = FALSE)
    {
        $body = $this->preparePayload($payload);

        if (!$uri)
            $uri = $this->config->get('rest-bridge.url');

        $uri .= '/_rest_bridge/handler';

        $options = [
            'body'    => $body,
            'headers' => [
                'Accept-encoding' => 'None',
                'Content-type' => 'application/octet-stream'
            ]
        ];

        $response = $this->client->request('POST', $uri, $options);

        $data = $response->getBody()->getContents();

        return $this->parsePayload($data);
    }

    public function preparePayload($payload)
    {
        $data = json_encode($payload);

        $data = $this->compress($data);
        $data = $this->encrypt($data);

        return $data;
    }

    public function parsePayload($payload)
    {
        $data = NULL;

        $data = $this->decrypt($payload);
        $data = $this->decompress($data);

        $data = json_decode($data, TRUE);

        if (isset($data['exception']) && $data['exception'] != NULL) {
            $exception = array_get($data, 'exception');
            throw new RestException(array_get($exception, 'message'));
        }

        return $data;
    }
}