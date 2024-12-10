<?php

namespace p3rs0n\ApiRequest\Enums;

enum MethodEnum: string
{
    case GET = 'get';
    case POST = 'post';
    case PUT = 'put';
    case DELETE = 'delete';
    case PATCH = 'patch';
    case OPTIONS = 'options';
    case HEAD = 'head';

}