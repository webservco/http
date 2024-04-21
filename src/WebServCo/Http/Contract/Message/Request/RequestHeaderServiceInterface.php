<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message\Request;

use Psr\Http\Message\RequestInterface;

interface RequestHeaderServiceInterface
{
    public function getHeaderValue(string $headerName, RequestInterface $request): string;
}
