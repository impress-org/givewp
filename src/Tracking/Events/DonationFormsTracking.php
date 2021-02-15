<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\DonationFormsData;
use Give\Tracking\ValueObjects\EventType;

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
	protected $trackId;

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  Track  $track
	 * @param  DonationFormsData  $themeData
	 *
	 * @since 2.10.0
	 */
	public function __construct( Track $track, DonationFormsData $themeData ) {
		$this->trackId = ( new EventType() )->getDonationFormUpdated();
		parent::__construct( $track, $themeData );
	}

}
