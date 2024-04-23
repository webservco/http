<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message;

use InvalidArgumentException;
use OutOfBoundsException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

use function array_key_exists;
use function array_merge;
use function implode;
use function is_string;
use function strtolower;

/**
 * AbstractMessage.
 *
 * This is used by both requests and responses.
 */
abstract class AbstractMessage implements MessageInterface
{
    /**
     * Message header values.
     *
     * @var array<string,array<string>>
     */
    protected array $headers;

    protected string $protocolVersion;

    /**
     * Header name mapping.
     *
     * Key: lowercase header name.
     * Value: unaltered header name as specified.
     *
     * @var array<string,string>
     */
    protected array $headersMap;

    public function __construct(protected StreamInterface $body)
    {
        $this->headers = $this->headersMap = [];
        $this->protocolVersion = '1.1';
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * @param string $name
     * @return array<string>
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function getHeader($name): array
    {
        if (!$this->hasHeader($name)) {
            return [];
        }

        $name = $this->headersMap[strtolower($name)];

        if (!array_key_exists($name, $this->headers)) {
            throw new OutOfBoundsException('Header not found.');
        }

        return $this->headers[$name];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * @param string $name
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function getHeaderLine($name): string
    {
        $value = $this->getHeader($name);
        if ($value === []) {
            return '';
        }

        return implode(', ', $value);
    }

    /**
     * Retrieves all message header values.
     *
     * @return array<string,array<string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function hasHeader($name): bool
    {
        return array_key_exists(strtolower($name), $this->headersMap);
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * @param string $name
     * @param array<string>|string $value
     * @return static
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withAddedHeader($name, $value): self
    {
        if (!$this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        // Header already exists, we need to append the value(s)

        // Handle string / array
        if (is_string($value)) {
            $value = [$value];
        }

        $clone = clone $this;

        $originalName = $clone->getOriginalHeaderName($name);

        $clone->headers[$originalName] = array_merge($clone->headers[$originalName], $value);

        return $clone;
    }

    /**
     * Return an instance with the specified message body.
     *
     * @return static
     */
    public function withBody(StreamInterface $body): self
    {
        if ($body === $this->body) {
            return $this;
        }

        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * @param string $name
     * @param array<string>|string $value
     * @return static
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withHeader($name, $value): self
    {
        $clone = clone $this;

        if ($clone->hasHeader($name)) {
            $originalName = $clone->getOriginalHeaderName($name);
            unset($clone->headers[$originalName]);
        }

        $clone->headersMap[strtolower($name)] = $name;

        // Handle string / array
        if (is_string($value)) {
            $value = [$value];
        }
        $clone->headers[$name] = $value;

        return $clone;
    }

    /**
     * Return an instance without the specified header.
     *
     * PSR compatibility workaround (@param)
     *
     * @param string $name
     * @return static
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withoutHeader($name): self
    {
        if (!$this->hasHeader($name)) {
            return $this;
        }

        $clone = clone $this;

        $originalName = $this->getOriginalHeaderName($name);

        unset($clone->headers[$originalName]);
        unset($clone->headersMap[strtolower($name)]);

        return $clone;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * @param string $version
     * @return static
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withProtocolVersion($version): self
    {
        if ($version === $this->protocolVersion) {
            return $this;
        }

        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
     * Get the exact header name as specified by the consumer.
     */
    protected function getOriginalHeaderName(string $name): string
    {
        if (!$this->hasHeader($name)) {
            throw new InvalidArgumentException('Specified header name does not exist.');
        }

        return $this->headersMap[strtolower($name)];
    }
}
