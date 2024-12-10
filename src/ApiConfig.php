<?php

namespace p3rs0n\ApiRequest;

abstract class ApiConfig //TODO interface?
{
    public string $baseUri = '';
    public int $timeout = 0;
    public function getClientConfiguration(): array
    {
        return [
            'base_uri' => $this->baseUri,
            'timeout' => $this->timeout,
        ];
    }
}