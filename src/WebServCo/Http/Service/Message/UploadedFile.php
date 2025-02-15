<?php

declare(strict_types=1);

namespace WebServCo\Http\Service\Message;

use InvalidArgumentException;
use OutOfBoundsException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use UnexpectedValueException;

use function array_key_exists;
use function assert;
use function dirname;
use function fclose;
use function fopen;
use function fwrite;
use function is_string;
use function is_uploaded_file;
use function is_writable;
use function move_uploaded_file;
use function rename;
use function unlink;

use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_EXTENSION;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_OK;
use const UPLOAD_ERR_PARTIAL;

final class UploadedFile implements UploadedFileInterface
{
    // @phpcs:ignore SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder
    private const ERROR_MESSAGES = [
        // 0
        UPLOAD_ERR_OK => 'There is no error, the file uploaded with success.',
        // 1
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        // 2
        UPLOAD_ERR_FORM_SIZE =>
        'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        // 3
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        // 4
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        // 6
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        // 7
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        // 8
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
    ];

    private bool $isMoved;

    public function __construct(
        private StreamInterface $stream,
        private int $size,
        private int $errorCode,
        private ?string $clientFilename = null,
        private ?string $clientMediaType = null,
    ) {
        if (!array_key_exists($errorCode, self::ERROR_MESSAGES)) {
            throw new InvalidArgumentException('Invalid error code specified.');
        }

        $this->isMoved = false;
    }

    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    public function getError(): int
    {
        return $this->errorCode;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getStream(): StreamInterface
    {
        if ($this->errorCode !== UPLOAD_ERR_OK) {
            throw new UnexpectedValueException(self::ERROR_MESSAGES[$this->errorCode]);
        }

        if (!$this->isMoved) {
            throw new OutOfBoundsException('The file is already moved.');
        }

        return $this->stream;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * @param string $targetPath
     */
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function moveTo($targetPath): void
    {
        $this->validateTargetPath($targetPath);

        // Get stream from self; already handles errorCode and isMoved
        $stream = $this->getStream();

        // https://stackoverflow.com/questions/56672106/how-to-detect-mime-type-from-psr-7-uploadedfileinterface
        $uploadedFilePath = $stream->getMetadata('uri');
        assert(is_string($uploadedFilePath) || $uploadedFilePath === null);

        $result = is_string($uploadedFilePath)
            ? $this->writeFile($uploadedFilePath, $targetPath)
            : $this->writeStream($stream, $targetPath);

        if ($result === false) {
            throw new OutOfBoundsException('Could not move uploaded file to target.');
        }

        $this->isMoved = true;
    }

    private function moveUploadedFile(string $uploadedFilePath, string $targetPath): bool
    {
        $result = move_uploaded_file($uploadedFilePath, $targetPath);
        unlink($uploadedFilePath);

        return $result;
    }

    private function validateTargetPath(string $targetPath): bool
    {
        $targetDirectory = dirname($targetPath);
        if (!is_writable($targetDirectory)) {
            throw new OutOfBoundsException('Target directory is not writable.');
        }

        return true;
    }

    private function writeFile(string $uploadedFilePath, string $targetPath): bool
    {
        return is_uploaded_file($uploadedFilePath)
            ? $this->moveUploadedFile($uploadedFilePath, $targetPath)
            : rename($uploadedFilePath, $targetPath);
    }

    private function writeStream(StreamInterface $stream, string $targetPath): bool
    {
        $resource = fopen($targetPath, 'w');

        if ($resource === false) {
            throw new OutOfBoundsException('Error binding resource to stream.');
        }

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        while (!$stream->eof()) {
            fwrite($resource, $stream->read(4_096));
        }

        fclose($resource);

        $stream->close();

        return true;
    }
}
