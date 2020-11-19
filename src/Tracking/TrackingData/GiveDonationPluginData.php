<?php
namespace Give\Tracking\TrackingData;

use Give\Framework\Collection;

/**
 * Class GiveDonationPluginData
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class GiveDonationPluginData implements Collection {

	/**
	 * Return Give plugin settings data.
	 *
	 * @since 2.10.0
	 * @return array
	 */
	public function get() {
		return [
			'givewp' => [
				'installDate'       => $this->getPluginInstallDate(),
				'donationFormCount' => $this->getDonationFormCount(),
				'revenue'           => $this->getRevenueTillNow(),
			],
		];
	}

	/**
	 * Returns plugin install date
	 *
	 * @since 2.10.0
	 * @return int
	 */
	private function getPluginInstallDate() {
		return 0;
	}

	/**
	 * Returns donation form count
	 *
	 * @since 2.10.0
	 * @return int
	 */
	private function getDonationFormCount() {
		return 0;
	}

	/**
	 * Returns revenue till current date.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	private function getRevenueTillNow() {
		return '';
	}
}
