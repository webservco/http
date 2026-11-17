<?php

declare(strict_types=1);

namespace Tests\Unit\WebServCo\Http\Service\Message\Request\Server;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use WebServCo\Http\Service\Message\Request\Server\ServerDataParser;

#[CoversClass(ServerDataParser::class)]
final class ServerDataParserTest extends TestCase
{
    /**
     * Same shape as $_POST for "q=x&tag[]=a&tag[]=b&filter[post]=1&filter[author][]=12&0=y".
     */
    private const array FORM_DATA = [
        'filter' => [
            'author' => ['12'],
            'post' => '1',
        ],
        'q' => 'x',
        'tag' => ['a', 'b'],
        0 => 'y',
    ];

    public function testParseBodyDataAcceptsFlatData(): void
    {
        $serverDataParser = new ServerDataParser();

        self::assertSame(['q' => 'x'], $serverDataParser->parseBodyData(['q' => 'x']));
    }

    public function testParseBodyDataAcceptsMultidimensionalData(): void
    {
        $serverDataParser = new ServerDataParser();

        self::assertSame(self::FORM_DATA, $serverDataParser->parseBodyData(self::FORM_DATA));
    }

    public function testParseBodyDataReturnsObjectUnchanged(): void
    {
        $serverDataParser = new ServerDataParser();
        $data = new stdClass();

        self::assertSame($data, $serverDataParser->parseBodyData($data));
    }

    public function testParseBodyDataAcceptsNull(): void
    {
        $serverDataParser = new ServerDataParser();

        self::assertNull($serverDataParser->parseBodyData(null));
    }

    public function testParseBodyDataRejectsNonStringValue(): void
    {
        $serverDataParser = new ServerDataParser();

        $this->expectException(InvalidArgumentException::class);

        $serverDataParser->parseBodyData(['tag' => ['a', 1]]);
    }

    public function testParseBodyDataRejectsScalar(): void
    {
        $serverDataParser = new ServerDataParser();

        $this->expectException(InvalidArgumentException::class);

        $serverDataParser->parseBodyData('q=x');
    }

    public function testParseCookieQueryParamsAcceptsMultidimensionalData(): void
    {
        $serverDataParser = new ServerDataParser();

        self::assertSame(self::FORM_DATA, $serverDataParser->parseCookieQueryParams(self::FORM_DATA));
    }
}
