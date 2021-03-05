<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\TrackRegisterer;
use Give\Tracking\TrackingData\DonationFormsData;
use Give\Tracking\Enum\EventType;

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
	protected $dataClassName = DonationFormsData::class;

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @since 2.10.0
	 *
	 * @param  TrackRegisterer  $track
	 *
	 */
	public function __construct( TrackRegisterer $track ) {
		$this->eventType = new EventType( EventType::DONATION_FORM_UPDATED );
		parent::__construct( $track );
	}
}
