<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\DonationMetricsData;
use Give\Tracking\TrackingData\ThemeData;

/**
 * Class DonationMetrics
 * @package Give\Tracking\Events
 *
 * @since 2.10.0
 */
class DonationMetrics extends TrackEvent {

	/**
	 * @var string
	 */
	protected $trackId = 'donation-metrics';

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  Track  $track
	 * @param  DonationMetricsData  $themeData
	 *
	 * @since 2.10.0
	 */
	public function __construct( Track $track, DonationMetricsData $themeData ) {
		parent::__construct( $track, $themeData );
	}
}
