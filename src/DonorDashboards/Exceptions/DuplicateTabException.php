<?php

namespace Give\DonorDashboards\Exceptions;

use Exception;
use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Traits\Loggable;

/**
 * @since 2.10.0
 */
class DuplicateTabException extends Exception implements LoggableException {
	use Loggable;

	/**
	 * DuplicateTabException constructor.
	 *
	 * @since 2.10.0
	 *
	 * @param int            $code
	 * @param Exception|null $previous
	 */
	public function __construct( $code = 0, Exception $previous = null ) {
		parent::__construct(
			__( 'A tab can only be added once. Make sure there are not id conflicts.', 'give' ),
			$code,
			$previous
		);
	}

	/**
	 * Allows the exception to be cast to a string format
	 *
	 * @since 2.10.0
	 *
	 * @return string
	 */
	public function __toString() {
		return __CLASS__ . ": [$this->code]: $this->message\n";
	}
}
