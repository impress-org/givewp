<?php

namespace Give\Framework\Exceptions;

use Exception;
use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Traits\Loggable;
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

	/**
	 * Handles an uncaught exception by checking if the Exception is native to GiveWP and then logging it if it is
	 *
	 * @unreleased
	 *
	 * @param Exception $exception
	 */
	public function handleException( Exception $exception ) {
		if ( $this->isGiveException( $exception ) ) {
			/** @var LoggableException|Loggable $exception */
			Log::error( $exception->getLogMessage(), $exception->getLogContext() );
		}

		if ( $this->previousHandler !== null ) {
			$previousHandler = $this->previousHandler;
			$previousHandler( $exception );
		}
	}

	/**
	 * Checks to see if the given Exception is native to GiveWP
	 *
	 * @param Exception $exception
	 *
	 * @return bool
	 */
	private function isGiveException( Exception $exception ) {
		if ( $exception instanceof LoggableException ) {
			return true;
		}

		$traits = class_uses( $exception );
		if ( isset( $traits[ Loggable::class ] ) ) {
			return true;
		}

		return false;
	}
}
