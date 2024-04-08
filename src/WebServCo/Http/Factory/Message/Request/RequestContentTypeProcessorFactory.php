<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message\Request;

use WebServCo\Http\Contract\Message\Request\RequestContentTypeProcessorInterface;
use WebServCo\Http\Service\Message\Request\RequestContentTypeProcessor;

final class RequestContentTypeProcessorFactory
{
    public function createRequestContentTypeProcessor(): RequestContentTypeProcessorInterface
    {
        return new RequestContentTypeProcessor();
    }
}
