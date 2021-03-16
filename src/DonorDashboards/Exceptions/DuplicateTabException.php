<?php

namespace Give\DonorDashboards\Exceptions;

use Exception;

/**
 * @since 2.10.0
 */
class DuplicateTabException extends Exception {

	// Redefine the exception so message is pre-defined
	public function __construct( $message = 'A tab can only be added once. Make sure there are not id conflicts.', $code = 0, Exception $previous = null ) {
		// some code

		// make sure everything is assigned properly
		parent::__construct( $message, $code, $previous );
	}

	// custom string representation of object
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}
