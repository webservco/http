<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message\Request;

use WebServCo\Http\Contract\Message\Request\RequestHeaderServiceInterface;
use WebServCo\Http\Service\Message\Request\RequestHeaderService;

final class RequestHeaderServiceFactory
{
    public function createRequestHeaderService(): RequestHeaderServiceInterface
    {
        return new RequestHeaderService();
    }
}
