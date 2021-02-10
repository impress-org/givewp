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
		return $this->getGlobalSettings();
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
			'is_name_title'         => 'name_title_prefix',
			'is_company'            => 'company_field',
			'is_anonymous_donation' => 'anonymous_donation',
			'is_donor_comment'      => 'donor_comment',
			'is_anonymous_tracking' => AdminSettings::USAGE_TRACKING_OPTION_NAME,
		];

		$data = [];

		foreach ( $generalSettings as $setting ) {
			$data[ $setting ] = give_get_option( $setting, '' );
		}

		foreach ( $trueFalseSettings as $key => $setting ) {
			$data[ $key ] = absint( give_is_setting_enabled( give_get_option( $setting, 'disabled' ) ) );
		}

		$data['active_payment_gateways'] = give_get_enabled_payment_gateways();

		return $data;
	}
}
