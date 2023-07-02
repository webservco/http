<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message\Request\Server;

use Psr\Http\Message\UriInterface;

interface ServerParamsProcessorInterface
{
    /**
     * Returns the processed method from the server data.
     */
    public function processMethod(): string;

    /**
     * Returns the processed parameters from the server data.
     *
     * @return array<string,array<int,string>|scalar|null>
     */
    public function processParams(): array;

    /**
     * Returns the processed Uri from the server data.
     */
    public function processUri(): UriInterface;
}
