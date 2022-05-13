<?php

namespace Give\Framework\Database\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;
use Throwable;

/**
 * Class DatabaseQueryException
 *
 * An exception for when errors occurred within the database while performing a query, which stores the SQL errors the
 * database returned
 *
 * @unreleased Use the GiveWP exception class
 * @since 2.9.2
 */
class DatabaseQueryException extends Exception
{
    /**
     * @var string[]
     */
    private $queryErrors;

    public function __construct(string $message, array $queryErrors, $code = 0, Throwable $previous = null)
    {
        $this->queryErrors = $queryErrors;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the query errors
     *
     * @since 2.9.2
     *
     * @return string[]
     */
    public function getQueryErrors(): array
    {
        return $this->queryErrors;
    }

    /**
     * @inheritDoc
     */
    public function getLogContext(): array
    {
        return [
            'category' => 'Uncaught database exception',
            'Query Errors' => $this->queryErrors,
        ];
    }
}
