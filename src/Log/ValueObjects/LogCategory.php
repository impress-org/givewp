<?php

namespace Give\Log\ValueObjects;

/**
 * Class LogCategory
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
class LogCategory extends ValueObject {
	const CORE      = 'Core';
	const PAYMENT   = 'Payment';
	const MIGRATION = 'Migration';

	/**
	 * @inheritDoc
	 */
	public static function getDefault() {
		return LogCategory::CORE;
	}
}
