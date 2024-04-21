<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message\Request\Server;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use WebServCo\Http\Contract\Message\Request\Method\RequestMethodServiceInterface;
use WebServCo\Http\Contract\Message\Request\Server\ServerDataParserInterface;
use WebServCo\Http\Contract\Message\UploadedFileParserInterface;
use WebServCo\Http\Service\Message\Request\Server\ServerRequest;

use function is_string;

/**
 * A general `Psr\Http\Message\ServerRequestFactoryInterface` implementation.
 *
 * PSR-17
 * Not appropriate to directly create ServerRequestInterface,
 * since createServerRequest only accepts $_SERVER data,
 * and it also requires method and uri which are usually extracted from the acutal $_SERVER data.
 *
 * Returns a \WebServCo\Http\ServerRequest implementation of the \Psr\Http\Message\ServerRequestInterface
 */
final class ServerRequestFactory implements ServerRequestFactoryInterface
{
    public function __construct(
        private StreamFactoryInterface $streamFactory,
        private UploadedFileParserInterface $uploadedFileParser,
        private RequestMethodServiceInterface $requestMethodService,
        private ServerDataParserInterface $serverDataParser,
        private UriFactoryInterface $uriFactory,
    ) {
    }

    /**
     * Create a new server request (PSR-7).
     *
     * @param \Psr\Http\Message\UriInterface|string $uri
     * Following abomination needed in order to be contravariant with PSR method definition.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $serverParams
     * @phpcs:enable
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        }

        return new ServerRequest(
            $this->serverDataParser,
            $this->uploadedFileParser,
            $this->requestMethodService,
            /**
             * \Psr\Http\Message\StreamInterface
             * PSR-17 does not specify a body parameter, despite PSR-7 requiring the body to not be null.
             * So we need to create an empty request body.
             */
            $this->streamFactory->createStream(''),
            $uri,
            $method,
            $serverParams,
        );
    }
}
