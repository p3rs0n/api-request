<?php

namespace p3rs0n\ApiRequest;

use p3rs0n\ApiRequest\Enums\AuthenticationTypeEnum;

interface ApiAuthenticationProviderInterface
{
    public function getAuthenticationType(): AuthenticationTypeEnum;
    public function getAuthenticationParameters(ApiRequest $request): array;

}
