# webservco/http

A minimalist PHP PSR implementation.

---

## Implements

- [PSR-7: HTTP message interfaces](https://www.php-fig.org/psr/psr-7/)
- [PSR-17: HTTP Factories](https://www.php-fig.org/psr/psr-17/)

---

## Provides

- `psr/http-factory-implementation`
- `psr/http-message-implementation`

---

## Troubleshooting

### `php-http/discovery`

If using a project/library that requires `php-http/discovery`, a list of 9 "well-known" implementations is forced.

Workaround to install custom implementations of your choice:

1) Create custom strategy - implement `Http\Discovery\Strategy\DiscoveryStrategy`:

```php
<?php

declare(strict_types=1);

namespace Project\Factory\Http\Discovery;

use Http\Discovery\Strategy\DiscoveryStrategy;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
// A RequestFactoryInterface implementation of your choice:
use WebServCo\Http\Factory\Message\Request\RequestFactory;
// A StreamFactoryInterface implementation of your choice: 
use WebServCo\Http\Factory\Message\Stream\StreamFactory;

final class Psr17DiscoveryStrategy implements DiscoveryStrategy
{
    /**
     * @inheritDoc
     * @param string $type
     * @return array<array<string, string>>
     */
    public static function getCandidates($type)
    {
        if ($type === RequestFactoryInterface::class) {
            return [
                [
                    'class' => RequestFactory::class,
                ],
            ];
        }

        if ($type === StreamFactoryInterface::class) {
            return [
                [
                    'class' => StreamFactory::class,
                ],
            ];
        }

        return [];
    }
}
```

2) Use custom strategy:

```php
use Http\Discovery\ClassDiscovery;
use Project\Factory\Http\Discovery\Psr17DiscoveryStrategy;

// Before instantiating the class that uses `php-http/discovery`
ClassDiscovery::prependStrategy(Psr17DiscoveryStrategy::class);
```

- Workaround credit: [Popus Razvan Adrian](https://github.com/punkrock34/)
- Documentation: [Strategies](https://docs.php-http.org/en/latest/discovery.html#strategies)

---
