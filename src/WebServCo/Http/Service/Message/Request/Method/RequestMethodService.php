<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Request\Method;

use InvalidArgumentException;
use WebServCo\Http\Contract\Message\Request\Method\RequestMethodServiceInterface;

use function in_array;

final class RequestMethodService implements RequestMethodServiceInterface
{
    public function validateMethod(string $method): bool
    {
        if (in_array($method, self::METHODS, true)) {
            return true;
        }

        throw new InvalidArgumentException('Invalid method specified.');
    }
}
