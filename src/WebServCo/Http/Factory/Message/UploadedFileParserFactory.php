<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message;

use Psr\Http\Message\StreamFactoryInterface;
use WebServCo\Http\Contract\Message\UploadedFileParserInterface;
use WebServCo\Http\Service\Message\UploadedFileParser;

final class UploadedFileParserFactory
{
    public function __construct(private StreamFactoryInterface $streamFactory)
    {
    }

    public function createUploadedFileParser(): UploadedFileParserInterface
    {
        return new UploadedFileParser(
            $this->streamFactory,
            new UploadedFileFactory(),
        );
    }
}
