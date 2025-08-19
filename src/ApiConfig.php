<?php

namespace p3rs0n\ApiRequest;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

abstract class ApiConfig
{
    public function __construct(
        public array $clientConfiguration = [],
        public ?CacheItemPoolInterface $cache = null,
        public ?LoggerInterface $logger = null,
        public ?ApiAuthenticationProviderInterface $authenticationProvider = null,
    )
    {
    }

    public static function make(...$args): static
    {
        return new static(...$args);
    }

}
