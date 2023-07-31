<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Request\Server;

use OutOfBoundsException;
use Psr\Http\Message\ServerRequestInterface;
use WebServCo\Data\DataTransferObject\KeyValue\StringString;
use WebServCo\Http\Contract\Message\Request\Server\ServerHeadersAcceptProcessorInterface;

use function array_key_exists;
use function count;
use function explode;
use function krsort;
use function sprintf;
use function str_replace;
use function strpos;
use function strtolower;

/**
 * Helper for working with SERVER "Accept" header.
 */
final class ServerHeadersAcceptProcessor implements ServerHeadersAcceptProcessorInterface
{
    public function getAcceptHeaderValue(ServerRequestInterface $request): string
    {
        $array = $request->getHeader('Accept');
        if (!array_key_exists(0, $array)) {
            throw new OutOfBoundsException('Accept header array is empty.');
        }

        return $array[0];
    }

    /**
     * Process accept list based on the accept header value.
     *
     * @return array<string,string>
     */
    public function processAcceptList(string $acceptHeaderValue): array
    {
        $result = [];
        $acceptHeaderValue = strtolower(str_replace(' ', '', $acceptHeaderValue));
        $parts = explode(',', $acceptHeaderValue);
        $index = count($parts);
        foreach ($parts as $item) {
            $data = $this->processAcceptItem($item);
            if ($data->key === '0') {
                // q=0 actually means that that mime type is not supported.
                continue;
            }
            $result[sprintf('%s.%d', $data->key, $index)] = $data->value;
            $index -= 1;
        }

        krsort($result);

        return $result;
    }

    private function processAcceptItem(string $item): StringString
    {
        // The default quality is 1.
        $quality = '1';
        // Check if there is a different quality.
        if (strpos($item, ';q=') !== false) {
            // Divide "mime/type;q=X" into two parts: "mime/type" and "X".
            $itemParts = explode(';q=', $item);
            if (!array_key_exists(0, $itemParts)) {
                throw new OutOfBoundsException('Key not found in array');
            }
            $item = $itemParts[0];
            if (!array_key_exists(1, $itemParts)) {
                throw new OutOfBoundsException('Key not found in array');
            }
            $quality = $itemParts[1];
            // q=0 actually means that that mime type is not supported (handled elsewhere).
        }

        return new StringString($quality, $item);
    }
}
