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
	protected function setFormIdsByDonationIds() {
		$this->formIds = $this->trackEvents->getRecentlyEditedDonationFormsList();

		return $this;
	}
}
