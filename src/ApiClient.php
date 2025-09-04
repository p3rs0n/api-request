<?php

namespace p3rs0n\ApiRequest;

use GuzzleHttp\Client;
use JsonException;
use p3rs0n\ApiRequest\Enums\AuthenticationTypeEnum;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    private static ?Client $client = null;

    public static function make(ApiConfig $config): self
    {
        return new self($config);
    }

    public function __construct(
        private readonly ApiConfig $config,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function query(ApiRequest $request): ResponseInterface
    {
        return $this->getApiClient()->{$request->method->value}(
            $request->getUri(),
            [
                'headers' => $this->getHeaders($request),
                'query'   => $this->getQueryParameters($request),
                'body'    => $request->getRawBody() ?? json_encode($this->getBodyParameters($request), JSON_THROW_ON_ERROR),
            ]
        );
    }

    private function getHeaders(ApiRequest $request): array
    {
        $headers = $request->getHeaders();
        if ($this->config->authenticationProvider && $this->config->authenticationProvider->getAuthenticationType(
            ) === AuthenticationTypeEnum::HEADER) {
            $headers = array_merge(
                $headers,
                $this->config->authenticationProvider->getAuthenticationParameters($request)
            );
        }
        return $headers;
    }

    private function getQueryParameters(ApiRequest $request): array
    {
        $query = $request->getQueryParameters();
        if ($this->config->authenticationProvider && $this->config->authenticationProvider->getAuthenticationType(
            ) === AuthenticationTypeEnum::QUERY) {
            $query = array_merge(
                $query,
                $this->config->authenticationProvider->getAuthenticationParameters($request)
            );
        }
        return $query;
    }

    private function getBodyParameters(ApiRequest $request): array
    {
        $body = $request->getBodyParameters();
        if ($this->config->authenticationProvider && $this->config->authenticationProvider->getAuthenticationType(
            ) === AuthenticationTypeEnum::BODY) {
            $body = array_merge(
                $body,
                $this->config->authenticationProvider->getAuthenticationParameters($request)
            );
        }
        return $body;
    }

    private function getApiClient(): Client
    {
        if (!self::$client) {
            self::$client = new Client(
                $this->config->clientConfiguration
            );
        }
        return self::$client;
    }

}
