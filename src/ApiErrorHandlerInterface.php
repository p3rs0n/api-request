<?php

namespace p3rs0n\ApiRequest;

use Psr\Http\Message\ResponseInterface;
use Throwable;

interface ApiErrorHandlerInterface
{
    public function handle(ApiRequest $apiRequest, Throwable $throwable, ?ResponseInterface $response = null): void;
}
