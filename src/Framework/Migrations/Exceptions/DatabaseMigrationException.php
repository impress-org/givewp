<?php

namespace Give\Framework\Migrations\Exceptions;

use Exception;
use Give\Framework\Database\Exceptions\DatabaseQueryException;

/**
 * Class DatabaseMigrationException
 *
 * Represents an exception that occurred when executing a migration within the database
 */
class DatabaseMigrationException extends Exception {
	public static function fromException( DatabaseQueryException $exception, $message ) {
		return new Exception( $message . PHP_EOL . print_r( $exception, true ) );
	}
}
