<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\DonationMetricsData;
use Give\Tracking\Enum\EventType;

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
	 * @var string
	 */
	protected $dataClassName = DonationMetricsData::class;

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  Track  $track
	 *
	 * @since 2.10.0
	 */
	public function __construct( Track $track ) {
		$this->trackId = new EventType( 'donation-metrics' );
		parent::__construct( $track );
	}
}
