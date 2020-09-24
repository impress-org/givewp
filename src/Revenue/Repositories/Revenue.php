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
	 * Revenue constructor
	 */
	public function constructor() {
	}

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

		$this->validateDataFormInsertion( $revenueData );

		/**
		 * Filter data name for revenue table.
		 *
		 * @since 2.9.0
		 */
		$revenueData = apply_filters(
			'give_revenue_data_for_insertion',
			$revenueData
		);

		return $wpdb->insert(
			$wpdb->give_revenue,
			$revenueData,
			$this->getDataFormatType( $revenueData )
		);
	}

	/**
	 * Validate revenue data for insertion.
	 *
	 * @since 2.9.0
	 *
	 * @param array $array
	 */
	private function validateDataFormInsertion( $array ) {
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
	}

	/**
	 * Get data format type for data.
	 *
	 * We are storing only string '%s' and integer '%d' in revenue table. this function will auto generate format array for INSERT.
	 *
	 * @param $data
	 *
	 * @return string[] Array of value format type
	 */
	private function getDataFormatType( $data ) {
		array_walk(
			$data,
			static function( $value ) {
				return is_numeric( $value ) ? '%d' : '%s';
			}
		);

		return $data;
	}
}
