<?php
namespace Give\Tracking\TrackingData;

use Give\Framework\Collection;
use Give\Helpers\ArrayDataSet;
use Give\Tracking\AdminSettings;

/**
 * Class GiveDonationPluginData
 *
 * Represents Give plugin data.
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class GivePluginData implements Collection {
	/**
	 * @var DonorData
	 */
	private $donorData;

	/**
	 * @var DonationData
	 */
	private $donationData;

	/**
	 * @var DonationFormData
	 */
	private $donationFormData;

	/**
	 * GiveDonationPluginData constructor.
	 *
	 * @param  DonorData  $donorData
	 * @param  DonationFormData  $donationFormData
	 * @param  DonationData  $donationData
	 */
	public function __construct( DonorData $donorData, DonationFormData $donationFormData, DonationData $donationData ) {
		$this->donorData        = $donorData;
		$this->donationData     = $donationData;
		$this->donationFormData = $donationFormData;
	}

	/**
	 * Return Give plugin settings data.
	 *
	 * @since 2.10.0
	 * @return array
	 */
	public function get() {
		$data = [
			'installDate'    => $this->getPluginInstallDate(),
			'globalSettings' => $this->getGlobalSettings(),
		];

		$data = array_merge( $data, $this->donorData->get() );
		$data = array_merge( $data, $this->donationData->get() );
		$data = array_merge( $data, $this->donationFormData->get() );

		return [ 'givewp' => $data ];
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
