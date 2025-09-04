<?php

namespace p3rs0n\ApiRequest;

use Closure;
use Exception;
use GuzzleHttp\Exception\RequestException;
use p3rs0n\ApiRequest\Enums\MethodEnum;
use p3rs0n\ApiRequest\Exceptions\RequestValidationFailedException;
use Psr\Http\Message\ResponseInterface;

abstract class ApiRequest
{
    public string $uri = '';
    public MethodEnum $method = MethodEnum::GET;
    public bool $shouldLog = false;
    public bool $shouldCache = false;
    public int $cacheTtl = 0;
    private ?Closure $errorHandler = null;

    public function __construct(
        public array $headers = [],
        public array $body = [],
        public array $query = [],
        public array $route = [],
    ) {
    }

    public static function make(...$args): static
    {
        return new static(...$args);
    }

    public function execute(bool $disableCache = false): mixed
    {
        if (!$this->validate()) {
            throw new RequestValidationFailedException($this);
        }
        if (!$disableCache && $cachedResponse = $this->getCachedResponse()) {
            return $cachedResponse;
        }
        try {
            $response = ApiClient::make($this->getApiConfig())->query($this);
            if (!$disableCache) {
                $this->cacheResponse($response);
            }
            $this->logSuccess($response);
        } catch (Exception $e) {
            $response = null;
            if ($e instanceof RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
            }
            $this->logError($response, $e);
            if ($this->errorHandler) {
                ($this->errorHandler)($this, $e, $response);
            }
            if ($errorHandler = $this->getErrorHandler()) {
                $errorHandler->handle($this, $e, $response);
            }
            else {
                throw $e;
            }
            return null;
        }
        return $this->processResponse($response);
    }

    public function processResponse(?ResponseInterface $response): mixed
    {
        return $response;
    }

    public function getQueryParameters(): array
    {
        return $this->query;
    }

    public function getBodyParameters(): array
    {
        return $this->body;
    }

    public function getRawBody(): ?string
    {
        return null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getUri(): string
    {
        if ($this->route) {
            return preg_replace_callback(
                '/\{(\w+)}/',
                function ($matches) {
                    return $this->route[$matches[1]] ?? $matches[0];
                },
                $this->uri
            );
        }
        return $this->uri;
    }

    public function getErrorHandler(): ?ApiErrorHandlerInterface
    {
        return null;
    }

    public function withErrorHandler(Closure $errorHandler): static
    {
        $this->errorHandler = $errorHandler;
        return $this;
    }

    public function validate(): bool
    {
        return true;
    }

    public function getCacheKey(): string
    {
        try {
            return 'request_'.md5(
                    self::class.$this->getUri().$this->method->value.json_encode(
                        [
                            'headers' => $this->getHeaders(),
                            'query'   => $this->getQueryParameters(),
                            'body'    => $this->getBodyParameters(),
                        ],
                        JSON_THROW_ON_ERROR
                    )
                );
        } catch (Exception) {
        }
        return 'request_'.md5(random_bytes(32));
    }

    private function getCachedResponse(): ?ResponseInterface
    {
        if ($this->shouldCache && $this->cacheTtl > 0 && $this->getApiConfig()->cache) {
            $cachedResponse = $this->getApiConfig()->cache->getItem($this->getCacheKey())->get();
            if ($cachedResponse) {
                return $cachedResponse;
            }
        }
        return null;
    }

    private function cacheResponse(ResponseInterface $response): void
    {
        if ($this->shouldCache && $this->cacheTtl > 0 && $this->getApiConfig()->cache) {
            $this->getApiConfig()->cache->getItem($this->getCacheKey())->expiresAfter($this->cacheTtl)->set($response);
        }
    }

    private function logSuccess(ResponseInterface $response): void
    {
        if (!$this->shouldLog || !$this->getApiConfig()->logger) {
            return;
        }
        $this->getApiConfig()->logger->info(
            self::class.' - Success',
            [
                                        'request'  => [
                                            'uri'     => $this->getUri(),
                                            'method'  => $this->method->value,
                                            'headers' => $this->getHeaders(),
                                            'query'   => $this->getQueryParameters(),
                                            'body'    => $this->getBodyParameters(),
                                        ],
                                        'response' => [
                                            'status_code' => $response->getStatusCode(),
                                            'body'        => substr($response->getBody()->getContents(), 0, 1000),
                                        ],
                                    ]
        );
    }

    private function logError(?ResponseInterface $response, Exception $e): void
    {
        if (!$this->shouldLog || !$this->getApiConfig()->logger) {
            return;
        }
        $this->getApiConfig()->logger->error(
            self::class.' - Error',
            [
                                      'request'  => [
                                          'uri'     => $this->getUri(),
                                          'method'  => $this->method->value,
                                          'headers' => $this->getHeaders(),
                                          'query'   => $this->getQueryParameters(),
                                          'body'    => $this->getBodyParameters(),
                                      ],
                                      'response' => [
                                          'status_code' => $response?->getStatusCode() ?? 0,
                                          'body'        => substr($response?->getBody()->getContents() ?? '', 0, 1000),
                                      ],
                                      'error'    => [
                                          'message' => $e->getMessage(),
                                          'code'    => $e->getCode(),
                                          'file'    => $e->getFile(),
                                          'line'    => $e->getLine(),
                                      ],
                                  ]
        );
    }

    abstract public function getApiConfig(): ApiConfig;

}
