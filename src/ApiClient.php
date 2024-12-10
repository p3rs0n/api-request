<?php

namespace p3rs0n\ApiRequest;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    private ?Client $client = null;

    public static function make(ApiConfig $config): self
    {
        return new self($config);
    }

    public function __construct(
        private readonly ApiConfig $config,
    )
    {
    }

    public function query(ApiRequest $request): ResponseInterface
    {
        return $this->getApiClient()->{$request->method->value}(
            $request->getUri(),
            [
                'headers' => $request->getHeaders(),
                'query' => $request->getQueryParameters(),
                'body' => json_encode($request->getBodyParameters()),
            ]
        );
    }

    private function getApiClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client(
                $this->config->getClientConfiguration()
            );
        }
        return $this->client;
    }

}