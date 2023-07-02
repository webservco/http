<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message\Response;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use WebServCo\Http\Contract\Message\Response\StatusCodeServiceInterface;
use WebServCo\Http\Service\Message\Response\Response;

final class ResponseFactory implements ResponseFactoryInterface
{
    public function __construct(
        private StatusCodeServiceInterface $statusCodeService,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response(
            $this->statusCodeService,
            /**
             * PSR-17 does not specify a body parameter, despite PSR-7 requiring the body to not be null.
             * So we need to create an empty request body.
             * \Psr\Http\Message\StreamInterface
             */
            $this->streamFactory->createStream(''),
            $code,
            $reasonPhrase,
        );
    }
}
