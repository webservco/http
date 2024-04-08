<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message\Request;

interface RequestContentTypeProcessorInterface
{
    /**
     * @param array<string,array<int,string>|scalar|null> $processedServerParams
     */
    public function getRequestContentType(array $processedServerParams): string;
}
