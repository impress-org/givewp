<?php

namespace Give\API\REST\V3\Routes\Subscriptions\Exceptions;

use Exception;

/**
 * @unreleased
 */
class SubscriptionValidationException extends Exception
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
} 