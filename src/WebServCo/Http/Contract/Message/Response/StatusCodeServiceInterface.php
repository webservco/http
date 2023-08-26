<?php

declare(strict_types=1);

namespace WebServCo\Http\Contract\Message\Response;

use Fig\Http\Message\StatusCodeInterface;

/**
 * @phpcs:disable SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder
 */
interface StatusCodeServiceInterface extends StatusCodeInterface
{
    public const STATUS_CODES = [
        // Informational 1xx
        // 100
        self::STATUS_CONTINUE => 'Continue',
        // 101
        self::STATUS_SWITCHING_PROTOCOLS => 'Switching Protocols',
        // 102
        self::STATUS_PROCESSING => 'Processing',
        // 103,
        self::STATUS_EARLY_HINTS => 'Early Hints',
        // Successful 2xx
        // 200
        self::STATUS_OK => 'OK',
        // 201
        self::STATUS_CREATED => 'Created',
        // 202
        self::STATUS_ACCEPTED => 'Accepted',
        // 203
        self::STATUS_NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        // 204
        self::STATUS_NO_CONTENT => 'No Content',
        // 205
        self::STATUS_RESET_CONTENT => 'Reset Content',
        // 206
        self::STATUS_PARTIAL_CONTENT => 'Partial Content',
        // 207
        self::STATUS_MULTI_STATUS => 'Multi-Status',
        // 208
        self::STATUS_ALREADY_REPORTED => 'Already Reported',
        // 226,
        self::STATUS_IM_USED => 'IM Used',
        // Redirection 3xx
        // 300
        self::STATUS_MULTIPLE_CHOICES => 'Multiple Choices',
        // 301
        self::STATUS_MOVED_PERMANENTLY => 'Moved Permanently',
        // 302
        self::STATUS_FOUND => 'Moved Temporarily',
        // 303
        self::STATUS_SEE_OTHER => 'See Other',
        // 304
        self::STATUS_NOT_MODIFIED => 'Not Modified',
        // 305
        self::STATUS_USE_PROXY => 'Use Proxy',
        //306 (`self::STATUS_RESERVED`) is no longer valid
        // 307
        self::STATUS_TEMPORARY_REDIRECT => 'Temporary Redirect',
        // 308,
        self::STATUS_PERMANENT_REDIRECT => 'Permanent Redirect',
        // Client Errors 4xx
        // 400
        self::STATUS_BAD_REQUEST => 'Bad Request',
        // 401
        self::STATUS_UNAUTHORIZED => 'Unauthorized',
        // 402
        self::STATUS_PAYMENT_REQUIRED => 'Payment Required',
        // 403
        self::STATUS_FORBIDDEN => 'Forbidden',
        // 404
        self::STATUS_NOT_FOUND => 'Not Found',
        // 405
        self::STATUS_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        // 406
        self::STATUS_NOT_ACCEPTABLE => 'Not Acceptable',
        // 407
        self::STATUS_PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        // 408
        self::STATUS_REQUEST_TIMEOUT => 'Request Time-out',
        // 409
        self::STATUS_CONFLICT => 'Conflict',
        // 410
        self::STATUS_GONE => 'Gone',
        // 411
        self::STATUS_LENGTH_REQUIRED => 'Length Required',
        // 412
        self::STATUS_PRECONDITION_FAILED => 'Precondition Failed',
        // 413
        self::STATUS_PAYLOAD_TOO_LARGE => 'Request Entity Too Large',
        // 414
        self::STATUS_URI_TOO_LONG => 'Request-URI Too Large',
        // 415
        self::STATUS_UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        // 416
        self::STATUS_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        // 417
        self::STATUS_EXPECTATION_FAILED => 'Expectation Failed',
        // 418
        self::STATUS_IM_A_TEAPOT => 'I\'m a teapot',
        // 421
        self::STATUS_MISDIRECTED_REQUEST => 'Misdirected Request',
        // 422
        self::STATUS_UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        // 423
        self::STATUS_LOCKED => 'Locked',
        // 424
        self::STATUS_FAILED_DEPENDENCY => 'Failed Dependency',
        // 425
        self::STATUS_TOO_EARLY => 'Too Early',
        // 426
        self::STATUS_UPGRADE_REQUIRED => 'Upgrade Required',
        // 428
        self::STATUS_PRECONDITION_REQUIRED => 'Precondition Required',
        // 429
        self::STATUS_TOO_MANY_REQUESTS => 'Too Many Requests',
        // 431
        self::STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        // 451,
        self::STATUS_UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
        // Server Errors 5xx
        // 500
        self::STATUS_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        // 501
        self::STATUS_NOT_IMPLEMENTED => 'Not Implemented',
        // 502
        self::STATUS_BAD_GATEWAY => 'Bad Gateway',
        // 503
        self::STATUS_SERVICE_UNAVAILABLE => 'Service Unavailable',
        // 504
        self::STATUS_GATEWAY_TIMEOUT => 'Gateway Time-out',
        // 505
        self::STATUS_VERSION_NOT_SUPPORTED => 'HTTP Version not supported',
        // 506
        self::STATUS_VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
        // 507
        self::STATUS_INSUFFICIENT_STORAGE => 'Insufficient Storage',
        // 508
        self::STATUS_LOOP_DETECTED => 'Loop Detected',
        // 510
        self::STATUS_NOT_EXTENDED => 'Not Extended',
        // 511,
        self::STATUS_NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
    ];

    public const REDIRECT_STATUS_CODES = [
        // 302
        self::STATUS_FOUND => 'Moved Temporarily',
        // 301
        self::STATUS_MOVED_PERMANENTLY => 'Moved Permanently',
        // 303
        self::STATUS_SEE_OTHER => 'See Other',
    ];
    // @phpcs:enable

    public function getReasonPhraseByStatusCode(int $code): string;

    public function validateStatusCode(int $code): bool;
}
