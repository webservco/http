<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message\Request\Server;

interface ServerHeadersParserInterface
{
    public function parseHeaderField(string $field): string;

    /**
     * Parse header data from server parameters.
     * Retuns data in the format used in `\WebServCo\Http\Message\AbstractMessage`.
     * Input data should already be parsed (@see `\WebServCo\Http\Helper\ServerParserHelper`)
     *
     * @param array<string,array<int,string>|scalar|null> $serverParams already parsed server params.
     * @return array<string,array<string>>
     */
    public function parseServerHeaders(array $serverParams): array;
}
