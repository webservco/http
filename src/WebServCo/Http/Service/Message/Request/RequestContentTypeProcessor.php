<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Request;

use LogicException;
use Override;
use WebServCo\Http\Contract\Message\Request\RequestContentTypeProcessorInterface;

use function array_key_exists;
use function is_string;

final class RequestContentTypeProcessor implements RequestContentTypeProcessorInterface
{
    private const HEADER_KEY = 'Content-Type';

    /**
     * @param array<string,array<int,string>|scalar|null> $processedServerParams
     */
    #[Override]
    public function getRequestContentType(array $processedServerParams): string
    {
        // Try in processed $_SERVER data.
        foreach (['CONTENT_TYPE', 'HTTP_CONTENT_TYPE'] as $searchKey) {
            if (array_key_exists($searchKey, $processedServerParams) && is_string($processedServerParams[$searchKey])) {
                return $processedServerParams[$searchKey];
            }
        }

        /**
         * Try in Apache headers.
         *
         * "Works in the Apache, FastCGI, CLI, and FPM webservers. "
         */
        $apacheRequestHeaders = apache_request_headers();

        if (array_key_exists(self::HEADER_KEY, $apacheRequestHeaders)) {
            return $apacheRequestHeaders[self::HEADER_KEY];
        }

        // Give up.
        throw new LogicException('Unable to determine request content type.');
    }
}
