<?php

namespace Give\Log\ValueObjects;

/**
 * Class LogType
 * @package Give\Log\ValueObjects
 *
 * @since 2.9.7
 *
 * @method static error()
 * @method static warning()
 * @method static notice()
 * @method static success()
 * @method static info()
 * @method static http()
 */
class LogType extends ValueObject {
	const ERROR   = 'error';
	const WARNING = 'warning';
	const NOTICE  = 'notice';
	const SUCCESS = 'success';
	const INFO    = 'info';
	const HTTP    = 'http';

	/**
	 * @inheritDoc
	 */
	public static function getDefault() {
		return LogType::ERROR;
	}
}
