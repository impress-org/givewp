<?php

/**
 * Class Give_Helper_Form.
 *
 * Helper class to create and delete a donation form.
 */
class Give_Helper_Form extends WP_UnitTestCase {

	/**
	 * Delete a donation form.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id ID of the donation form to delete.
	 */
	public static function delete_form( $form_id ) {

		// Delete the post
		wp_delete_post( $form_id, true );

	}

	/**
	 * Create a simple form.
	 *
	 * @since 1.0
	 */
	public static function create_simple_form() {

		$post_id = wp_insert_post( array(
			'post_title'  => 'Test Donation Form',
			'post_name'   => 'test-donation-form',
			'post_type'   => 'give_forms',
			'post_status' => 'publish'
		) );

		$meta = array(
			'_give_price_option'                => 'set',
			'_give_set_price'                   => '20.00',
			'_give_custom_amount'               => 'enabled',
			'_give_custom_amount_minimum'       => '1',
			'_give_custom_amount_text'          => 'Would you like to set a custom amount?',
			'_give_goal_option'                 => 'disabled',
			'_give_payment_display'             => 'onpage',
			'_give_show_register_form'          => 'none',
			'_give_customize_offline_donations' => 'disabled',
			'_give_terms_option'                => 'disabled',
			'_give_form_earnings'               => '40.00',
			'_give_form_sales'                  => '2',
			'_give_default_gateway'             => 'global'
		);
		foreach ( $meta as $key => $value ) {
			give_update_meta( $post_id, $key, $value );
		}

		return get_post( $post_id );

	}

	/**
	 * Create a multi-level donation form.
	 *
	 * @since 1.0
	 */
	public static function create_multilevel_form() {

		$post_id = wp_insert_post( array(
			'post_title'  => 'Multi-level Test Donation Form',
			'post_name'   => 'multilevel-test-donation-form',
			'post_type'   => 'give_forms',
			'post_status' => 'publish'
		) );

		$_multi_level_donations = array(
			array(
				'_give_id'     => array( 'level_id' => '1' ),
				'_give_amount' => '10',
				'_give_text'   => 'Small Gift'
			),
			array(
				'_give_id'      => array( 'level_id' => '2' ),
				'_give_amount'  => '25',
				'_give_text'    => 'Mid-size Gift',
				'_give_default' => 'default'
			),
			array(
				'_give_id'     => array( 'level_id' => '3' ),
				'_give_amount' => '50',
				'_give_text'   => 'Large Gift'
			),
			array(
				'_give_id'     => array( 'level_id' => '4' ),
				'_give_amount' => '100',
				'_give_text'   => 'Big Gift'
			),
		);

		$meta = array(
			'_give_set_price'          => '0.00', //Multi-level Pricing; not set
			'_give_price_option'       => 'multi',
			'_give_display_style'      => 'buttons',
			'_give_donation_levels'    => array_values( $_multi_level_donations ),
			'_give_form_earnings'      => 120,
			'_give_form_sales'         => 6,
		);


		foreach ( $meta as $key => $value ) {
			give_update_meta( $post_id, $key, $value );
		}

		return new Give_Donate_Form( $post_id );

	}
	
}
