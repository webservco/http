<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message\Request;

use WebServCo\Http\Contract\Message\Request\RequestBodyServiceInterface;
use WebServCo\Http\Service\Message\Request\RequestBodyService;

final class RequestBodyServiceFactory
{
    public function createRequestBodyService(): RequestBodyServiceInterface
    {
        return new RequestBodyService();
    }
}
