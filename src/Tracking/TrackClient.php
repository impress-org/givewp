<?php
namespace Give\Tracking;

use Give\Tracking\TrackingData\ServerData;
use Give\Tracking\TrackingData\WebsiteData;
use InvalidArgumentException;

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
	const SERVER_URL = 'https://stats.givewp.com';

	/**
	 * Send a track event.
	 *
	 * @since 2.10.0
	 *
	 * @param string $trackId
	 * @param array $trackData
	 *
	 * @throws InvalidArgumentException
	 */
	public function send( $trackId, $trackData ) {
		if ( ! $trackId || ! $trackData ) {
			throw new InvalidArgumentException( 'Pass valid track id and tracked data to TrackClient' );
		}

		$url = add_query_arg(
			[
				'en' => $trackId,
				'ts' => time(),
			],
			self::SERVER_URL
		);

		$trackData['server']  = ( new ServerData() )->get();
		$trackData['website'] = ( new WebsiteData() )->get();

		// Set a 'content-type' header of 'application/json'.
		$tracking_request_args = [
			'headers'     => [ 'content-type:' => 'application/json' ],
			'timeout'     => 8,
			'httpversion' => '1.1',
			'blocking'    => false,
			'user-agent'  => 'GIVE/' . GIVE_VERSION . ' ' . get_bloginfo( 'url' ),
			'body'        => wp_json_encode( $trackData ),
			'data_format' => 'body',
		];

		wp_remote_post( $url, $tracking_request_args );
	}
}
