<?php

namespace Give\Framework\Migrations\Exceptions;

use Exception;
use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Traits\Loggable;

/**
 * Class DatabaseMigrationException
 *
 * Represents an exception that occurred when executing a migration within the database
 */
class DatabaseMigrationException extends Exception implements LoggableException {
	use Loggable;
}
