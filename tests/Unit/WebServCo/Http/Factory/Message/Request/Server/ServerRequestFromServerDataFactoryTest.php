<?php

declare(strict_types=1);

namespace Tests\Unit\WebServCo\Http\Factory\Message\Request\Server;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionMethod;
use WebServCo\Http\Factory\Message\Request\Server\ServerParamsProcessorFactory;
use WebServCo\Http\Factory\Message\Request\Server\ServerRequestFactory;
use WebServCo\Http\Factory\Message\Request\Server\ServerRequestFromServerDataFactory;
use WebServCo\Http\Factory\Message\Stream\StreamFactory;
use WebServCo\Http\Factory\Message\UploadedFileFactory;
use WebServCo\Http\Factory\Message\UploadedFileParserFactory;
use WebServCo\Http\Service\Message\Stream\AbstractStream;
use WebServCo\Http\Service\Message\Stream\Stream;
use WebServCo\Http\Service\Message\UploadedFileParser;

/**
 * The public method returns early in CLI, so the body handling is tested directly.
 */
#[CoversClass(ServerRequestFromServerDataFactory::class)]
#[UsesClass(AbstractStream::class)]
#[UsesClass(ServerParamsProcessorFactory::class)]
#[UsesClass(ServerRequestFactory::class)]
#[UsesClass(Stream::class)]
#[UsesClass(StreamFactory::class)]
#[UsesClass(UploadedFileFactory::class)]
#[UsesClass(UploadedFileParser::class)]
#[UsesClass(UploadedFileParserFactory::class)]
final class ServerRequestFromServerDataFactoryTest extends TestCase
{
    #[DataProvider('contentTypeProvider')]
    public function testCreateRequestBody(?string $contentType, bool $expectBody): void
    {
        $method = new ReflectionMethod(ServerRequestFromServerDataFactory::class, 'createRequestBody');
        $requestBody = $method->invoke(
            new ServerRequestFromServerDataFactory(),
            $this->createServerRequest($contentType),
        );

        if ($expectBody) {
            self::assertInstanceOf(StreamInterface::class, $requestBody);

            return;
        }

        self::assertNull($requestBody);
    }

    /**
     * Content-Type header value (null: header missing), whether the body is read from php://input.
     *
     * @return array<string,array{0:?string,1:bool}>
     */
    public static function contentTypeProvider(): array
    {
        return [
            'form urlencoded' => ['application/x-www-form-urlencoded', false],
            'form urlencoded with charset' => ['application/x-www-form-urlencoded; charset=UTF-8', false],
            'json' => ['application/json', true],
            'missing header' => [null, true],
            'multipart mixed case' => ['Multipart/Form-Data; boundary=----abc', false],
            'multipart with boundary' => ['multipart/form-data; boundary=----abc', false],
        ];
    }

    private function createServerRequest(?string $contentType): ServerRequestInterface
    {
        $serverRequest = self::createStub(ServerRequestInterface::class);
        $serverRequest->method('hasHeader')->willReturn($contentType !== null);
        $serverRequest->method('getHeader')->willReturn($contentType === null ? [] : [$contentType]);
        $serverRequest->method('getHeaderLine')->willReturn($contentType ?? '');

        return $serverRequest;
    }
}
