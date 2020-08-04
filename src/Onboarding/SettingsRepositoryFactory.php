<?php

namespace Give\Onboarding;

class SettingsRepositoryFactory {

	public static function make( $optionName ) {

		$option = get_option( $optionName );

		$persistCallback = function( $settings ) use ( $optionName ) {
			return update_option( $optionName, $settings );
		};

		return new SettingsRepository( $option, $persistCallback );
	}
}
