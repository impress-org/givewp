<?php
namespace Give\Tracking\TrackingData;

use Braintree\Exception;
use Give\Framework\Collection;
use Give\Helpers\ArrayDataSet;
use Give\Tracking\AdminSettings;
use Give\ValueObjects\Money;
use Give_Donors_Query;
use WP_Query;

/**
 * Class GiveDonationPluginData
 *
 * Represents Give plugin settings data.
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
				'installDate'              => $this->getPluginInstallDate(),
				'donationFormCount'        => $this->getDonationFormCount(),
				'firstDonationDate'        => $this->getFirstDonationDate(),
				'lastDonationDate'         => $this->getLastDonationDate(),
				'donorCount'               => $this->getDonorCount(),
				'avgDonationAmountByDonor' => $this->getAvgDonationAmountByDonor(),
				'revenue'                  => $this->getRevenueTillNow(),
				'globalSettings'           => $this->getGlobalSettings(),
				'userType'                 => give_get_option( 'user_type' ),
				'causeType'                => give_get_option( 'cause_type' ),
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
		$confirmationPageID = give_get_option( 'success_page' );

		return strtotime( get_post_field( 'post_date', $confirmationPageID, 'db' ) );
	}

	/**
	 * Returns donation form count
	 *
	 * @since 2.10.0
	 * @return int
	 */
	private function getDonationFormCount() {
		$formQuery = new WP_Query(
			[
				'post_type' => 'give_forms',
				'status'    => 'publish',
				'fields'    => 'ids',
				'number'    => -1,
			]
		);

		return $formQuery->found_posts;
	}

	/**
	 * Get first donation date.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	private function getFirstDonationDate() {
		global $wpdb;

		$date = $wpdb->get_var(
			"
				SELECT post_date_gmt
				FROM {$wpdb->posts}
				WHERE post_status IN ({$this->getDonationStatuses()})
				ORDER BY post_date_gmt DESC
				LIMIT 1
				"
		);

		return $date ? strtotime( $date ) : 'NULL';
	}

	/**
	 * Get last donation date.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	private function getLastDonationDate() {
		global $wpdb;

		$date = $wpdb->get_var(
			"
				SELECT post_date_gmt
				FROM {$wpdb->posts}
				WHERE post_status IN ({$this->getDonationStatuses()})
				ORDER BY post_date_gmt ASC
				LIMIT 1
				"
		);

		return $date ? strtotime( $date ) : 'NULL';
	}

	/**
	 * Returns donor count which donated greater then zero
	 *
	 * @since 2.10.0
	 * @return string
	 */
	private function getDonorCount() {
		$donorQuery = new Give_Donors_Query(
			[
				'number'          => -1,
				'count'           => true,
				'donation_amount' => 0,
			]
		);

		return $donorQuery->get_donors();
	}

	/**
	 * Get average donation by donor.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	private function getAvgDonationAmountByDonor() {
		try {
			$amount   = $this->getRevenueTillNow() / $this->getDonorCount();
			$currency = give_get_option( 'currency' );
			$amount   = round( $amount, give_get_price_decimals( $currency ) );
		} catch ( Exception $e ) {
			$amount = 'NULL';
		}

		return $amount;
	}

	/**
	 * Returns revenue till current date.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	private function getRevenueTillNow() {
		global $wpdb;

		$currency = give_get_option( 'currency' );
		$result   = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT SUM(amount)
				FROM {$wpdb->give_revenue} as r
				INNER JOIN {$wpdb->posts} as p
				ON r.donation_id=p.id
				WHERE p.post_date<=%s
				AND post_status IN ({$this->getDonationStatuses()})
				",
				current_time( 'mysql' )
			)
		);
		return $result ? Money::ofMinor( $result, $currency )->getAmount() : '';
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

	/**
	 * Get donation statuses.
	 *
	 * @since 2.10.0
	 *
	 * @return string
	 */
	private function getDonationStatuses() {
		$statuses = implode(
			'\',\'',
			[
				'publish', // One time donation
				'give_subscription', // Renewal
			]
		);

		return "'{$statuses}'";
	}
}
