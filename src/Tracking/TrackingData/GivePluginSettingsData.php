<?php
namespace Give\Tracking\TrackingData;

use Give\Tracking\Contracts\TrackData;
use Give\Tracking\AdminSettings;
use Give\Tracking\Repositories\Settings;

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
			'is_anonymous_tracking' => Settings::USAGE_TRACKING_OPTION_KEY,
		];

		$data     = [];
		$settings = get_option( 'give_settings', give_get_default_settings() );
		foreach ( $generalSettings as $setting ) {
			$data[ $setting ] = isset( $settings[ $setting ] ) ? $settings[ $setting ] : '';
		}

		foreach ( $trueFalseSettings as $key => $setting ) {
			$value        = isset( $settings[ $setting ] ) ? $settings[ $setting ] : 'disabled';
			$data[ $key ] = absint( give_is_setting_enabled( $value ) );
		}

		$data['active_payment_gateways'] = $this->getGatewaysLabels();

		return $data;
	}

	/**
	 * Return active gateways labels.
	 *
	 * @since 2.10.2
	 * @return array
	 */
	private function getGatewaysLabels() {
		$gateways = give_get_enabled_payment_gateways();
		$labels   = [];

		foreach ( $gateways as $id => $data ) {
			$labels[ $id ]['admin_label']    = $data['admin_label'];
			$labels[ $id ]['checkout_label'] = $data['checkout_label'];
		}

		return $labels;
	}
}
