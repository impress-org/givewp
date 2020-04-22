<?php

namespace Give\Addon;

use function Give\Helpers\Form\Template\Utils\Frontend\getFormId;

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

	/**
	 * Return whether or not form accept fee.
	 *
	 * @since 2.7.0
	 * @return bool
	 */
	public static function canFormRecoverFee() {
		// Get the value of fee recovery enable or not.
		$optionValue = give_get_meta( getFormId(), '_form_give_fee_recovery', true );
		$optionValue = ! empty( $optionValue ) ? $optionValue : 'global';

		return give_is_setting_enabled( $optionValue ) ||
			   ( give_is_setting_enabled( $optionValue, 'global' ) &&
				 give_is_setting_enabled( give_get_option( 'give_fee_recovery' ) )
			   );
	}
}
