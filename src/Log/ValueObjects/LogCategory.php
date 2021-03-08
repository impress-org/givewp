<?php

namespace Give\Log\ValueObjects;

/**
 * Class LogCategory
 * @package Give\Log\ValueObjects
 *
 * @since 2.10.0
 *
 * @method static CORE()
 * @method static PAYMENT()
 * @method static MIGRATION()
 */
class LogCategory extends Enum {
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
