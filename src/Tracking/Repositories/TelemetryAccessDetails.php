<?php

namespace Give\Tracking\Repositories;

/**
 * Class TelemetryAccessDetails
 * @package Give\Tracking\Repositories
 *
 * @since 2.10.0
 */
class TelemetryAccessDetails {
	/**
	 * Get telemetry server url.
	 *
	 * @since 2.10.0
	 *
	 * @return string
	 */
	public function getServerUrl() {
		return 'https://givetelemetryserver.test/api/v1/track-plugin-usage';
	}

	/**
	 * Get option key for telemetry access token.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	public function getAccessTokenOptionKey() {
		return 'give_telemetry_server_access_token';
	}

	/**
	 * Get option value for telemetry access token.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	public function getAccessTokenOptionValue() {
		return get_option( $this->getAccessTokenOptionKey(), '' );
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
		return update_option( $this->getAccessTokenOptionKey(), $optionValue );
	}
}
