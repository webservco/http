<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Request\Server;

use LogicException;
use OutOfBoundsException;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use UnexpectedValueException;
use WebServCo\Http\Contract\Message\Request\Method\RequestMethodServiceInterface;
use WebServCo\Http\Contract\Message\Request\Server\ServerDataParserInterface;
use WebServCo\Http\Contract\Message\Request\Server\ServerParamsProcessorInterface;

use function array_key_exists;
use function in_array;
use function is_string;
use function sprintf;

final class ServerParamsProcessor implements ServerParamsProcessorInterface
{
    /**
     * Server.
     *
     * @var array<string,array<int,string>|scalar|null>
     */
    private array $serverParams;

    /**
     * Server service.
     *
     * @param array<int,string> $allowedHosts
     * Following abomination needed in order to be contravariant with PSR method definition.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $rawServerParams
     * @phpcs:enable
     */
    public function __construct(
        private RequestMethodServiceInterface $requestMethodService,
        private ServerDataParserInterface $serverDataParser,
        private UriFactoryInterface $uriFactory,
        private array $allowedHosts = [],
        array $rawServerParams = [],
    ) {
        $this->serverParams = $this->serverDataParser->parseServerParams($rawServerParams);
    }

    /**
     * Returns the processed method from the server data.
     */
    public function processMethod(): string
    {
        if (array_key_exists('REQUEST_METHOD', $this->serverParams)) {
            $method = $this->serverParams['REQUEST_METHOD'];
            if (!is_string($method)) {
                throw new UnexpectedValueException('Request method is not a string.');
            }
            if (!$this->requestMethodService->validateMethod($method)) {
                throw new UnexpectedValueException('Invalid request method.');
            }

            return $method;
        }

        /**
         * If we arrive here the request method is not available.
         * This could indicate CLI run.
         * At the time of development not yet decided how to handle this, so throw an exception.
         */
        throw new LogicException('Unable to determine request method');
    }

    /**
     * Returns the processed parameters from the server data.
     *
     * @return array<string,array<int,string>|scalar|null>
     */
    public function processParams(): array
    {
        return $this->serverParams;
    }

    /**
     * Returns the processed Uri from the server data.
     */
    public function processUri(): UriInterface
    {
        return $this->uriFactory->createUri(
            sprintf(
                '%s://%s%s',
                $this->getRequestScheme(),
                $this->getServerName(),
                $this->getServerParamStringValue('REQUEST_URI'),
            ),
        );
    }

    private function getRequestScheme(): string
    {

        /**
         * Note: as the name implies, this is the protocol of the server,
         * not the one of the request.
         * Eg.
         * request: > GET / HTTP/1.0
         * response: < HTTP/1.1 404 Not Found
         * SERVER_PROTOCOL = HTTP/1.1
         */
        $serverProtocol = $this->getServerParamStringValue('SERVER_PROTOCOL');
        if (!in_array($serverProtocol, ['HTTP/1.1', 'HTTP/2.0'], true)) {
            throw new LogicException('Unsupported server protocol.');
        }

        /**
         * At this point we know the server protocol is HTTP,
         * we just need to determine if SSL is used.
         */

        if ($this->isHttps() || $this->isForwardedSsl()) {
            return 'https';
        }

        // Nothing more to check, assume 'http'.
        return 'http';
    }

    /**
     * "The name of the server host under which the current script is executing".
     *
     * This is the doman name, or host name of the application.
     * `SERVER_NAME` is used because it seems to be the most reliable.
     * Eg. in `ddev` it's the only one that contains the correct host name.
     * For security reasons the obtained name must be in a list of allowed hosts.
     *
     * @see https://www.php.net/manual/en/reserved.variables.server.php
     */
    private function getServerName(): string
    {
        $serverName = $this->getServerParamStringValue('SERVER_NAME');

        if (in_array($serverName, $this->allowedHosts, true)) {
            return $serverName;
        }

        throw new OutOfBoundsException('Server name not in allowed hosts list.');
    }

    /**
     * Get a string value from the serverParams array with existence and type validation.
     */
    private function getServerParamStringValue(string $key): string
    {
        if (!array_key_exists($key, $this->serverParams)) {
            throw new OutOfBoundsException('Requested key not found in storage.');
        }

        $value = $this->serverParams[$key];

        if (!is_string($value)) {
            throw new UnexpectedValueException('Value is not a string');
        }

        return $value;
    }

    private function isForwardedSsl(): bool
    {
        try {
            $forwardedSsl = $this->getServerParamStringValue('HTTP_X_FORWARDED_SSL');

            return $forwardedSsl === 'on';
        } catch (OutOfBoundsException) {
            // Key not found in storage.

            return false;
        }
    }

    private function isHttps(): bool
    {
        try {
            $https = $this->getServerParamStringValue('HTTPS');

            return $https === 'on';
        } catch (OutOfBoundsException) {
            // Key not found in storage.

            return false;
        }
    }
}
