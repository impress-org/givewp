<?php
namespace Give\Tracking\TrackingData;

use Give\Tracking\Contracts\TrackData;
use Give\Helpers\ArrayDataSet;
use Give\Tracking\AdminSettings;

/**
 * Class GivePluginSettingsData
 *
 * This class represents Give plugin data.
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class GivePluginSettingsData implements TrackData {

	/**
	 * Return Give plugin settings data.
	 *
	 * @since 2.10.0
	 * @return array
	 */
	public function get() {
		return [
			'installDate'    => $this->getPluginInstallDate(),
			'globalSettings' => $this->getGlobalSettings(),
		];
	}

	/**
	 * Returns plugin install date
	 *
	 * @since 2.10.0
	 * @return int
	 */
	private function getPluginInstallDate() {
		$confirmationPageID = give_get_option( 'success_page' );

		return strtotime( get_post_field( 'post_date', $confirmationPageID, 'db' ) );
	}

	/**
	 * Returns plugin global settings.
	 *
	 * @since 2.10.0
	 * @return array
	 */
	private function getGlobalSettings() {
		$generalSettings = [
			'currency',
			'base_country',
			'base_state',
			'currency',
			'user_type',
			'cause_type',
		];

		$trueFalseSettings = [
			'name_title_prefix',
			'company_field',
			'anonymous_donation',
			'donor_comment',
			AdminSettings::USAGE_TRACKING_OPTION_NAME,
		];

		$data = [];

		foreach ( $generalSettings as $setting ) {
			$data[ $setting ] = give_get_option( $setting, '' );
		}

		foreach ( $trueFalseSettings as $setting ) {
			$data[ $setting ] = absint( give_is_setting_enabled( give_get_option( $setting, 'disabled' ) ) );
		}

		$data                          = ArrayDataSet::camelCaseKeys( $data );
		$data['activePaymentGateways'] = give_get_enabled_payment_gateways();

		return $data;
	}
}
