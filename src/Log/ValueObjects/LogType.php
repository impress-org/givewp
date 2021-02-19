<?php

namespace Give\Log\ValueObjects;

/**
 * Class LogType
 * @package Give\Log\ValueObjects
 *
 * @since 2.9.7
 *
 * @method static ERROR()
 * @method static WARNING()
 * @method static NOTICE()
 * @method static SUCCESS()
 * @method static INFO()
 * @method static HTTP()
 * @method static SPAM()
 */
class LogType extends Enum {
	const ERROR   = 'error';
	const WARNING = 'warning';
	const NOTICE  = 'notice';
	const SUCCESS = 'success';
	const INFO    = 'info';
	const HTTP    = 'http';
	const SPAM    = 'spam';

	/**
	 * @inheritDoc
	 */
	public static function getDefault() {
		return LogType::ERROR;
	}
}
