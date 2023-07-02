<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message\Request\Server;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use WebServCo\Http\Contract\Message\Request\Server\ServerRequestFromServerDataFactoryInterface;
use WebServCo\Http\Contract\Message\UploadedFileParserInterface;
use WebServCo\Http\Factory\Message\Stream\StreamFactory;
use WebServCo\Http\Factory\Message\UploadedFileParserFactory;
use WebServCo\Http\Factory\Message\UriFactory;
use WebServCo\Http\Service\Message\Request\Method\RequestMethodService;
use WebServCo\Http\Service\Message\Request\Server\ServerDataParser;
use WebServCo\Http\Service\Message\Request\Server\ServerHeadersParser;

use function in_array;

use const PHP_SAPI;

/**
 * Helper to create custom ServerRequestInterface implementations.
 */
final class ServerRequestFromServerDataFactory implements ServerRequestFromServerDataFactoryInterface
{
    private ServerRequestFactoryInterface $serverRequestFactory;

    private UploadedFileParserInterface $uploadedFileParser;

    public function __construct(
        private ServerParamsProcessorFactory $serverParamsProcFactory = new ServerParamsProcessorFactory(),
        StreamFactoryInterface $streamFactory = new StreamFactory(),
    ) {
        $uploadedFileParserFactory = new UploadedFileParserFactory($streamFactory);
        $this->uploadedFileParser = $uploadedFileParserFactory->createUploadedFileParser();
        $this->serverRequestFactory = new ServerRequestFactory(
            $streamFactory,
            $this->uploadedFileParser,
            new RequestMethodService(),
            new ServerHeadersParser(),
            new ServerDataParser(),
            new UriFactory(),
        );
    }

    /**
     * Create ServerRequestInterface from server data (usually $_SERVER).
     *
     * All parameters required by ServerRequestFactory.createServerRequest are handled locally.
     *
     * @param array<int,string> $allowedHosts
     * Following abomination needed in order to be contravariant with PSR method definitions.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $cookieParams
     * @param mixed[]|object|null $parsedBody
     * @param mixed[] $queryParams
     * @param mixed[] $serverParams
     * @param array<int|string,mixed> $uploadedFiles
     * @phpcs:enable
     */
    public function createServerRequestFromServerData(
        array $allowedHosts = [],
        array $cookieParams = [],
        mixed $parsedBody = null,
        array $queryParams = [],
        array $serverParams = [],
        array $uploadedFiles = [],
    ): ServerRequestInterface {

        $serverParamsProcessor = $this->serverParamsProcFactory->createServerParamsProcessor(
            $allowedHosts,
            $serverParams,
        );

        $serverRequest = $this->serverRequestFactory->createServerRequest(
            // method
            $serverParamsProcessor->processMethod(),
            // uri
            $serverParamsProcessor->processUri(),
            // serverParams
            $serverParamsProcessor->processParams(),
        );
        if (in_array(PHP_SAPI, ['cli', 'cgi-fcgi'], true)) {
            // Nothing else to do.
            return $serverRequest;
        }

        // Add web server data to the request.
        return $serverRequest
        ->withCookieParams($cookieParams)
        ->withParsedBody($parsedBody)
        ->withQueryParams($queryParams)
        ->withUploadedFiles(
            $this->uploadedFileParser->parseSuperglobalUploadedFiles($uploadedFiles),
        );
    }
}
