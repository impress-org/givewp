<?php

namespace Give\Onboarding;

/**
 * @since 2.8.0
 */
class SettingsRepositoryFactory {

	/**
	 * @since 2.8.0
	 * @unreleased Casts the option value as an array.
	 *
	 * @param string $optionName
	 *
	 * @return SettingsRepository
	 */
	public function make( $optionName ) {

		$option = get_option( $optionName, [] );

		/**
		 * @param array $settings
		 *
		 * @return bool True if the value was updated, false otherwise.
		 */
		$persistCallback = function( $settings ) use ( $optionName ) {
			return update_option( $optionName, $settings );
		};

		return new SettingsRepository( (array) $option, $persistCallback );
	}
}
