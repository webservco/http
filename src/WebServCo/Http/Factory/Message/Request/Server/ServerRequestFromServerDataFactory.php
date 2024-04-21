<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message\Request\Server;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;
use WebServCo\Http\Contract\Message\Request\RequestBodyServiceInterface;
use WebServCo\Http\Contract\Message\Request\RequestContentTypeProcessorInterface;
use WebServCo\Http\Contract\Message\Request\Server\ServerRequestFromServerDataFactoryInterface;
use WebServCo\Http\Contract\Message\UploadedFileParserInterface;
use WebServCo\Http\Factory\Message\Request\RequestBodyServiceFactory;
use WebServCo\Http\Factory\Message\Request\RequestContentTypeProcessorFactory;
use WebServCo\Http\Factory\Message\Stream\StreamFactory;
use WebServCo\Http\Factory\Message\UploadedFileParserFactory;
use WebServCo\Http\Factory\Message\UriFactory;
use WebServCo\Http\Service\Message\Request\Method\RequestMethodService;
use WebServCo\Http\Service\Message\Request\Server\ServerDataParser;

use function fopen;
use function in_array;
use function is_resource;

use const PHP_SAPI;

/**
 * Helper to create custom ServerRequestInterface implementations.
 */
final class ServerRequestFromServerDataFactory implements ServerRequestFromServerDataFactoryInterface
{
    private RequestContentTypeProcessorInterface $requestContentTypeProcessor;

    private RequestBodyServiceInterface $requestBodyService;

    private ServerRequestFactoryInterface $serverRequestFactory;

    private UploadedFileParserInterface $uploadedFileParser;

    public function __construct(
        RequestContentTypeProcessorFactory $contentTypeProcFactory = new RequestContentTypeProcessorFactory(),
        RequestBodyServiceFactory $requestBodyServiceFactory = new RequestBodyServiceFactory(),
        private ServerParamsProcessorFactory $serverParamsProcFactory = new ServerParamsProcessorFactory(),
        private StreamFactoryInterface $streamFactory = new StreamFactory(),
    ) {
        $this->requestContentTypeProcessor = $contentTypeProcFactory->createRequestContentTypeProcessor();
        $this->requestBodyService = $requestBodyServiceFactory->createRequestBodyService();
        $uploadedFileParserFactory = new UploadedFileParserFactory($streamFactory);
        $this->uploadedFileParser = $uploadedFileParserFactory->createUploadedFileParser();
        $this->serverRequestFactory = new ServerRequestFactory(
            $streamFactory,
            $this->uploadedFileParser,
            new RequestMethodService(),
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
        $serverRequest = $serverRequest
        ->withCookieParams($cookieParams)
        ->withParsedBody($parsedBody)
        ->withQueryParams($queryParams)
        ->withUploadedFiles(
            $this->uploadedFileParser->parseSuperglobalUploadedFiles($uploadedFiles),
        );

        /**
         * Handle non form data request body.
         */
        return $this->processRequestBody($serverRequest);
    }

    /**
     * Create request body from request.
     */
    private function createRequestBody(ServerRequestInterface $serverRequest): ?StreamInterface
    {
        switch ($this->requestContentTypeProcessor->getRequestContentType($serverRequest->getServerParams())) {
            // Already available in $_POST data
            case 'application/x-www-form-urlencoded':
            case 'multipart/form-data':
                return null;
            /**
             * "php://input is not available in POST requests with enctype="multipart/form-data"
             * if enable_post_data_reading option is enabled."
             */
            default:
                $resource = fopen('php://input', 'r');
                if (!is_resource($resource)) {
                    throw new UnexpectedValueException('Resource is not a resource object.');
                }

                return $this->streamFactory->createStreamFromResource($resource);
        }
    }

    private function processRequestBody(ServerRequestInterface $serverRequest): ServerRequestInterface
    {
        if (!$this->requestBodyService->canHaveRequestBody($serverRequest)) {
            return $serverRequest;
        }

        $requestBody = $this->createRequestBody($serverRequest);

        if ($requestBody === null) {
            return $serverRequest;
        }

        return $serverRequest->withBody($requestBody);
    }
}
