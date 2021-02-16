<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\DonationMetricsData;
use Give\Tracking\ValueObjects\EventType;

/**
 * Class DonationMetricsTracking
 * @package Give\Tracking\Events
 *
 * @since 2.10.0
 */
class DonationMetricsTracking extends TrackEvent {

	/**
	 * @var string
	 */
	protected $trackId;

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  Track  $track
	 * @param  DonationMetricsData  $themeData
	 *
	 * @since 2.10.0
	 */
	public function __construct( Track $track, DonationMetricsData $themeData ) {
		$this->trackId = ( new EventType() )->getDonationMetrics();
		parent::__construct( $track, $themeData );
	}
}
