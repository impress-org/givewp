<?php
namespace Give\Revenue\Repositories;

use InvalidArgumentException;

/**
 * Class Revenue
 * @package Give\Revenue\Repositories
 *
 * Use this class to get data from "give_revenue" table.
 *
 * @since 2.9.0
 */
class Revenue {
	/**
	 * Insert revenue.
	 *
	 * @since 2.9.0
	 *
	 * @param  array  $revenueData
	 *
	 * @return bool|int
	 */
	public function insert( $revenueData ) {
		global $wpdb;

		// Validate revenue data
		$this->validateNewRevenueData( $revenueData );

		return $wpdb->insert(
			$wpdb->give_revenue,
			$revenueData,
			$this->getPlaceholderForPrepareQuery( $revenueData )
		);
	}

	/**
	 * Validate new revenue data.
	 *
	 * @since 2.9.0
	 *
	 * @param array $array
	 */
	protected function validateNewRevenueData( $array ) {
		$required = [ 'donation_id', 'form_id', 'amount' ];

		$array = array_filter( $array ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException(
				sprintf(
					'To insert revenue, please provide valid %1$s.',
					implode( ', ', $required )
				)
			);
		}

		foreach ( $required as $columnName ) {
			if ( empty( $array[ $columnName ] ) ) {
				throw new InvalidArgumentException( 'Empty value is not allowed to create revenue.' );
			}
		}
	}

	/**
	 * Get placeholder for prepare query.
	 *
	 * @param array $data
	 *
	 * @return string[] Array of value format type
	 */
	private function getPlaceholderForPrepareQuery( $data ) {
		$format = [];

		foreach ( $data as $value ) {
			$format[] = is_numeric( $value ) ? '%d' : '%s';
		}

		return $format;
	}

	/**
	 * Return whether or not donation id exist in give_revenue table.
	 *
	 * @sicne 2.9.0
	 *
	 * @param int $donationId
	 *
	 * @return bool
	 */
	public function isDonationExist( $donationId ) {
		global $wpdb;

		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT donation_id
				FROM {$wpdb->give_revenue}
				WHERE donation_id = %d
				",
				$donationId
			)
		);
	}
}
