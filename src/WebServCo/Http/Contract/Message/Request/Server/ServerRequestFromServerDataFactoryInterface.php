<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message\Request\Server;

use Psr\Http\Message\ServerRequestInterface;

interface ServerRequestFromServerDataFactoryInterface
{
    /**
     * Create ServerRequestInterface from server data (usually $_SERVER).
     *
     * All parameters required by ServerRequestFactory.createServerRequest are handled locally.
     *
     * @param array<int,string> $allowedHosts
     * Following abomination needed in order to be contravariant with PSR method definitions.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $cookieParams
     * @param mixed[]|object|null $parsedBody
     * @param mixed[] $queryParams
     * @param mixed[] $serverParams
     * @param array<int|string,mixed> $uploadedFiles
     * @phpcs:enable
     */
    public function createServerRequestFromServerData(
        array $allowedHosts = [],
        array $cookieParams = [],
        mixed $parsedBody = null,
        array $queryParams = [],
        array $serverParams = [],
        array $uploadedFiles = [],
    ): ServerRequestInterface;
}
