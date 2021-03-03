<?php
namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Enum\EventType;
use Give\Tracking\TrackingData\EditedDonationFormsData;
use Give\Tracking\TrackRegisterer;

/**
 * Class EditedDonationFormsTracking
 *
 * @package Give\Tracking\Events
 * @unreleased
 */
class EditedDonationFormsTracking extends TrackEvent {
	/**
	 * @var string
	 */
	protected $dataClassName = EditedDonationFormsData::class;

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
