<?php

namespace Give\Framework\Exceptions\Traits;

trait Loggable {
	/**
	 * Gets the Exception::getMessage() method
	 *
	 * @unreleased
	 *
	 * @return string
	 */
	abstract public function getMessage();

	/**
	 * Returns the human-readable log message
	 *
	 * @unreleased
	 *
	 * @return string
	 */
	public function getLogMessage() {
		return $this->getMessage();
	}

	/**
	 * Returns an array with the basic context details
	 *
	 * @unreleased
	 *
	 * @return array
	 */
	public function getLogContext() {
		return [
			'category'  => 'Uncaught Exception',
			'exception' => $this,
		];
	}
}
