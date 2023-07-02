<?php

declare(strict_types=1);

namespace Tests\Unit\WebServCo\Http\Service\Message\Request\Server;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use WebServCo\DataTransfer\DataTransferObject\KeyValue\StringString;
use WebServCo\Http\Factory\Message\Request\Server\ServerRequestFactory;
use WebServCo\Http\Factory\Message\Stream\StreamFactory;
use WebServCo\Http\Factory\Message\UploadedFileFactory;
use WebServCo\Http\Factory\Message\UriFactory;
use WebServCo\Http\Service\Message\AbstractMessage;
use WebServCo\Http\Service\Message\Request\AbstractRequest;
use WebServCo\Http\Service\Message\Request\Method\RequestMethodService;
use WebServCo\Http\Service\Message\Request\Server\ServerDataParser;
use WebServCo\Http\Service\Message\Request\Server\ServerHeadersAcceptProcessor;
use WebServCo\Http\Service\Message\Request\Server\ServerHeadersParser;
use WebServCo\Http\Service\Message\Request\Server\ServerRequest;
use WebServCo\Http\Service\Message\Stream\AbstractStream;
use WebServCo\Http\Service\Message\UploadedFileParser;
use WebServCo\Http\Service\Message\Uri;

#[CoversClass(ServerHeadersAcceptProcessor::class)]
#[UsesClass(AbstractMessage::class)]
#[UsesClass(AbstractRequest::class)]
#[UsesClass(AbstractStream::class)]
#[UsesClass(ServerDataParser::class)]
#[UsesClass(ServerHeadersParser::class)]
#[UsesClass(ServerRequest::class)]
#[UsesClass(ServerRequestFactory::class)]
#[UsesClass(StreamFactory::class)]
#[UsesClass(StringString::class)]
#[UsesClass(Uri::class)]
#[UsesClass(UploadedFileParser::class)]
#[UsesClass(UriFactory::class)]
final class ServerHeadersAcceptProcessorTest extends TestCase
{
    private const HTTP_ACCEPT = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';

    public function testGetAcceptStringWorks(): void
    {
        $serverRequest = $this->createServerRequest('foo', 'bar', ['HTTP_ACCEPT' => self::HTTP_ACCEPT]);

        $serverHeadersAcceptProcessor = new ServerHeadersAcceptProcessor();

        self::assertEquals(self::HTTP_ACCEPT, $serverHeadersAcceptProcessor->getAcceptHeaderValue($serverRequest));
    }

    public function testGetAcceptHeaderValueWorks(): void
    {
        $serverHeadersAcceptProcessor = new ServerHeadersAcceptProcessor();

        $expected = [
            '0.8.1' => '*/*',
            '0.9.4' => 'application/xml',
            '1.2' => 'image/webp',
            '1.3' => 'image/avif',
            '1.5' => 'application/xhtml+xml',
            '1.6' => 'text/html',
        ];
        self::assertEquals($expected, $serverHeadersAcceptProcessor->processAcceptList(self::HTTP_ACCEPT));
    }

    /**
     * @param array<string,string> $serverParams
     */
    private function createServerRequest(
        string $method,
        string|UriInterface $uri,
        array $serverParams = [],
    ): ServerRequestInterface {
        $streamFactory = new StreamFactory();
        $serverRequestFactory = new ServerRequestFactory(
            $streamFactory,
            new UploadedFileParser($streamFactory, new UploadedFileFactory()),
            new RequestMethodService(),
            new ServerHeadersParser(),
            new ServerDataParser(),
            new UriFactory(),
        );

        return $serverRequestFactory->createServerRequest($method, $uri, $serverParams);
    }
}
