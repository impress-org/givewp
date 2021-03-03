<?php
namespace Give\Tracking\TrackingData;

/**
 * Class EditedDonationFormsData
 *
 * @package Give\Tracking\TrackingData
 * @unreleased
 */
class EditedDonationFormsData extends DonationFormsData {
	/**
	 * set donation form ids.
	 *
	 * @unreleased
	 * @return EditedDonationFormsData|void
	 */
	protected function setFormIds() {
		$this->formIds = $this->trackEvents->getRecentlyEditedDonationFormsList();

		return $this;
	}
}
