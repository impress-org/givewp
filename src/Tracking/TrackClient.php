<?php
namespace Give\Tracking;

use Give\Tracking\ValueObjects\OptionName;
use InvalidArgumentException;
use WP_Error;

/**
 * Class TrackClient
 *
 * This class has responsibility to send tracking information
 *
 * @since 2.10.0
 * @package Give\Tracking
 *
 */
class TrackClient {
	/**
	 * Server URL.
	 *
	 * @var string
	 */
	const SERVER_URL = 'https://givetelemetryserver.test/api/v1/track-plugin-usage';

	/**
	 * Send a track event.
	 *
	 * @since 2.10.0
	 *
	 * @param  string  $trackId
	 * @param  array  $trackData
	 * @param  array  $requestArgs
	 *
	 * @return array|WP_Error
	 */
	public function post( $trackId, $trackData, $requestArgs = [] ) {
		if ( ! $trackId || ! $trackData ) {
			return new WP_Error( 'invalid-telemetry-request', 'Pass valid track id and tracked data to TrackClient' );
		}

		$default_request_args = [
			'headers'     => [
				'content-type:' => 'application/json',
				'Authorization' => 'Bearer ' . get_option( OptionName::TELEMETRY_ACCESS_TOKEN ),
			],
			'timeout'     => 8,
			'httpversion' => '1.1',
			'blocking'    => false,
			'user-agent'  => 'GIVE/' . GIVE_VERSION . ' ' . get_bloginfo( 'url' ),
			'body'        => wp_json_encode( $trackData ),
			'data_format' => 'body',
		];

		return wp_remote_post( $this->getApiUrl( $trackId ), wp_parse_args( $requestArgs, $default_request_args ) );
	}

	/**
	 * Get api url.
	 *
	 * @since 2.10.0
	 *
	 * @param string $trackId
	 *
	 * @return string
	 */
	public function getApiUrl( $trackId ) {
		return self::SERVER_URL . '/' . $trackId;
	}
}
