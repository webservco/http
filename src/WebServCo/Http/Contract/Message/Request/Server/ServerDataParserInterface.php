<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message\Request\Server;

interface ServerDataParserInterface
{
    /**
     * Parse body data.
     *
     * @return array<string,string>|object|null
     */
    public function parseBodyData(mixed $data): array|object|null;

    /**
     * Parse cookie/query parameters.
     *
     * Following abomination needed in order to be contravariant with PSR method definition.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $params
     * @phpcs:enable
     * @return array<int|string,string>
     */
    public function parseCookieQueryParams(array $params): array;

    /**
     * Parse server params.
     *
     * Following abomination needed in order to be contravariant with PSR method definition.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $serverParams
     * @phpcs:enable
     * @return array<string,array<int,string>|bool|int|float|string|null>
     */
    public function parseServerParams(array $serverParams): array;
}
