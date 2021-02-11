<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\DonationFormsData;

/**
 * Class DonationFormsTracking
 * @package Give\Tracking\Events
 *
 * @since 2.10.0
 */
class DonationFormsTracking extends TrackEvent {
	/**
	 * @var string
	 */
	protected $trackId = 'donation-form-updated';

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  Track  $track
	 * @param  DonationFormsData  $themeData
	 *
	 * @since 2.10.0
	 */
	public function __construct( Track $track, DonationFormsData $themeData ) {
		parent::__construct( $track, $themeData );
	}

}
