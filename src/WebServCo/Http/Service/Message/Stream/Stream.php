<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Stream;

use OutOfBoundsException;
use Psr\Http\Message\StreamInterface;
use RangeException;
use UnexpectedValueException;

use function fclose;
use function feof;
use function fread;
use function fseek;
use function ftell;
use function fwrite;
use function is_int;
use function is_resource;

use const SEEK_SET;

final class Stream extends AbstractStream implements StreamInterface
{
    public function close(): void
    {
        if (!is_resource($this->resource)) {
            return;
        }

        $resource = $this->detach();

        if (!is_resource($resource)) {
            return;
        }

        fclose($resource);
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach(): mixed
    {
        $resource = $this->resource;
        $this->resource = null;

        if (!is_resource($resource)) {
            return null;
        }

        return $resource;
    }

    public function eof(): bool
    {
        if (!is_resource($this->resource)) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * Read data from the stream.
     *
     * @param int $length
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function read($length): string
    {
        if (!is_resource($this->resource)) {
            throw new UnexpectedValueException('Resource is not a resource object.');
        }

        if (!$this->isReadable()) {
            throw new OutOfBoundsException('Resource is not readable.');
        }

        if ($length < 0) {
            throw new RangeException('Length is not greater than zero.');
        }

        $result = fread($this->resource, $length);

        if ($result === false) {
            throw new UnexpectedValueException('Failure reading from stream.');
        }

        return $result;
    }

    public function rewind(): void
    {
        if (!is_resource($this->resource)) {
            throw new UnexpectedValueException('Resource is not a resource object.');
        }

        $this->seek(0);
    }

    /**
     * Seek to a position in the stream.
     *
     * @param int $offset
     * @param int $whence
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!is_resource($this->resource)) {
            throw new UnexpectedValueException('Resource is not a resource object.');
        }

        if (!$this->isSeekable()) {
            throw new OutOfBoundsException('Resource is not seekable.');
        }

        $result = fseek($this->resource, $offset, $whence);

        if ($result !== 0) {
            throw new UnexpectedValueException('Failure while seeking.');
        }
    }

    public function tell(): int
    {
        if (!is_resource($this->resource)) {
            throw new UnexpectedValueException('Resource is not a resource object.');
        }

        $result = ftell($this->resource);

        if (!is_int($result)) {
            throw new UnexpectedValueException('Failure returning current pointer position.');
        }

        return $result;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function write($string): int
    {
        if (!is_resource($this->resource)) {
            throw new UnexpectedValueException('Resource is not a resource object.');
        }

        if (!$this->isWritable()) {
            throw new UnexpectedValueException('Resource is not writable.');
        }

        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new UnexpectedValueException('Failure writing data to stream.');
        }

        return $result;
    }

    public function __toString(): string
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (UnexpectedValueException) {
            return '';
        }
    }
}
