<?php

declare(strict_types=1);

namespace WebServCo\Http\Factory\Message\Request\Server;

use Psr\Http\Message\UriFactoryInterface;
use WebServCo\Http\Contract\Message\Request\Method\RequestMethodServiceInterface;
use WebServCo\Http\Contract\Message\Request\Server\ServerDataParserInterface;
use WebServCo\Http\Contract\Message\Request\Server\ServerParamsProcessorInterface;
use WebServCo\Http\Factory\Message\UriFactory;
use WebServCo\Http\Service\Message\Request\Method\RequestMethodService;
use WebServCo\Http\Service\Message\Request\Server\ServerDataParser;
use WebServCo\Http\Service\Message\Request\Server\ServerParamsProcessor;

final class ServerParamsProcessorFactory
{
    public function __construct(
        private RequestMethodServiceInterface $requestMethodService = new RequestMethodService(),
        private ServerDataParserInterface $serverDataParser = new ServerDataParser(),
        private UriFactoryInterface $uriFactory = new UriFactory(),
    ) {
    }

    /**
     * @param array<int,string> $allowedHosts
     * Following abomination needed in order to be contravariant with PSR method definition.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $serverParams
     * @phpcs:enable
     */
    public function createServerParamsProcessor(
        array $allowedHosts,
        array $serverParams = [],
    ): ServerParamsProcessorInterface {
        return new ServerParamsProcessor(
            $this->requestMethodService,
            $this->serverDataParser,
            $this->uriFactory,
            $allowedHosts,
            $serverParams,
        );
    }
}
