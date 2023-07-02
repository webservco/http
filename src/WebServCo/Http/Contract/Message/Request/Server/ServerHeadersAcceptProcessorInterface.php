<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message\Request\Server;

use Psr\Http\Message\ServerRequestInterface;

interface ServerHeadersAcceptProcessorInterface
{
    public function getAcceptHeaderValue(ServerRequestInterface $request): string;

    /**
     * @return array<string,string>
     */
    public function processAcceptList(string $acceptHeaderValue): array;
}
