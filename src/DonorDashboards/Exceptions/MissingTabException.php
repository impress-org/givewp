<?php

namespace Give\DonorDashboards\Exceptions;

use Exception;

/**
 * @since 2.10.0
 */
class MissingTabException extends Exception {

	// Redefine the exception so tab ID isn't optional
	public function __construct( $tabId, $code = 0, Exception $previous = null ) {

		$message = "No tab exists with the ID {$tabId}";

		// make sure everything is assigned properly
		parent::__construct( $message, $code, $previous );
	}

	// custom string representation of object
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}
