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
 * @since 2.21.0 Use the GiveWP exception class
 * @since 2.9.2
 */
class DatabaseQueryException extends Exception
{
    /**
     * @var string[]
     */
    private $queryErrors;

    /**
     * @var string
     */
    private $query;

    /**
     * @since 2.21.0 include query and query errors, and make auto-logging compatible
     * @since 2.9.2
     */
    public function __construct(
        string $query,
        array $queryErrors,
        string $message = 'Database Query',
        $code = 0,
        Throwable $previous = null
    ) {
        $this->query = $query;
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

    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getLogContext(): array
    {
        return [
            'category' => 'Uncaught database exception',
            'Query' => $this->query,
            'Query Errors' => $this->queryErrors,
        ];
    }
}
