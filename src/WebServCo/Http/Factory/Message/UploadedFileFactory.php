<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use UnexpectedValueException;
use WebServCo\Http\Service\Message\UploadedFile;

use const UPLOAD_ERR_OK;

final class UploadedFileFactory implements UploadedFileFactoryInterface
{
    public function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null,
    ): UploadedFileInterface {
        if ($size === null) {
            $size = $stream->getSize();
        }
        if ($size === null) {
            throw new UnexpectedValueException('Could not get size of uploaded file.');
        }

        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }
}
