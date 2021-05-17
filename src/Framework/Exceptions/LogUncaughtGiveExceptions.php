<?php

namespace Give\Framework\Exceptions;

use Exception;
use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Log\Log;

class LogUncaughtGiveExceptions {
	/**
	 * @var callable|null
	 */
	private $previousHandler;

	/**
	 * Registers the class with the set_exception_handler to receive uncaught exceptions
	 *
	 * @unreleased
	 */
	public function setupExceptionHandler() {
		if ( $this->previousHandler !== null ) {
			return;
		}

		$this->previousHandler = @set_exception_handler( [ $this, 'handleException' ] );
	}

	public function handleException( Exception $exception ) {
		if ( $exception instanceof LoggableException ) {
			Log::error( $exception->getLogMessage(), $exception->getLogContext() );
		}

		if ( $this->previousHandler !== null ) {
			$previousHandler = $this->previousHandler;
			$previousHandler( $exception );
		}
	}
}
