# webservco/http

A PHP component/library. Please see [webservco/sandbox](https://webservco.github.io/sandbox/) for an example project using these types of components.

Minimal implementation of the PSR-7 HTTP message interfaces and the PSR-17 factories.

- Implements: [PSR-7: HTTP message interfaces](https://www.php-fig.org/psr/psr-7/), [PSR-17: HTTP Factories](https://www.php-fig.org/psr/psr-17/)
- Provides: `psr/http-message-implementation`, `psr/http-factory-implementation`
- Related: `webservco/http-request-service`, `webservco/http-response-service` (helper services), `webservco/http-client` (sends requests created here)

---

## Installation

```shell
composer require webservco/http
```

---

## Usage

### Server request from PHP globals

```php
use WebServCo\Http\Factory\Message\Request\Server\ServerRequestFromServerDataFactory;

// \WebServCo\Http\Contract\Message\Request\Server\ServerRequestFromServerDataFactoryInterface
$serverRequestFactory = new ServerRequestFromServerDataFactory();
// \Psr\Http\Message\ServerRequestInterface
$serverRequest = $serverRequestFactory->createServerRequestFromServerData(
    ['example.ddev.site'],
    $_COOKIE,
    $_POST,
    $_GET,
    $_SERVER,
    $_FILES,
);
```

`webservco/application-default` does this for you, with the hosts taken from `ALLOWED_HOSTS`.

### Requests and responses

```php
use WebServCo\Http\Factory\Message\Request\RequestFactory;
use WebServCo\Http\Factory\Message\Response\ResponseFactory;
use WebServCo\Http\Factory\Message\Stream\StreamFactory;
use WebServCo\Http\Service\Message\Response\StatusCodeService;

// PSR-17: \Psr\Http\Message\RequestFactoryInterface
$requestFactory = new RequestFactory();
$request = $requestFactory->createRequest('GET', 'https://example.com/api/items');

$streamFactory = new StreamFactory();
// PSR-17: \Psr\Http\Message\ResponseFactoryInterface
$responseFactory = new ResponseFactory(new StatusCodeService(), $streamFactory);
$response = $responseFactory->createResponse(404);
```

---

## Contracts

| Interface | Implementation | Purpose |
| --- | --- | --- |
| PSR-17 `RequestFactoryInterface` | `Factory\Message\Request\RequestFactory` | Creates a `Service\Message\Request\Request`. |
| PSR-17 `ServerRequestFactoryInterface` | `Factory\Message\Request\Server\ServerRequestFactory` | Creates a `Service\Message\Request\Server\ServerRequest`. |
| PSR-17 `ResponseFactoryInterface` | `Factory\Message\Response\ResponseFactory` | Creates a `Service\Message\Response\Response`. |
| PSR-17 `StreamFactoryInterface` | `Factory\Message\Stream\StreamFactory` | Creates a `Service\Message\Stream\Stream`. |
| PSR-17 `UriFactoryInterface` | `Factory\Message\UriFactory` | Creates a `Service\Message\Uri`. |
| PSR-17 `UploadedFileFactoryInterface` | `Factory\Message\UploadedFileFactory` | Creates a `Service\Message\UploadedFile`. |
| `Contract\Message\Request\Server\ServerRequestFromServerDataFactoryInterface` | `Factory\Message\Request\Server\ServerRequestFromServerDataFactory` | Builds the server request from PHP globals. |
| `Contract\Message\Request\RequestHandler\RequestHandlerFactoryInterface` | your factory | Creates the application's PSR-15 request handler. |

Namespaces are relative to `WebServCo\Http\`.

---

## Notes

The request host comes from `SERVER_NAME`, and it must be in the allowed hosts list or an `OutOfBoundsException` is thrown. This blocks requests with a forged `Host` header.

### Using this implementation with `php-http/discovery`

A library that depends on `php-http/discovery` only looks for a fixed list of 9 "well-known" implementations and won't find this one. Register a discovery strategy to add it:

```php
use Http\Discovery\Strategy\DiscoveryStrategy;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use WebServCo\Http\Factory\Message\Request\RequestFactory;
use WebServCo\Http\Factory\Message\Stream\StreamFactory;

final class Psr17DiscoveryStrategy implements DiscoveryStrategy
{
    /**
     * @param string $type
     * @return array<array<string, string>>
     */
    public static function getCandidates($type)
    {
        return match ($type) {
            RequestFactoryInterface::class => [['class' => RequestFactory::class]],
            StreamFactoryInterface::class => [['class' => StreamFactory::class]],
            default => [],
        };
    }
}
```

```php
use Http\Discovery\ClassDiscovery;

// Before creating the class that uses php-http/discovery.
ClassDiscovery::prependStrategy(Psr17DiscoveryStrategy::class);
```

Workaround credit: [Popus Razvan Adrian](https://github.com/punkrock34/). See also the [discovery strategies documentation](https://docs.php-http.org/en/latest/discovery.html#strategies).

---

## Development

```shell
# Start project.
ddev start
# Install dependencies.
ddev c u
# Run all validations.
ddev c c
# Run unit tests.
ddev c test:dox
```

---

## License

[MIT](LICENSE.md)
