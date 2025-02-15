<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Request;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use WebServCo\Http\Contract\Message\Request\Method\RequestMethodServiceInterface;
use WebServCo\Http\Service\Message\AbstractMessage;

use function is_string;
use function strtolower;

abstract class AbstractRequest extends AbstractMessage implements RequestInterface
{
    protected const string HOST_HEADER_NAME = 'Host';

    protected ?string $requestTarget;

    public function __construct(
        protected RequestMethodServiceInterface $requestMethodService,
        StreamInterface $body,
        protected UriInterface $uri,
        protected string $method,
    ) {
        parent::__construct($body);

        $this->requestTarget = null;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        $query = $this->uri->getQuery();
        if ($query !== '') {
            $target .= '?' . $query;
        }

        if ($target === '') {
            return '/';
        }

        return $target;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * @param string $method
     * @return static
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withMethod($method): self
    {
        $this->requestMethodService->validateMethod($method);

        if ($method === $this->method) {
            return $this;
        }

        $clone = clone $this;
        $clone->method = $method;

        return $clone;
    }

    /**
     * Return an instance with the specific request-target.
     *
     * @return static
     */
    public function withRequestTarget(mixed $requestTarget): self
    {
        if (!is_string($requestTarget)) {
            // Not clear why PSR specifies requestTarget as mixed
            throw new InvalidArgumentException('Request target is not a string.');
        }
        if ($requestTarget === $this->requestTarget) {
            return $this;
        }

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * PHPMD error BooleanArgumentFlag; "PSR made me do it"
     *
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     * @param bool $preserveHost
     * @return static
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $clone = clone $this;

        $clone->uri = $uri;

        if ($preserveHost && $this->hasHeader(self::HOST_HEADER_NAME)) {
            return $clone;
        }

        $host = $uri->getHost();

        if ($host === '') {
            return $clone;
        }

        $port = $uri->getPort();
        if ($port !== null) {
            $host .= ':' . $port;
        }

        $clone = $clone->withoutHeader(self::HOST_HEADER_NAME);
        $clone->headersMap[strtolower(self::HOST_HEADER_NAME)] = self::HOST_HEADER_NAME;
        $clone->headers[self::HOST_HEADER_NAME] = [$host];

        return $clone;
    }
}
