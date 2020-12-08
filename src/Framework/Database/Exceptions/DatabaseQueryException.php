<?php

namespace Give\Framework\Database\Exceptions;

use Exception;

/**
 * Class DatabaseQueryException
 *
 * An exception for when errors occurred within the database while performing a query, which stores the SQL errors the
 * database returned
 *
 * @since 2.9.2
 */
class DatabaseQueryException extends Exception {
	/**
	 * @var string[]
	 */
	private $queryErrors;

	/**
	 * Creates a new instance wih the query errors
	 *
	 * @since 2.9.2
	 *
	 * @param string|string[] $queryErrors
	 * @param string|null     $message
	 *
	 * @return DatabaseQueryException
	 */
	public static function create( $queryErrors, $message = null ) {
		$error = new self();

		$error->message     = $message ?: 'Query failed in database';
		$error->queryErrors = (array) $queryErrors;

		return $error;
	}

	/**
	 * Returns the query errors
	 *
	 * @since 2.9.2
	 *
	 * @return string[]
	 */
	public function getQueryErrors() {
		return $this->queryErrors;
	}

	/**
	 * Returns a human readable form of the exception for logging
	 *
	 * @since 2.9.2
	 *
	 * @return string
	 */
	public function getLogOutput() {
		$queryErrors = array_map(
			function ( $error ) {
				return " - {$error}";
			},
			$this->queryErrors
		);

		return "
			Code: {$this->getCode()}\n
			Message: {$this->getMessage()}\n
			DB Errors: \n
			{$queryErrors}
		";
	}
}
