<?php

/**
 * Class Give_Helper_Form.
 *
 * Helper class to create and delete a donation form.
 */
class Give_Helper_Form extends Give_Unit_Test_Case {

	/**
	 * Delete a donation form.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id ID of the donation form to delete.
	 *
	 * @return array|false|WP_Post
	 */
	public static function delete_form( $form_id ) {
		return wp_delete_post( $form_id, true );
	}

	/**
	 * Create a simple form.
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return Give_Donate_Form
	 */
	public static function create_simple_form( $args = array() ) {
		// Set form args.
		$form_args = wp_parse_args(
			( ! empty( $args['form'] ) ? $args['form'] : array() ),
			array(
				'post_title'  => 'Test Donation Form',
				'post_name'   => 'test-donation-form',
				'post_type'   => 'give_forms',
				'post_status' => 'publish',
			)
		);

		// Set form meta data.
		$meta = wp_parse_args(
			( ! empty( $args['meta'] ) ? $args['meta'] : array() ),
			array(
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
				'_give_default_gateway'             => 'global',
			)
		);

		return self::create_form(
			array(
				'form' => $form_args,
				'meta' => $meta,
			)
		);

	}

	/**
	 * Create a multi-level form.
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return Give_Donate_Form
	 */
	public static function create_multilevel_form( $args = array() ) {
		// Set form arguments.
		$form_args = wp_parse_args(
			( ! empty( $args['form'] ) ? $args['form'] : array() ),
			array(
				'post_title'  => 'Multi-level Test Donation Form',
				'post_name'   => 'multilevel-test-donation-form',
				'post_type'   => 'give_forms',
				'post_status' => 'publish',
			)
		);

		// Levels.
		$_multi_level_donations = array(
			array(
				'_give_id'     => array( 'level_id' => '1' ),
				'_give_amount' => '10',
				'_give_text'   => 'Small Gift',
			),
			array(
				'_give_id'      => array( 'level_id' => '2' ),
				'_give_amount'  => '25',
				'_give_text'    => 'Mid-size Gift',
				'_give_default' => 'default',
			),
			array(
				'_give_id'     => array( 'level_id' => '3' ),
				'_give_amount' => '50',
				'_give_text'   => 'Large Gift',
			),
			array(
				'_give_id'     => array( 'level_id' => '4' ),
				'_give_amount' => '100',
				'_give_text'   => 'Big Gift',
			),
		);

		// Set meta data
		$meta = wp_parse_args(
			( ! empty( $args['meta'] ) ? $args['meta'] : array() ),
			array(
				'_give_set_price'       => '0.00', //Multi-level Pricing; not set
				'_give_price_option'    => 'multi',
				'_give_display_style'   => 'buttons',
				'_give_donation_levels' => array_values( $_multi_level_donations ),
				'_give_form_earnings'   => 120,
				'_give_form_sales'      => 6,
			)
		);

		return self::create_form(
			array(
				'form' => $form_args,
				'meta' => $meta,
			)
		);

	}


	/**
	 * Create a form.
	 *
	 * @since 2.0
	 *
	 * @param array $args
	 *
	 * @return Give_Donate_Form
	 */
	public static function create_form( $args ) {
		$form_id   = 0;
		$form_args = ! empty( $args['form'] ) ? $args['form'] : array();
		$meta      = ! empty( $args['meta'] ) ? $args['meta'] : array();

		try {
			// Insert form.
			if ( ! empty( $form_args ) ) {
				$form_id = wp_insert_post( $form_args );
			} else {
				throw new Exception( __( 'Empty form argument is not valid to setup donation form.', 'give' ) );
			}

			if ( ! is_wp_error( $form_id ) && ! empty( $meta ) ) {
				foreach ( $meta as $key => $value ) {
					give_update_meta( $form_id, $key, $value );
				}
			}
		} catch ( Exception $e ) {
			echo "\n{$e->getMessage()}";
		}

		return new Give_Donate_Form( $form_id );
	}
}

// @todo: Add default form setting to created_form function.
