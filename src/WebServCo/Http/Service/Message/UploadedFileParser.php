<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use WebServCo\Http\Contract\Message\UploadedFileParserInterface;

use function array_key_exists;
use function assert;
use function count;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

final class UploadedFileParser implements UploadedFileParserInterface
{
    public function __construct(
        private StreamFactoryInterface $streamFactory,
        private UploadedFileFactoryInterface $uploadedFileFactory,
    ) {
    }

    /**
     * Process uploaded files that should already be in PSR format.
     *
     * $uploadedFiles should be "An array tree of UploadedFileInterface instances."
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param mixed[] $uploadedFiles
     * @phpcs:enable
     * @return array<string,array<int,\Psr\Http\Message\UploadedFileInterface>>
     */
    public function parsePsrUploadedFiles(array $uploadedFiles): array
    {
        $result = [];
        foreach ($uploadedFiles as $inputName => $data) {
            assert(is_array($data));
            $inputName = $this->parseInputName($inputName);
            $data = $this->parseFileData($data);
            foreach ($data as $file) {
                $result[$inputName][] = $file;
            }
        }

        return $result;
    }

    /**
     * Process uploaded files from superglobal to PSR ("An array tree of UploadedFileInterface instances.") format.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param array<int|string,mixed> $uploadedFiles
     * @phpcs:enable
     * @return array<string,array<int,\Psr\Http\Message\UploadedFileInterface>>
     */
    public function parseSuperglobalUploadedFiles(array $uploadedFiles): array
    {
        $result = [];

        foreach ($uploadedFiles as $inputName => $data) {
            assert(is_array($data));
            $inputName = $this->parseInputName($inputName);
            $data = $this->parseData($data);
            $data = $this->parseDataKeys($data);

            $totalMultipleFiles = $this->countIndividualFiles($data, 'name');
            for ($i = 0; $i < $totalMultipleFiles; $i += 1) {
                $result[$inputName][$i] = $this->uploadedFileFactory->createUploadedFile(
                    $this->createStream($this->parseStringValue($data, 'tmp_name', $i)),
                    $this->parseIntValue($data, 'size', $i),
                    $this->parseIntValue($data, 'error', $i),
                    $this->parseNullableStringValue($data, 'name', $i),
                    $this->parseNullableStringValue($data, 'type', $i),
                );
            }
        }

        return $result;
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param array<string,array<int|string,mixed>> $data
     * @phpcs:enable
     */
    private function countIndividualFiles(array $data, string $key = 'name'): int
    {
        if (!array_key_exists($key, $data)) {
            throw new InvalidArgumentException(sprintf('Key "%s" is missing.', $key));
        }

        return count($data[$key]);
    }

    private function createStream(string $filename): StreamInterface
    {
        return $this->streamFactory->createStreamFromFile($filename, 'r');
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @return array<mixed>
     * @phpcs:enable
     */
    private function parseData(mixed $data): array
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data is not an array.');
        }

        return $data;
    }

    /**
     * Return one processed $_FILES item as array.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param array<mixed> $data
     * @return array<string,array<int|string,mixed>>
     * @phpcs:enable
     */
    private function parseDataKeys(array $data): array
    {
        $result = [];

        foreach (['name', 'full_path', 'type', 'tmp_name', 'error', 'size'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException(sprintf('Key "%s" is missing.', $key));
            }
            $result[$key] = is_array($data[$key])
                ? $data[$key]
                // Data is not an array, normalize to array for consistency.
                : [$data[$key]];
        }

        return $result;
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param array<mixed> $data
     * @phpcs:enable
     * @return array<int,\Psr\Http\Message\UploadedFileInterface>
     */
    private function parseFileData(array $data): array
    {
        $data = $this->parseData($data);
        $parsedData = [];
        foreach ($data as $file) {
            if (!($file instanceof UploadedFileInterface)) {
                throw new InvalidArgumentException('Invalid uploaded file.');
            }
            $parsedData[] = $file;
        }

        return $parsedData;
    }

    private function parseInputName(int|string $inputName): string
    {
        if (!is_string($inputName)) {
            throw new InvalidArgumentException('Input name is not a string.');
        }

        return $inputName;
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param array<string,array<int|string,mixed>> $data
     * @phpcs:enable
     */
    private function parseIntValue(array $data, string $key, int $index): int
    {
        $value = $this->parseValue($data, $key, $index);

        if (!is_int($value)) {
            throw new InvalidArgumentException('Invalid value.');
        }

        return $value;
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param array<string,array<int|string,mixed>> $data
     * @phpcs:enable
     */
    private function parseNullableStringValue(array $data, string $key, int $index): ?string
    {
        $value = $this->parseValue($data, $key, $index);

        if ($value === null) {
            return null;
        }

        return $this->parseStringValue($data, $key, $index);
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param array<string,array<int|string,mixed>> $data
     * @phpcs:enable
     */
    private function parseStringValue(array $data, string $key, int $index): string
    {
        $value = $this->parseValue($data, $key, $index);

        if (!is_string($value)) {
            throw new InvalidArgumentException('Invalid value.');
        }

        return $value;
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax.DisallowedArrayTypeHintSyntax
     * @param array<string,array<int|string,mixed>> $data
     * @phpcs:enable
     */
    private function parseValue(array $data, string $key, int $index): int|string|null
    {
        if (!array_key_exists($key, $data)) {
            throw new InvalidArgumentException('Key not found in array.');
        }

        if (!array_key_exists($index, $data[$key])) {
            throw new InvalidArgumentException('Index not found in array.');
        }

        $value = $data[$key][$index];

        if (!is_int($value) && !is_string($value) && $value !== null) {
            throw new InvalidArgumentException('Invalid value.');
        }

        return $value;
    }
}
