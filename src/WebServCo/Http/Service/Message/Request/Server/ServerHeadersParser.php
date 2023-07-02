<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Request\Server;

use UnexpectedValueException;
use WebServCo\Http\Contract\Message\Request\Server\ServerHeadersParserInterface;

use function is_string;
use function str_replace;
use function strpos;
use function strtolower;
use function substr;
use function ucwords;

final class ServerHeadersParser implements ServerHeadersParserInterface
{
    public function parseHeaderField(string $field): string
    {
        if (strpos($field, 'HTTP_', 0) !== 0) {
            throw new UnexpectedValueException('Not a header field.');
        }

        return str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower(substr($field, 5)))));
    }

    /**
     * Parse header data from server parameters.
     * Retuns data in the format used in `\WebServCo\Http\Message\AbstractMessage`.
     * Input data should already be parsed (@see `\WebServCo\Http\Helper\ServerParserHelper`)
     *
     * @param array<string,array<int,string>|scalar|null> $serverParams already parsed server params.
     * @return array<string,array<string>>
     */
    public function parseServerHeaders(array $serverParams): array
    {
        $result = [];
        foreach ($serverParams as $key => $value) {
            if (strpos($key, 'HTTP_', 0) !== 0) {
                // Not a header field.
                continue;
            }
            if (!is_string($value)) {
                throw new UnexpectedValueException('Value is not scalar');
            }
            $result[$this->parseHeaderField($key)][] = $value;
        }

        return $result;
    }
}
