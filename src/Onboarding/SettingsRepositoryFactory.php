<?php

namespace Give\Onboarding;

/**
 * @since 2.8.0
 */
class SettingsRepositoryFactory {

	/**
	 * @since 2.8.0
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

		try {
			$settingsRepository = new SettingsRepository( $option, $persistCallback );
		} catch ( \TypeError $e ) {
			trigger_error( $e->getMessage(), E_USER_ERROR );
		}

		return $settingsRepository;
	}
}
