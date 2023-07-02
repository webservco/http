<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message\Request\Method;

use Fig\Http\Message\RequestMethodInterface;

interface RequestMethodServiceInterface extends RequestMethodInterface
{
    public const METHODS = [
        self::METHOD_CONNECT,
        self::METHOD_DELETE,
        self::METHOD_GET,
        self::METHOD_HEAD,
        self::METHOD_OPTIONS,
        self::METHOD_PATCH,
        self::METHOD_POST,
        self::METHOD_PURGE,
        self::METHOD_PUT,
        self::METHOD_TRACE,
    ];

    public function validateMethod(string $method): bool;
}
