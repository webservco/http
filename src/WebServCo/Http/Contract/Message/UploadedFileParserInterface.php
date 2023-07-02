<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message;

interface UploadedFileParserInterface
{
    /**
     * Process uploaded files that should already be in PSR format.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $uploadedFiles
     * @phpcs:enable
     * @return array<string,array<int,\Psr\Http\Message\UploadedFileInterface>>
     */
    public function parsePsrUploadedFiles(array $uploadedFiles): array;

    /**
     * Process uploaded files from superglobal to PSR format.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param array<int|string,mixed> $uploadedFiles
     * @phpcs:enable
     * @return array<string,array<int,\Psr\Http\Message\UploadedFileInterface>>
     */
    public function parseSuperglobalUploadedFiles(array $uploadedFiles): array;
}
