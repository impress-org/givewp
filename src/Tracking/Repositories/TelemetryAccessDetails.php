<?php

namespace Give\Tracking\Repositories;

/**
 * Class TelemetryAccessDetails
 * @package Give\Tracking\Repositories
 *
 * @since 2.10.0
 */
class TelemetryAccessDetails {
	const ACCESS_TOKEN_OPTION_KEY = 'give_telemetry_server_access_token';

	/**
	 * Get option value for telemetry access token.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	public function getAccessTokenOptionValue() {
		return get_option( self::ACCESS_TOKEN_OPTION_KEY, '' );
	}

	/**
	 * Return whether or not website has telemetry access token.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	public function hasAccessTokenOptionValue() {
		return (bool) $this->getAccessTokenOptionValue();
	}

	/**
	 * Get option value for telemetry access token.
	 *
	 * @since 2.10.0
	 *
	 * @param string $optionValue
	 *
	 * @return string
	 */
	public function saveAccessTokenOptionValue( $optionValue ) {
		return update_option( self::ACCESS_TOKEN_OPTION_KEY, $optionValue, false );
	}
}
