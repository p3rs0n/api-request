<?php

namespace p3rs0n\ApiRequest\Exceptions;

use Exception;
use p3rs0n\ApiRequest\ApiRequest;

class RequestValidationFailedException extends Exception
{

    public function __construct(
        private readonly ApiRequest $request,
    )
    {
        parent::__construct();
    }

    public function getRequest(): ApiRequest
    {
        return $this->request;
    }

}
