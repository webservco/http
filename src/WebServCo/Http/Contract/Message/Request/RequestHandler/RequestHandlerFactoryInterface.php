<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message\Request\RequestHandler;

use Psr\Http\Server\RequestHandlerInterface;

interface RequestHandlerFactoryInterface
{
    public function createRequestHandler(): RequestHandlerInterface;
}
