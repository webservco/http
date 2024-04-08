<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message\Request;

use Psr\Http\Message\RequestInterface;

interface RequestBodyServiceInterface
{
    public function canHaveRequestBody(RequestInterface $request): bool;
}
