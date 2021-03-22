<?php

namespace Give\Tracking;

use Give\Tracking\Enum\EventType;
use Give\Tracking\Repositories\TelemetryAccessDetails;
use Give\Tracking\TrackingData\WebsiteInfoData;

/**
 * Class AccessTokenGenerator
 * @package Give\Tracking
 *
 * @since 2.10.0
 */
class AccessToken {
	/**
	 * @var TrackClient
	 */
	private $trackClient;

	/**
	 * @var TelemetryAccessDetails
	 */
	private $telemetryAccessDetails;

	/**
	 * AccessToken constructor.
	 *
	 * @param  TrackClient  $trackClient
	 * @param  TelemetryAccessDetails  $telemetryAccessDetails
	 */
	public function __construct( TrackClient $trackClient, TelemetryAccessDetails $telemetryAccessDetails ) {
		$this->trackClient            = $trackClient;
		$this->telemetryAccessDetails = $telemetryAccessDetails;
	}

	/**
	 * Store access token
	 *
	 * @since 2.10.0
	 */
	public function store() {
		/* @var WebsiteInfoData $dataClass */
		$dataClass = give( WebsiteInfoData::class );

		$response = $this->trackClient->post( new EventType( EventType::CREATE_TOKEN ), $dataClass, [ 'blocking' => true ] );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $response['success'] ) ) {
			return false;
		}

		$token = $response['data']['access_token'];
		$this->telemetryAccessDetails->saveAccessTokenOptionValue( $token );

		return true;
	}
}
