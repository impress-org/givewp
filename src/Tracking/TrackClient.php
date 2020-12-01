<?php
namespace Give\Tracking;

use Give\Tracking\Events\TrackTracking;
use Give\Tracking\TrackingData\ServerData;
use Give\Tracking\TrackingData\WebsiteData;

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
	 * @param string $trackId
	 * @param array $trackData
	 *
	 * @since 2.10.0
	 */
	public function send( $trackId, $trackData ) {
		$url = add_query_arg(
			[
				'en' => $trackId,
				'ts' => time(),
			],
			self::SERVER_URL
		);

		/* @var ServerData $serverData */
		$serverData = give( ServerData::class );
		/* @var  WebsiteData $websiteData */
		$websiteData = give( WebsiteData::class );

		$trackData['server']  = $serverData->get();
		$trackData['website'] = $websiteData->get();

		/**
		 * Filter tracked data.
		 *
		 * @since 2.10.0
		 */
		$trackData = apply_filters( "give_tracked_data_{$trackId}", $trackData );

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
