<?php

/**
 * Class Give_Helper_Payment.
 *
 * Helper class to create and delete a payment easily.
 */
class Give_Helper_Payment extends WP_UnitTestCase {

	/**
	 * Delete a payment.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id ID of the payment to delete.
	 */
	public static function delete_payment( $payment_id ) {

		// Delete the payment
		give_delete_purchase( $payment_id );

	}

	/**
	 * Create a simple donation payment.
	 *
	 * @since 1.0
	 */
	public static function create_simple_payment() {

		global $give_options;

		// Enable a few options
		$give_options['enable_sequential'] = '1';
		$give_options['sequential_prefix'] = 'GIVE-';
		update_option( 'give_settings', $give_options );

		$simple_form     = Give_Helper_Form::create_simple_form();
		$multilevel_form = Give_Helper_Form::create_multilevel_form();

		/** Generate some donations */
		$user      = get_userdata( 1 );
		$user_info = array(
			'id'         => $user->ID,
			'email'      => $user->user_email,
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name
		);

		$total               = 0;
		$simple_price        = get_post_meta( $simple_form->ID, 'give_price', true );
		$variable_prices     = get_post_meta( $multilevel_form->ID, 'give_variable_prices', true );
		$variable_item_price = $variable_prices[1]['amount']; // == $100

		$total += $variable_item_price + $simple_price;

		//		$cart_details = array(
		//			array(
		//				'name'        => 'Test Download',
		//				'id'          => $simple_form->ID,
		//				'item_number' => array(
		//					'id'      => $simple_form->ID,
		//					'options' => array(
		//						'price_id' => 1
		//					)
		//				),
		//				'price'       => $simple_price,
		//				'item_price'  => $simple_price,
		//				'tax'         => 0,
		//				'quantity'    => 1
		//			),
		//			array(
		//				'name'        => 'Variable Test Download',
		//				'id'          => $multilevel_form->ID,
		//				'item_number' => array(
		//					'id'      => $multilevel_form->ID,
		//					'options' => array(
		//						'price_id' => 1
		//					)
		//				),
		//				'price'       => $variable_item_price,
		//				'item_price'  => $variable_item_price,
		//				'tax'         => 0,
		//				'quantity'    => 1
		//			),
		//		);

		$purchase_data = array(
			'price'           => number_format( (float) $total, 2 ),
			'give_form_title' => 'Test Donation',
			'give_form_id'    => $simple_form->ID,
			'date'            => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key'    => strtolower( md5( uniqid() ) ),
			'user_email'      => $user_info['email'],
			'user_info'       => $user_info,
			'currency'        => 'USD',
			'status'          => 'pending'
		);

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME'] = 'give_virtual';

		$payment_id = give_insert_payment( $purchase_data );
		$key        = $purchase_data['purchase_key'];

		$transaction_id = 'FIR3SID3';
		give_set_payment_transaction_id( $payment_id, $transaction_id );
		give_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'give' ), $transaction_id ) );

		return $payment_id;

	}

}
