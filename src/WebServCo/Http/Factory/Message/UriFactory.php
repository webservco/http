<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message;

use Override;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use WebServCo\Http\Service\Message\Uri;

final class UriFactory implements UriFactoryInterface
{
    #[Override]
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
