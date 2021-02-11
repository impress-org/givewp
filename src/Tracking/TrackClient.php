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
	 *
	 * @return array|WP_Error
	 */
	public function send( $trackId, $trackData, $requestArgs = [] ) {
		if ( ! $trackId || ! $trackData ) {
			throw new InvalidArgumentException( 'Pass valid track id and tracked data to TrackClient' );
		}

		$default_request_args = [
			'headers'     => [
				'content-type:' => 'application/json',
				'Authorization' => 'Bearer ' . get_option( OptionName::TELEMETRY_ACCESS_TOKEN ),
			],
			'timeout'     => 8,
			'httpversion' => '1.1',
			'blocking'    => true,
			'sslverify'    => false,
			'user-agent'  => 'GIVE/' . GIVE_VERSION . ' ' . get_bloginfo( 'url' ),
			'body'        => wp_json_encode( $trackData ),
			'data_format' => 'body',
		];

		$tracking_request_args = wp_parse_args( $requestArgs, $default_request_args );

		$response =  wp_remote_post( $this->getApiUrl( $trackId ), $tracking_request_args );

		error_log( print_r( $tracking_request_args, true ) . "\n", 3, WP_CONTENT_DIR . '/debug_new.log' );
		error_log( print_r( $response, true ) . "\n", 3, WP_CONTENT_DIR . '/debug_new.log' );
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
