<?php

namespace p3rs0n\ApiRequest\Enums;

enum AuthenticationTypeEnum
{
    case QUERY;
    case BODY;
    case HEADER;
}
