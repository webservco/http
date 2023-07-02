<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message\Stream;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;
use WebServCo\Http\Service\Message\Stream\Stream;

use function fopen;
use function fwrite;
use function is_resource;

final class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        // temporary file/memory wrapper; if bigger than 5MB will be written to temp file.
        $resource = fopen('php://temp/maxmemory:' . (5 * 1_024 * 1_024), 'rw+');

        if (!is_resource($resource)) {
            throw new UnexpectedValueException('Resource is not a resource object.');
        }

        fwrite($resource, $content);

        return new Stream($resource);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = fopen($filename, $mode);

        if (!is_resource($resource)) {
            throw new UnexpectedValueException('Resource is not a resource object.');
        }

        return new Stream($resource);
    }

    /**
     * Create a new stream from an existing resource.
     *
     * @param resource $resource
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
