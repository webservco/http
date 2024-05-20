<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message\Request\Server;

use InvalidArgumentException;
use WebServCo\Http\Contract\Message\Request\Server\ServerDataParserInterface;

use function assert;
use function is_array;
use function is_object;
use function is_scalar;
use function is_string;

final class ServerDataParser implements ServerDataParserInterface
{
    /**
     * Parse body data.
     *
     * @return array<string,string>|object|null
     */
    public function parseBodyData(mixed $data): array|object|null
    {
        if (is_array($data)) {
            return $this->parseBodyArrayData($data);
        }
        if (is_object($data)) {
            return $data;
        }
        if ($data === null) {
            return null;
        }

        throw new InvalidArgumentException('Invalid data provided.');
    }

    /**
     * Parse cookie/query parameters.
     *
     * Following abomination needed in order to be contravariant with PSR method definition.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $params
     * @return array<mixed>
     * @phpcs:enable
     */
    public function parseCookieQueryParams(array $params): array
    {
        $parsedParams = [];

        /**
         * Psalm error: "Unable to determine the type that $.. is being assigned to"
         * However this is indeed mixed, no solution but to suppress error.
         *
         * @psalm-suppress MixedAssignment
         */
        foreach ($params as $key => $value) {
            /**
             * No need to check key type, it can only be int or string:
             * https://www.php.net/manual/en/language.types.array.php
             * "The key can either be an int or a string. The value can be of any type."
             */

            /**
             * Value can be an array
             * Situation: query params can be a multi dimensional array.
             * Eg. "?filter[post]=1,2&filter[author]=12"
             */
            $parsedParams[$key] = is_array($value)
                ? $this->parseCookieQueryParams($value)
                : $this->extractStringDataFromParam($value);
        }

        return $parsedParams;
    }

    /**
     * Parse server params.
     *
     * Following abomination needed in order to be contravariant with PSR method definition.
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $serverParams
     * @phpcs:enable
     * @return array<string,array<int,string>|scalar|null>
     */
    public function parseServerParams(array $serverParams): array
    {
        $parsedParams = [];

        foreach ($serverParams as $key => $value) {
            assert(is_array($value) || is_scalar($value) || $value === null);

            if (!is_string($key)) {
                throw new InvalidArgumentException('Invalid key specified.');
            }

            $parsedParams[$key] = $this->parseServerValue($value);
        }

        return $parsedParams;
    }

    private function extractStringDataFromParam(mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Invalid value specified.');
        }

        return $value;
    }

    /**
     * Parse body data as array.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $data
     * @phpcs:enable
     * @return array<string,string>
     */
    private function parseBodyArrayData(array $data): array
    {
        $parsedData = [];
        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                throw new InvalidArgumentException('Invalid data provided.');
            }
            if (!is_string($value)) {
                throw new InvalidArgumentException('Invalid data provided.');
            }
            $parsedData[$key] = $value;
        }

        return $parsedData;
    }

    /**
     * Parse server value of type array.
     *
     * Currently only "args" server item is supported, hence the strict array<int,string> return.
     *
     * @return array<int,string>
     */
    private function parseServerValueArray(mixed $data): array
    {
        $result = [];

        // Using mixed type and checking for array in order to avoid static analysis errors.
        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid data type provided.');
        }

        foreach ($data as $item) {
            if (!is_string($item)) {
                throw new InvalidArgumentException('Invalid value type specified.');
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Parse server value.
     *
     * @return array<int,string>|bool|int|float|string
     */
    private function parseServerValue(mixed $data): array|bool|int|float|string
    {
        if (is_array($data)) {
            return $this->parseServerValueArray($data);
        }

        if (is_scalar($data)) {
            return $data;
        }

        /**
         * Note: if need to support also "null" value, be aware that phan will raise an unsolvable error
         * for method `parseServerParams` (null support needs to be implemented also there):
         *
         * PhanPartialTypeMismatchReturn Returning $parsedParams of type
         * array{}|non-empty-array<string,?array<int,string>>|non-empty-array<string,?bool>
         * |non-empty-array<string,?float>|non-empty-array<string,?int>|non-empty-array<string,?string>
         * but parseServerParams() is declared to return
         * array<string,array<int,string>>|array<string,bool>|array<string,float>|array<string,int>|array<string,null>
         * |array<string,string>
         * (non-empty-array<string,?array<int,string>> is incompatible)
         */

        throw new InvalidArgumentException('Invalid value type specified.');
    }
}
