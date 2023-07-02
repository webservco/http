<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use WebServCo\Http\Contract\Message\Response\StatusCodeServiceInterface;
use WebServCo\Http\Service\Message\AbstractMessage;

final class Response extends AbstractMessage implements ResponseInterface
{
    public function __construct(
        private StatusCodeServiceInterface $statusCodeService,
        StreamInterface $body,
        private int $statusCode,
        private string $reasonPhrase = '',
    ) {
        parent::__construct($body);

        $this->reasonPhrase = $this->processReasonPhrase($statusCode, $reasonPhrase);
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * @param int $code
     * @param string $reasonPhrase
     * @return static
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function withStatus($code, $reasonPhrase = ''): self
    {
        $this->statusCodeService->validateStatusCode($code);

        $clone = clone $this;
        $clone->statusCode = $code;
        $clone->reasonPhrase = $this->processReasonPhrase($code, $reasonPhrase);

        return $clone;
    }

    private function processReasonPhrase(int $statusCode, string $reasonPhrase = ''): string
    {
        return $reasonPhrase === ''
        ? $this->statusCodeService->getReasonPhraseByStatusCode($statusCode)
        : $reasonPhrase;
    }
}
