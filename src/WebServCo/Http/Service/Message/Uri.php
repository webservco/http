<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

use function is_int;
use function is_string;
use function parse_url;
use function sprintf;
use function strtolower;

use const PHP_URL_FRAGMENT;
use const PHP_URL_HOST;
use const PHP_URL_PASS;
use const PHP_URL_PATH;
use const PHP_URL_PORT;
use const PHP_URL_QUERY;
use const PHP_URL_SCHEME;
use const PHP_URL_USER;

final class Uri implements UriInterface
{
    private string $fragment;
    private string $host;
    private ?int $port;
    private string $path;
    private string $query;
    private string $scheme;
    private string $user;
    private ?string $password;

    public function __construct(string $url)
    {
        $this->fragment = (string) $this->parseUrlString($url, PHP_URL_FRAGMENT);

        /**
         * "The value returned MUST be normalized to lowercase, per RFC 3986 Section 3.2.2."
         */
        $this->host = strtolower((string) $this->parseUrlString($url, PHP_URL_HOST));

        $this->port = $this->parseUrlPort($url);

        $this->path = (string) $this->parseUrlString($url, PHP_URL_PATH);

        $this->query = (string) $this->parseUrlString($url, PHP_URL_QUERY);

        /**
         * "The value returned MUST be normalized to lowercase, per RFC 3986 Section 3.1."
         */
        $this->scheme = strtolower((string) $this->parseUrlString($url, PHP_URL_SCHEME));

        $this->user = (string) $this->parseUrlString($url, PHP_URL_USER);

        $this->password = $this->parseUrlString($url, PHP_URL_PASS);
    }

    public function getAuthority(): string
    {
        $port = $this->getPort();
        $userInfo = $this->getUserInfo();

        return sprintf(
            '%s%s%s',
            $userInfo !== ''
                ? sprintf('%s@', $userInfo)
                : '',
            $this->getHost(),
            is_int($port)
                ? sprintf(':%d', $port)
                : '',
        );
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPort(): int|null
    {
        return $this->port;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getUserInfo(): string
    {
        return sprintf(
            '%s%s',
            $this->user,
            $this->password !== null
                ? sprintf(':%s', $this->password)
                : '',
        );
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * @param string $fragment
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withFragment($fragment): self
    {
        if ($fragment === $this->fragment) {
            return $this;
        }
        $clone = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }

    /**
     * Return an instance with the specified host.
     *
     * @param string $host
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withHost($host): self
    {
        if ($host === $this->host) {
            return $this;
        }
        $clone = clone $this;
        $clone->host = $host;

        return $clone;
    }

    /**
     * Return an instance with the specified port.
     *
     * @param int|null $port
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withPort($port): self
    {
        if ($port === $this->port) {
            return $this;
        }
        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
     * Return an instance with the specified path.
     *
     * @param string $path
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withPath($path): self
    {
        if ($path === $this->path) {
            return $this;
        }
        $clone = clone $this;
        $clone->path = $path;

        return $clone;
    }

    /**
     * Return an instance with the specified query string.
     *
     * @param string $query
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withQuery($query): self
    {
        if ($query === $this->query) {
            return $this;
        }
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    /**
     * Return an instance with the specified scheme.
     *
     * @param string $scheme
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withScheme($scheme): self
    {
        if ($scheme === $this->scheme) {
            return $this;
        }
        $clone = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    /**
     * Return an instance with the specified user information.
     *
     * @param string $user
     * @param string|null $password
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withUserInfo($user, $password = null): self
    {
        if ($user === $this->user && $password === $this->password) {
            return $this;
        }
        $clone = clone $this;
        $clone->user = $user;
        $clone->password = $password;

        return $clone;
    }

    private function parseUrlComponent(string $url, int $component): int|bool|string|null
    {
        switch ($component) {
            case PHP_URL_FRAGMENT:
                return parse_url($url, PHP_URL_FRAGMENT);
            case PHP_URL_HOST:
                return parse_url($url, PHP_URL_HOST);
            case PHP_URL_PASS:
                return parse_url($url, PHP_URL_PASS);
            case PHP_URL_PATH:
                return parse_url($url, PHP_URL_PATH);
            case PHP_URL_PORT:
                return parse_url($url, PHP_URL_PORT);
            case PHP_URL_QUERY:
                return parse_url($url, PHP_URL_QUERY);
            case PHP_URL_SCHEME:
                /**
                 * PHPStan false positive:
                 * "Method WebServCo\Http\Service\Message\Uri::parseUrlComponent() should return
                 * bool|int|string|null but returns array<string, int|string>|false."
                 * https://phpstan.org/r/1fe9cbdd-0a14-4922-9330-c39431615640
                 *
                 * @phpstan-ignore return.type (PHPStan bug)
                 */
                return parse_url($url, PHP_URL_SCHEME);
            case PHP_URL_USER:
                return parse_url($url, PHP_URL_USER);
            default:
                throw new InvalidArgumentException('Invalid component specified.');
        }
    }

    private function parseUrlPort(string $url): ?int
    {
        $result = $this->parseUrlComponent($url, PHP_URL_PORT);

        if ($result === false) {
            throw new InvalidArgumentException('Unable to parse url.');
        }

        if ($result === null) {
            return null;
        }

        if (!is_int($result)) {
            throw new InvalidArgumentException('Result is not an integer.');
        }

        return $result;
    }

    private function parseUrlString(string $url, int $component): ?string
    {
        $result = $this->parseUrlComponent($url, $component);

        if ($result === false) {
            throw new InvalidArgumentException('Unable to parse url.');
        }

        if ($result === null) {
            return null;
        }

        if (!is_string($result)) {
            throw new InvalidArgumentException('Result is not a string.');
        }

        return $result;
    }

    public function __toString(): string
    {
        $string = '';
        if ($this->scheme !== '') {
            $string .= sprintf('%s:', $this->scheme);
        }
        $authority = $this->getAuthority();
        if ($authority !== '') {
            $string .= sprintf('//%s', $authority);
        }
        $string .= $this->path;
        if ($this->query !== '') {
            $string .= sprintf('?%s', $this->query);
        }
        if ($this->fragment !== '') {
            $string .= sprintf('#%s', $this->fragment);
        }

        return $string;
    }
}
