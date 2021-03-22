<?php

namespace Give\Tracking\Events;

use Give\Tracking\TrackingData\ActiveDonationFormsData;

/**
 * Class ActiveDonationFormsFirstTimeTracking
 * @package Give\Tracking\Events
 *
 * @since 2.10.0
 */
class ActiveDonationFormsFirstTimeTracking extends DonationFormsTracking {
	protected $dataClassName = ActiveDonationFormsData::class;
}
