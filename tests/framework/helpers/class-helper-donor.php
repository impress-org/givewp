<?php

/**
 * Class Give_Helper_Donor.
 *
 * Helper class to create a donor.
 */
class Give_Helper_Donor extends Give_Unit_Test_Case {

	/**
	 * Create a donor.
	 *
	 * @since 2.0
	 *
	 * @param array $donor_args
	 *
	 * @return int|Give_Donor
	 */
	public static function create_simple_payment( $donor_args = array() ) {

		$args = array(
			'name'  => 'Admin User',
			'email' => 'testadmin@domain.com',
		);

		$donor_args = wp_parse_args( $donor_args, $args );

		$donor = new Give_Donor();
		return $donor->create( $donor_args );
	}
}