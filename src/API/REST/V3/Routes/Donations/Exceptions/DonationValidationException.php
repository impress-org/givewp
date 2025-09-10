<?php

namespace Give\API\REST\V3\Routes\Donations\Exceptions;

use Exception;

/**
 * @since 4.8.0
 */
class DonationValidationException extends Exception
{
    /**
     * @var string
     */
    private $errorCode;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @since 4.8.0
     *
     * @param string $message
     * @param string $errorCode
     * @param int $statusCode
     * @param Exception|null $previous
     */
    public function __construct(string $message, string $errorCode, int $statusCode = 400, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }

    /**
     * Get the error code
     *
     * @since 4.8.0
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get the HTTP status code
     *
     * @since 4.8.0
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
