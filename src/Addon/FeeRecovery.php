<?php

namespace Give\Addon;

/**
 * Class Addon
 *
 * @package Give\Addon
 */
class FeeRecovery implements Addonable {

	/**
	 * @inheritDoc
	 */
	public static function isActive() {
		return defined( 'GIVE_FEE_RECOVERY_VERSION' );
	}
}
