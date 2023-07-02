<?php

declare(strict_types=1);

namespace Tests\Unit\WebServCo\Http\Service\Message\Request\Server;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WebServCo\Http\Service\Message\Request\Server\ServerHeadersParser;

#[CoversClass(ServerHeadersParser::class)]
final class ServerHeadersParserTest extends TestCase
{
    public function testParseHeaderFieldAcceptWorks(): void
    {
        $serverHeadersParser = new ServerHeadersParser();
        self::assertEquals('Accept', $serverHeadersParser->parseHeaderField('HTTP_ACCEPT'));
    }

    public function testParseHeaderFieldUserAgentWorks(): void
    {
        $serverHeadersParser = new ServerHeadersParser();
        self::assertEquals('UserAgent', $serverHeadersParser->parseHeaderField('HTTP_USER_AGENT'));
    }
}
