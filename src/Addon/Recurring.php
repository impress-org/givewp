<?php

namespace Give\Addon;

/**
 * Class Addon
 *
 * @package Give\Addon
 */
class Recurring implements Addonable {

	/**
	 * @inheritDoc
	 */
	public static function isActive() {
		return defined( 'GIVE_RECURRING_VERSION' );
	}
}
