<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Response;

use InvalidArgumentException;
use WebServCo\Http\Contract\Message\Response\StatusCodeServiceInterface;

use function array_key_exists;

final class StatusCodeService implements StatusCodeServiceInterface
{
    public function getReasonPhraseByStatusCode(int $code): string
    {
        $this->validateStatusCode($code);

        return self::STATUS_CODES[$code];
    }

    public function validateStatusCode(int $code): bool
    {
        if (array_key_exists($code, self::STATUS_CODES)) {
            return true;
        }

        throw new InvalidArgumentException('Invalid status code specified.');
    }
}
