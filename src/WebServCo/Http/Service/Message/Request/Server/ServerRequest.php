<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Request\Server;

use OutOfBoundsException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use UnexpectedValueException;
use WebServCo\Http\Contract\Message\Request\Method\RequestMethodServiceInterface;
use WebServCo\Http\Contract\Message\Request\Server\ServerDataParserInterface;
use WebServCo\Http\Contract\Message\UploadedFileParserInterface;
use WebServCo\Http\Service\Message\Request\AbstractRequest;

use function apache_request_headers;
use function array_key_exists;
use function array_keys;
use function function_exists;
use function is_scalar;
use function strtolower;

/**
 * "Representation of an incoming, server-side HTTP request."
 */
final class ServerRequest extends AbstractRequest implements ServerRequestInterface
{
    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @var array<string,mixed>
     */
    private array $attributes;

    /**
     * Cookie.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @var array<mixed>
     * @phpcs:enable
    */
    private array $cookieParams;

    /**
     * Parsed body.
     *
     * @var array<string,string>|object|null
     */
    private array|object|null $parsedBody;

    /**
     * Query
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @var array<mixed>
     * @phpcs:enable
     */
    private array $queryParams;

    /**
     * Server.
     *
     * @var array<string,array<int,string>|scalar|null>
     */
    private array $serverParams;

    /**
     * Files.
     *
     * @var array<string,array<int,\Psr\Http\Message\UploadedFileInterface>>
     */
    private array $uploadedFiles;

    /**
     * ServerRequest.
     *
     * Following abomination needed in order to be contravariant with PSR method definition.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $serverParams
     * @phpcs:enable
     */
    public function __construct(
        private ServerDataParserInterface $serverDataParser,
        private UploadedFileParserInterface $uploadedFileParser,
        RequestMethodServiceInterface $requestMethodService,
        StreamInterface $body,
        UriInterface $uri,
        string $method,
        array $serverParams = [],
    ) {
        parent::__construct($requestMethodService, $body, $uri, $method);

        $this->attributes = [];
        $this->cookieParams = [];
        $this->parsedBody = null;
        $this->queryParams = [];
        $this->serverParams = $this->serverDataParser->parseServerParams($serverParams);
        $this->uploadedFiles = [];

        // Process server request specific headers.
        $this->headers = $this->processHeaders();

        // Set header mapping.
        foreach (array_keys($this->headers) as $field) {
            $this->headersMap[strtolower($field)] = $field;
        }
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * @param string $name
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param mixed $default
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function getAttribute($name, $default = null): mixed
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @return array<string,mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Retrieve cookies.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @return array<mixed>
     * @phpcs:enable
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * @return array<string,string>|object|null
     */
    public function getParsedBody(): array|object|null
    {
        return $this->parsedBody;
    }

    /**
     * Retrieve query string arguments.
     *
     * Array can be multidimensional, hence the mixed type.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @return array<mixed>
     * @phpcs:enable
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * Retrieve server parameters.
     *
     * @return array<string,array<int,string>|scalar|null>
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * @return array<string,array<int,\Psr\Http\Message\UploadedFileInterface>>
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * @param string $name
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param mixed $value
     * @return static
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withAttribute($name, $value): self
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * @param string $name
     */

    /**
     * Return an instance with the specified cookies.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $cookies
     * @phpcs:enable
     * @return static
     */
    public function withCookieParams(array $cookies): self
    {
        $clone = clone $this;
        $clone->cookieParams = $this->serverDataParser->parseCookieQueryParams($cookies);

        return $clone;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * @param string $name
     * @return static
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withoutAttribute($name): self
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param mixed[]|object|null $data
     * @phpcs:enable
     * @return static
     */
    public function withParsedBody(mixed $data): self
    {
        $clone = clone $this;
        $clone->parsedBody = $this->serverDataParser->parseBodyData($data);

        return $clone;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $query
     * @phpcs:enable
     * @return static
     */
    public function withQueryParams(array $query): self
    {
        $clone = clone $this;
        $clone->queryParams = $this->serverDataParser->parseCookieQueryParams($query);

        return $clone;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * $uploadedFiles should be "An array tree of UploadedFileInterface instances."
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $uploadedFiles
     * @phpcs:enable
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles): self
    {
        $clone = clone $this;
        $clone->uploadedFiles = $this->uploadedFileParser->parsePsrUploadedFiles($uploadedFiles);

        return $clone;
    }

    /**
     * "PhanUndeclaredFunction Call to undeclared function \apache_request_headers()"
     *
     * @return array<string,array<string>>
     * @suppress PhanUndeclaredFunction
     */
    private function processHeaders(): array
    {
        $headers = [];

        // "Works in the Apache, FastCGI, CLI, and FPM webservers. "
        if (!function_exists('apache_request_headers')) {
            throw new OutOfBoundsException('Function apache_request_headers does not exist.');
        }

        /**
         * Psalm error: MixedAssignment "Unable to determine the type that $.. is being assigned to"
         * However this is indeed mixed, no solution but to suppress error.
         *
         * Psalm error: PossiblyFalseIterator
         * "Cannot iterate over falsable var array<array-key, mixed>|false (see https://psalm.dev/164)"
         * However, false is not returned: https://www.php.net/apache_request_headers
         *
         * @psalm-suppress MixedAssignment,PossiblyFalseIterator
         */
        foreach (apache_request_headers() as $name => $value) {
            if (!is_scalar($value)) {
                throw new UnexpectedValueException('Header value is not scalar.');
            }
            $headers[(string) $name][] = (string) $value;
        }

        return $headers;
    }
}
