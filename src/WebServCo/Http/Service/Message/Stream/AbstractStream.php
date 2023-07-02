<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Stream;

use InvalidArgumentException;
use UnexpectedValueException;

use function array_key_exists;
use function fstat;
use function get_resource_type;
use function is_array;
use function is_resource;
use function stream_get_contents;
use function stream_get_meta_data;
use function strpos;

abstract class AbstractStream
{
    /**
     * Create instance from a stream resource.
     */
    public function __construct(protected mixed $resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Resource is not a resource object.');
        }
        if (get_resource_type($resource) !== 'stream') {
            throw new InvalidArgumentException('Resource is not a stream.');
        }
    }

    public function getContents(): string
    {
        if (!is_resource($this->resource)) {
            throw new UnexpectedValueException('Resource is not a resource object.');
        }

        if (!$this->isReadable()) {
            throw new UnexpectedValueException('Resource is not readable.');
        }

        $contents = stream_get_contents($this->resource);

        if ($contents === false) {
            throw new UnexpectedValueException('Failure getting stream contents.');
        }

        return $contents;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * @param string|null $key
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function getMetadata($key = null): mixed
    {
        if (!is_resource($this->resource)) {
            return $key === null
                ? []
                : null;
        }

        $data = stream_get_meta_data($this->resource);

        if ($key === null) {
            return $data;
        }

        if (!array_key_exists($key, $data)) {
            return null;
        }

        return $data[$key];
    }

    public function getSize(): ?int
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $info = fstat($this->resource);

        if (!is_array($info)) {
            return null;
        }

        return $info['size'];
    }

    public function isReadable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $mode = $this->getMode();

        return strpos($mode, 'r') !== false || strpos($mode, '+') !== false;
    }

    public function isSeekable(): bool
    {
        if (!is_resource($this->resource)) {
            throw new UnexpectedValueException('Resource is not a resource object.');
        }

        $meta = stream_get_meta_data($this->resource);

        return $meta['seekable'];
    }

    public function isWritable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $mode = $this->getMode();

        return strpos($mode, 'x') !== false
            || strpos($mode, 'w') !== false
            || strpos($mode, 'c') !== false
            || strpos($mode, 'a') !== false
            || strpos($mode, '+') !== false;
    }

    protected function getMode(): string
    {
        if (!is_resource($this->resource)) {
            throw new UnexpectedValueException('Resource is not a resource object.');
        }

        $meta = stream_get_meta_data($this->resource);

        return $meta['mode'];
    }
}
