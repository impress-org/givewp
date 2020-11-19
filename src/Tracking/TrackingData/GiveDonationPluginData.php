<?php
namespace Give\Tracking\TrackingData;

use Give\Framework\Collection;
use Give\Helpers\ArrayDataSet;
use Give\Tracking\AdminSettings;

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
		// @todo add organization type
		return [
			'givewp' => [
				'installDate'       => $this->getPluginInstallDate(),
				'donationFormCount' => $this->getDonationFormCount(),
				'donorCount'        => $this->getDonorCount(),
				'revenue'           => $this->getRevenueTillNow(),
				'settings'          => $this->getGlobalSettings(),
				'userType'          => give_get_option( 'user_type' ),
				'causeType'         => give_get_option( 'cause_type' ),
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
	 * Returns donor count
	 *
	 * @since 2.10.0
	 * @return int
	 */
	private function getDonorCount() {
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

	/**
	 * Returns plugin global settings.
	 *
	 * @since 2.10.0
	 * @return array
	 */
	private function getGlobalSettings() {
		$settings = [
			'currency',
			'base_country',
			'base_state',
			'currency',
			'name_title_prefix',
			'company_field',
			'anonymous_donation',
			'donor_comment',
			AdminSettings::USAGE_TRACKING_OPTION_NAME,
		];

		$data = [];
		foreach ( $settings as $setting ) {
			$data[ $setting ] = give_get_option( $setting, '' );
		}

		$data                          = ArrayDataSet::camelCaseKeys( $data );
		$data['activePaymentGateways'] = give_get_enabled_payment_gateways();

		return $data;
	}
}
