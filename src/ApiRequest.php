<?php

namespace p3rs0n\ApiRequest;

use GuzzleHttp\Exception\RequestException;
use p3rs0n\ApiRequest\Enums\MethodEnum;

abstract class ApiRequest
{
    protected string $uri = '';
    public MethodEnum $method = MethodEnum::GET;
    private array $headers = [];
    private array $body = [];
    private array $query = [];
    private array $values = [];
    private $validator;
    private $errorHandler;
    private $responseHandler;
    private bool $cached = true;

    abstract protected function getApiConfig(): ApiConfig;

    public function execute(): mixed
    {
        if($this->validator){
            $validator = $this->validator;
            $validator($this);
        }

        try {
            $response = ApiClient::make($this->getApiConfig())->query($this);
        }catch (RequestException $e){
            if($this->errorHandler){
                $errorHandler = $this->errorHandler;
                $errorHandler($this, $e); //TODO response
            }else{
                throw $e;
            }
        }catch (\Exception $e){
            dd($e);
        }

        if($this->responseHandler){
            $responseHandler = $this->responseHandler;
            $responseHandler($response);
        }

        return $response;
    }

    abstract public function getQueryParameters(): array;
    abstract public function getBodyParameters(): array;

    public function __set(string $name, $value): void
    {
        $this->values[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->values[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->values[$name]);
    }

    public function withValidator(callable $validator): static
    {
        $this->validator = $validator;
        return $this;
    }

    public function withErrorHandler(callable $errorHandler): static
    {
        $this->errorHandler = $errorHandler;
        return $this;
    }

    public function withResponseHandler(callable $responseHandler): static
    {
        $this->responseHandler = $responseHandler;
        return $this;
    }

    public function withValues(array $values): static
    {
        $this->values = $values;
        return $this;
    }

    public function setValue(string $key, $value): static
    {
        $this->values[$key] = $value;
        return $this;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public static function make(...$args): static
    {
        return new static(...$args);
    }

    public function setHeader(string $key, string $value): static
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function withHeaders(array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setUri(string $uri): static
    {
        $this->uri = $uri;
        return $this;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setMethod(MethodEnum $method): static
    {
        $this->method = $method;
        return $this;
    }

}