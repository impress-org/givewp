<?php
namespace Give\Tracking\TrackingData;

/**
 * Class EditedDonationFormsData
 *
 * @package Give\Tracking\TrackingData
 * @since 2.10.2
 */
class EditedDonationFormsData extends DonationFormsData {
	/**
	 * set donation form ids.
	 *
	 * @since 2.10.2
	 * @return EditedDonationFormsData|void
	 */
	protected function setFormIds() {
		$this->formIds = $this->trackEvents->getRecentlyEditedDonationFormsList();

		return $this;
	}
}
