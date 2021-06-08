<?php

namespace Give\Framework\Exceptions;

use Error;
use Exception;
use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Traits\Loggable;
use Give\Log\Log;

class UncaughtExceptionLogger {
	/**
	 * @var callable|null
	 */
	private $previousHandler;

	/**
	 * Registers the class with the set_exception_handler to receive uncaught exceptions
	 *
	 * @since 2.11.1
	 */
	public function setupExceptionHandler() {
		if ( $this->previousHandler !== null ) {
			return;
		}

		$this->previousHandler = @set_exception_handler( [ $this, 'handleException' ] );
	}

	/**
	 * Handles an uncaught exception by checking if the Exception is native to GiveWP and then logging it if it is
	 *
	 * @since 2.11.2 remove parameter typing as it may be an Error
	 * @since 2.11.1
	 *
	 * @param Exception|Error $exception
	 */
	public function handleException( $exception ) {
		if ( $exception instanceof LoggableException ) {
			/** @var LoggableException|Loggable $exception */
			Log::error( $exception->getLogMessage(), $exception->getLogContext() );
		}

		if ( $this->previousHandler !== null ) {
			$previousHandler = $this->previousHandler;
			$previousHandler( $exception );
		}
	}
}
