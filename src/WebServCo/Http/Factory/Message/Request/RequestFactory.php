<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message\Request;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use WebServCo\Http\Contract\Message\Request\Method\RequestMethodServiceInterface;
use WebServCo\Http\Service\Message\Request\Request;

use function is_string;

final class RequestFactory implements RequestFactoryInterface
{
    public function __construct(
        private RequestMethodServiceInterface $requestMethodService,
        private StreamFactoryInterface $streamFactory,
        private UriFactoryInterface $uriFactory,
    ) {
    }

    /**
     * Create a request.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function createRequest(string $method, $uri): RequestInterface
    {
        if (is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        }

        return new Request(
            $this->requestMethodService,
            /**
             * PSR-17 does not specify a body parameter, despite PSR-7 requiring the body to not be null.
             * So we need to create an empty request body.
             * \Psr\Http\Message\StreamInterface
             */
            $this->streamFactory->createStream(''),
            $uri,
            $method,
        );
    }
}
