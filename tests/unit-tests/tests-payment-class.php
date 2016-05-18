<?php

/**
 * Class Tests_Payment_Class
 */
class Tests_Payment_Class extends WP_UnitTestCase {

	protected $_payment_id = null;
	protected $_key = null;
	protected $_post = null;
	protected $_payment_key = null;

	public function setUp() {

		parent::setUp();

		$payment_id         = Give_Helper_Payment::create_simple_payment();
		$purchase_data      = give_get_payment_meta( $payment_id );
		$this->_payment_key = give_get_payment_key( $payment_id );

		$this->_payment_id = $payment_id;
		$this->_key        = $this->_payment_key;

		$this->_transaction_id = 'FIR3SID3';
		give_set_payment_transaction_id( $payment_id, $this->_transaction_id );
		give_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'give' ), $this->_transaction_id ) );

		// Make sure we're working off a clean object caching in WP Core.
		// Prevents some payment_meta from not being present.
		clean_post_cache( $payment_id );
		update_postmeta_cache( array( $payment_id ) );
	}

	public function tearDown() {

		parent::tearDown();

		Give_Helper_Payment::delete_payment( $this->_payment_id );

	}

	public function test_IDs() {

		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEquals( $this->_payment_id, $payment->ID );
		$this->assertEquals( $payment->_ID, $payment->ID );
	}

	public function test_ID_save_block() {
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEquals( $this->_payment_id, $payment->ID );
		$payment->ID = 12121222;
		$payment->save();
		$this->assertEquals( $this->_payment_id, $payment->ID );
	}

	public function test_get_existing_payment() {
		$payment = new Give_Payment( $this->_payment_id );

		$this->assertEquals( $this->_payment_id, $payment->ID );
		$this->assertEquals( 120.00, $payment->total );
	}

	public function test_getting_no_payment() {
		$payment = new Give_Payment();
		$this->assertEquals( null, $payment->ID );

		$payment = new Give_Payment( 99999999999 );
		$this->assertEquals( null, $payment->ID );
	}

	public function test_payment_status_update() {

		$payment = new Give_Payment( $this->_payment_id );

		$payment->update_status( 'pending' );
		$this->assertEquals( 'pending', $payment->status );
		$this->assertEquals( 'Pending', $payment->status_nicename );

		// Test backwards compatibility
		give_update_payment_status( $this->_payment_id, 'publish' );

		// Need to get the payment again since it's been updated
		$payment = new Give_Payment( $this->_payment_id );

		$this->assertEquals( 'publish', $payment->status );
		$this->assertEquals( 'Complete', $payment->status_nicename );
	}

	public function test_add_donation() {
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEquals( 2, count( $payment->donations ) );
		$this->assertEquals( 120.00, $payment->total );

		$new_form = Give_Helper_Form::create_simple_form();

		$payment->add_donation( $new_form->ID );
		$payment->save();

		$this->assertEquals( 3, count( $payment->donations ) );
		$this->assertEquals( 140.00, $payment->total );
	}

	public function test_add_donation_zero_item_price() {

		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEquals( 2, count( $payment->donations ) );
		$this->assertEquals( 120.00, $payment->total );

		$new_form = Give_Helper_Form::create_simple_form();

		$args = array(
			'item_price' => 0,
		);

		$payment->add_donation( $new_form->ID, $args );
		$payment->save();

		$this->assertEquals( 3, count( $payment->donations ) );
		$this->assertEquals( 120.00, $payment->total );

	}

	public function test_add_donation_with_fee() {
		$payment = new Give_Payment( $this->_payment_id );
		$args    = array(
			'fees' => array(
				array(
					'amount' => 5,
					'label'  => 'Test Fee',
				),
			),
		);

		$new_form = Give_Helper_Form::create_simple_form();

		$payment->add_donation( $new_form->ID, $args );
		$payment->save();

		$this->assertFalse( empty( $payment->payment_details[2]['fees'] ) );
	}

	public function test_remove_donation() {
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEquals( 2, count( $payment->donations ) );
		$this->assertEquals( 120.00, $payment->total );

		$form_id = $payment->payment_details[0]['id'];
		$amount      = $payment->payment_details[0]['price'];
		$quantity    = $payment->payment_details[0]['quantity'];

		$remove_args = array( 'amount' => $amount, 'quantity' => $quantity );
		$payment->remove_donation( $form_id, $remove_args );
		$payment->save();

		$this->assertEquals( 1, count( $payment->donations ) );
		$this->assertEquals( 100.00, $payment->total );
	}

	public function test_remove_donation_by_index() {
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEquals( 2, count( $payment->donations ) );
		$this->assertEquals( 120.00, $payment->total );

		$form_id = $payment->payment_details[1]['id'];

		$remove_args = array( 'cart_index' => 1 );
		$payment->remove_donation( $form_id, $remove_args );
		$payment->save();

		$this->assertEquals( 1, count( $payment->donations ) );
		$this->assertEquals( 20.00, $payment->total );
	}
	
	public function test_payment_add_fee() {
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 1' ) );
		$this->assertEquals( 1, count( $payment->fees ) );
		$this->assertEquals( 125, $payment->total );
		$payment->save();

		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEquals( 5, $payment->fees_total );
		$this->assertEquals( 125, $payment->total );

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_give_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEquals( 1, count( $fees ) );
	}

	public function test_payment_remove_fee() {
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );

		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 1', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 2', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 3', 'type' => 'fee' ) );
		$payment->save();
		$this->assertEquals( 3, count( $payment->fees ) );
		$this->assertEquals( 'Test Fee 2', $payment->fees[1]['label'] );
		$this->assertEquals( 135, $payment->total );

		$payment->remove_fee( 1 );
		$payment->save();
		$this->assertEquals( 2, count( $payment->fees ) );
		$this->assertEquals( 130, $payment->total );
		$this->assertEquals( 'Test Fee 3', $payment->fees[1]['label'] );

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_give_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEquals( 2, count( $fees ) );
		$this->assertEquals( 'Test Fee 3', $fees[1]['label'] );
	}

	public function test_payment_remove_fee_by_label() {
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );

		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee', 'type' => 'fee' ) );
		$this->assertEquals( 1, count( $payment->fees ) );
		$this->assertEquals( 'Test Fee', $payment->fees[0]['label'] );
		$payment->save();

		$payment->remove_fee_by( 'label', 'Test Fee' );
		$this->assertEmpty( $payment->fees );
		$this->assertEquals( 120, $payment->total );
		$payment->save();

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_give_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEmpty( $fees );
	}

	public function test_payment_remove_fee_by_label_w_multi_no_global() {
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );

		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee', 'type' => 'fee' ) );
		$this->assertEquals( 2, count( $payment->fees ) );
		$this->assertEquals( 'Test Fee', $payment->fees[0]['label'] );
		$payment->save();

		$payment->remove_fee_by( 'label', 'Test Fee' );
		$this->assertEquals( 1, count( $payment->fees ) );
		$this->assertEquals( 125, $payment->total );
		$payment->save();

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_give_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEquals( 1, count( $fees ) );
	}

	public function test_payment_remove_fee_by_label_w_multi_w_global() {
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );

		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee', 'type' => 'fee' ) );
		$this->assertEquals( 2, count( $payment->fees ) );
		$this->assertEquals( 'Test Fee', $payment->fees[0]['label'] );
		$payment->save();

		$payment->remove_fee_by( 'label', 'Test Fee', true );
		$this->assertEmpty( $payment->fees );
		$this->assertEquals( 120, $payment->total );
		$payment->save();

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_give_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEmpty( $fees );
	}

	public function test_payment_remove_fee_by_index() {
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->fees );

		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 1', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 2', 'type' => 'fee' ) );
		$payment->add_fee( array( 'amount' => 5, 'label' => 'Test Fee 3', 'type' => 'fee' ) );
		$this->assertEquals( 3, count( $payment->fees ) );
		$this->assertEquals( 'Test Fee 2', $payment->fees[1]['label'] );
		$payment->save();

		$payment->remove_fee_by( 'index', 1, true );
		$this->assertEquals( 2, count( $payment->fees ) );
		$this->assertEquals( 130, $payment->total );
		$this->assertEquals( 'Test Fee 3', $payment->fees[1]['label'] );
		$payment->save();

		// Test that it saves to the DB
		$payment_meta = get_post_meta( $this->_payment_id, '_give_payment_meta', true );
		$this->assertArrayHasKey( 'fees', $payment_meta );
		$fees = $payment_meta['fees'];
		$this->assertEquals( 2, count( $fees ) );
		$this->assertEquals( 'Test Fee 3', $fees[1]['label'] );
	}

	public function test_user_info() {
		$payment = new Give_Payment( $this->_payment_id );

		$this->assertEquals( 'Admin', $payment->first_name );
		$this->assertEquals( 'User', $payment->last_name );
	}

	public function test_for_searlized_user_info() {
		$payment            = new Give_Payment( $this->_payment_id );
		$payment->user_info = serialize( array( 'first_name' => 'John', 'last_name' => 'Doe' ) );
		$payment->save();

		$this->assertInternalType( 'array', $payment->user_info );
		foreach ( $payment->user_info as $key => $value ) {
			$this->assertFalse( is_serialized( $value ), $key . ' returned a searlized value' );
		}
	}

	public function test_payment_with_initial_fee() {

		$this->markTestIncomplete( 'This test is incomplete until fees are integrated further into Give' );

		Give_Helper_Payment::delete_payment( $this->_payment_id );

		$payment_id = Give_Helper_Payment::create_simple_payment_with_fee();

		$payment = new Give_Payment( $payment_id );
		$this->assertFalse( empty( $payment->fees ) );
		$this->assertEquals( 47, $payment->total );

	}

	public function test_update_date_future() {
		$payment      = new Give_Payment( $this->_payment_id );
		$current_date = $payment->date;

		$new_date      = strtotime( $payment->date ) + DAY_IN_SECONDS;
		$payment->date = date( 'Y-m-d H:i:s', $new_date );
		$payment->save();

		$date2 = strtotime( $payment->date );
		$this->assertEquals( $new_date, $date2 );
	}

	public function test_update_date_past() {
		$payment      = new Give_Payment( $this->_payment_id );
		$current_date = $payment->date;

		$new_date      = strtotime( $payment->date ) - DAY_IN_SECONDS;
		$payment->date = date( 'Y-m-d H:i:s', $new_date );
		$payment->save();

		$date2 = strtotime( $payment->date );
		$this->assertEquals( $new_date, $date2 );
	}

	public function test_refund_payment() {
		$payment         = new Give_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$form     = new Give_Donate_Form( $payment->donations[0]['id'] );
		$earnings = $form->earnings;
		$sales    = $form->sales;

		$site_earnings = give_get_total_earnings();
		$site_sales    = give_get_total_sales();

		$payment->refund();

		wp_cache_flush();

		$status = get_post_status( $payment->ID );
		$this->assertEquals( 'refunded', $status );
		$this->assertEquals( 'refunded', $payment->status );

		$form2 = new Give_Donate_Form( $form->ID );

		$this->assertEquals( $earnings - $form2->price, $form2->earnings );
		$this->assertEquals( $sales - 1, $form2->sales );

		$this->assertEquals( $site_earnings - $payment->total, give_get_total_earnings() );
		$this->assertEquals( $site_sales - 1, give_get_total_sales() );
	}

	public function test_refund_payment_legacy() {

		$payment         = new Give_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$form     = new Give_Donate_Form( $payment->donations[0]['id'] );
		$earnings = $form->earnings;
		$sales    = $form->sales;

		give_undo_donation_on_refund( $payment->ID, 'refunded', 'publish' );

		wp_cache_flush();

		$payment = new Give_Payment( $this->_payment_id );
		$status  = get_post_status( $payment->ID );
		$this->assertEquals( 'refunded', $status );
		$this->assertEquals( 'refunded', $payment->status );

		$form2 = new Give_Donate_Form( $form->ID );

		$this->assertEquals( $earnings - $form->price, $form2->earnings );
		$this->assertEquals( $sales - 1, $form2->sales );

	}

	public function test_remove_with_multi_price_points_by_price_id() {

		Give_Helper_Payment::delete_payment( $this->_payment_id );

		$form    = Give_Helper_Form::create_multilevel_form();
		$payment = new Give_Payment();

		$payment->add_donation( $form->ID, array( 'price_id' => 1 ) );
		$payment->add_donation( $form->ID, array( 'price_id' => 2 ) );
		$payment->add_donation( $form->ID, array( 'price_id' => 3 ) );
		$payment->add_donation( $form->ID, array( 'price_id' => 4 ) );

		$this->assertEquals( 4, count( $payment->donations ) );
		$this->assertEquals( 185, $payment->total );

		$payment->status = 'complete';
		$payment->save();

		$payment->remove_donation( $form->ID, array( 'price_id' => 2 ) );
		$payment->save();

		$this->assertEquals( 3, count( $payment->donations ) );

		$this->assertEquals( 1, $payment->donations[0]['options']['price_id'] );
		$this->assertEquals( 1, $payment->payment_details[0]['options']['price_id'] );

		$this->assertEquals( 3, $payment->donations[1]['options']['price_id'] );
		$this->assertEquals( 3, $payment->payment_details[2]['options']['price_id'] );

		$this->assertEquals( 4, $payment->donations[2]['options']['price_id'] );
		$this->assertEquals( 4, $payment->payment_details[3]['options']['price_id'] );
	}

	public function test_remove_with_multi_price_points_by_payment_index() {

		Give_Helper_Payment::delete_payment( $this->_payment_id );

		$form = Give_Helper_Form::create_multilevel_form();
		$payment  = new Give_Payment();

		$payment->add_donation( $form->ID, array( 'price_id' => 1 ) );
		$payment->add_donation( $form->ID, array( 'price_id' => 2 ) );
		$payment->add_donation( $form->ID, array( 'price_id' => 3 ) );
		$payment->add_donation( $form->ID, array( 'price_id' => 4 ) );

		$this->assertEquals( 4, count( $payment->donations ) );
		$this->assertEquals( 185, $payment->total );

		$payment->status = 'complete';
		$payment->save();

		$payment->remove_donation( $form->ID, array( 'payment_index' => 1 ) );
		$payment->remove_donation( $form->ID, array( 'payment_index' => 2 ) );
		$payment->save();

		$this->assertEquals( 2, count( $payment->donations ) );

		$this->assertEquals( 1, $payment->donations[0]['options']['price_id'] );
		$this->assertEquals( 1, $payment->payment_details[0]['options']['price_id'] );

		$this->assertEquals( 4, $payment->donations[1]['options']['price_id'] );
		$this->assertEquals( 4, $payment->payment_details[3]['options']['price_id'] );

	}

	public function test_remove_with_multiple_same_price_by_price_id_different_prices() {
		Give_Helper_Payment::delete_payment( $this->_payment_id );

		$form = Give_Helper_Form::create_multilevel_form();
		$payment  = new Give_Payment();

		$payment->add_donation( $form->ID, array( 'price_id' => 0, 'item_price' => 10 ) );
		$payment->add_donation( $form->ID, array( 'price_id' => 0, 'item_price' => 20 ) );
		$payment->add_donation( $form->ID, array( 'price_id' => 0, 'item_price' => 30 ) );

		$this->assertEquals( 3, count( $payment->donations ) );
		$this->assertEquals( 60, $payment->total );

		$payment->status = 'complete';
		$payment->save();

		$payment->remove_donation( $form->ID, array( 'price_id' => 0, 'item_price' => 20 ) );
		$payment->save();

		$this->assertEquals( 2, count( $payment->donations ) );

		$this->assertEquals( 0, $payment->donations[0]['options']['price_id'] );
		$this->assertEquals( 0, $payment->payment_details[0]['options']['price_id'] );
		$this->assertEquals( 10, $payment->payment_details[0]['item_price'] );

		$this->assertEquals( 0, $payment->donations[1]['options']['price_id'] );
		$this->assertEquals( 0, $payment->payment_details[2]['options']['price_id'] );
		$this->assertEquals( 30, $payment->payment_details[2]['item_price'] );

	}

	public function test_remove_with_multiple_same_price_by_price_id_same_prices() {
		Give_Helper_Payment::delete_payment( $this->_payment_id );

		$form    = Give_Helper_Form::create_multilevel_form();
		$payment = new Give_Payment();

		$payment->add_donation( $form->ID, array( 'price_id' => 0, 'item_price' => 10 ) );
		$payment->add_donation( $form->ID, array( 'price_id' => 0, 'item_price' => 10 ) );
		$payment->add_donation( $form->ID, array( 'price_id' => 0, 'item_price' => 10 ) );

		$this->assertEquals( 3, count( $payment->donations ) );
		$this->assertEquals( 30, $payment->total );

		$payment->status = 'complete';
		$payment->save();

		$payment->remove_donation( $form->ID, array( 'price_id' => 0, 'item_price' => 10 ) );
		$payment->save();

		$this->assertEquals( 2, count( $payment->donations ) );

		$this->assertEquals( 0, $payment->donations[0]['options']['price_id'] );
		$this->assertEquals( 0, $payment->payment_details[1]['options']['price_id'] );
		$this->assertEquals( 10, $payment->payment_details[1]['item_price'] );

		$this->assertEquals( 0, $payment->donations[1]['options']['price_id'] );
		$this->assertEquals( 0, $payment->payment_details[2]['options']['price_id'] );
		$this->assertEquals( 10, $payment->payment_details[2]['item_price'] );

	}

	public function test_refund_affecting_stats() {
		$payment         = new Give_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$customer = new Give_Customer( $payment->customer_id );
		$form     = new Give_Donate_Form( $payment->donations[0]['id'] );

		$customer_sales    = $customer->purchase_count;
		$customer_earnings = $customer->purchase_value;

		$form_sales    = $form->sales;
		$form_earnings = $form->earnings;

		$site_earnings = give_get_total_earnings();
		$site_sales    = give_get_total_sales();

		$payment->refund();
		wp_cache_flush();

		$customer = new Give_Customer( $payment->customer_id );
		$form = new Give_Donate_Form( $payment->donations[0]['id'] );

		$this->assertEquals( $customer_earnings - $payment->total, $customer->purchase_value );
		$this->assertEquals( $customer_sales - 1, $customer->purchase_count );

		$this->assertEquals( $form_earnings - $payment->payment_details[0]['price'], $form->earnings );
		$this->assertEquals( $form_sales - $payment->donations[0]['quantity'], $form->sales );

		$this->assertEquals( $site_earnings - $payment->total, give_get_total_earnings() );
		$this->assertEquals( $site_sales - 1, give_get_total_sales() );
	}

	public function test_refund_without_affecting_stats() {

		add_filter( 'give_decrease_earnings_on_undo', '__return_false' );
		add_filter( 'give_decrease_sales_on_undo', '__return_false' );
		add_filter( 'give_decrease_customer_value_on_refund', '__return_false' );
		add_filter( 'give_decrease_customer_purchase_count_on_refund', '__return_false' );
		add_filter( 'give_decrease_store_earnings_on_refund', '__return_false' );

		$payment         = new Give_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$customer = new Give_Customer( $payment->customer_id );
		$form     = new Give_Donate_Form( $payment->donations[0]['id'] );

		$customer_sales    = $customer->purchase_count;
		$customer_earnings = $customer->purchase_value;

		$form_sales    = $form->sales;
		$form_earnings = $form->earnings;

		$site_earnings = give_get_total_earnings();
		$site_sales    = give_get_total_sales();

		$payment->refund();
		wp_cache_flush();

		$customer = new Give_Customer( $payment->customer_id );
		$form     = new Give_Donate_Form( $payment->donations[0]['id'] );

		$this->assertEquals( $customer_earnings, $customer->purchase_value );
		$this->assertEquals( $customer_sales, $customer->purchase_count );

		$this->assertEquals( $form_earnings, $form->earnings );
		$this->assertEquals( $form_sales, $form->sales );

		$this->assertEquals( $site_earnings, give_get_total_earnings() );
		// Store sales are based off 'publish' & 'revoked' status. So it reduces this count
		$this->assertEquals( $site_sales - 1, give_get_total_sales() );

		remove_filter( 'give_decrease_earnings_on_undo', '__return_false' );
		remove_filter( 'give_decrease_sales_on_undo', '__return_false' );
		remove_filter( 'give_decrease_customer_value_on_refund', '__return_false' );
		remove_filter( 'give_decrease_customer_purchase_count_on_refund', '__return_false' );
		remove_filter( 'give_decrease_store_earnings_on_refund', '__return_false ' );
	}

	public function test_pending_affecting_stats() {
		$payment         = new Give_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$customer = new Give_Customer( $payment->customer_id );
		$form     = new Give_Donate_Form( $payment->donations[0]['id'] );

		$customer_sales    = $customer->purchase_count;
		$customer_earnings = $customer->purchase_value;

		$form_sales    = $form->sales;
		$form_earnings = $form->earnings;

		$site_earnings = give_get_total_earnings();
		$site_sales    = give_get_total_sales();

		$payment->status = 'pending';
		$payment->save();
		wp_cache_flush();

		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->completed_date );

		$customer = new Give_Customer( $payment->customer_id );
		$form     = new Give_Donate_Form( $payment->donations[0]['id'] );

		$this->assertEquals( $customer_earnings - $payment->total, $customer->purchase_value );
		$this->assertEquals( $customer_sales - 1, $customer->purchase_count );

		$this->assertEquals( $form_earnings - $payment->payment_details[0]['price'], $form->earnings );
		$this->assertEquals( $form_sales - $payment->donations[0]['quantity'], $form->sales );

		$this->assertEquals( $site_earnings - $payment->total, give_get_total_earnings() );
		$this->assertEquals( $site_sales - 1, give_get_total_sales() );
	}

	public function test_pending_without_affecting_stats() {
		add_filter( 'give_decrease_earnings_on_undo', '__return_false' );
		add_filter( 'give_decrease_sales_on_undo', '__return_false' );
		add_filter( 'give_decrease_customer_value_on_pending', '__return_false' );
		add_filter( 'give_decrease_customer_purchase_count_on_pending', '__return_false' );
		add_filter( 'give_decrease_store_earnings_on_pending', '__return_false' );

		$payment         = new Give_Payment( $this->_payment_id );
		$payment->status = 'complete';
		$payment->save();

		$customer = new Give_Customer( $payment->customer_id );
		$form     = new Give_Donate_Form( $payment->donations[0]['id'] );

		$customer_sales    = $customer->purchase_count;
		$customer_earnings = $customer->purchase_value;

		$form_sales    = $form->sales;
		$form_earnings = $form->earnings;

		$site_earnings = give_get_total_earnings();
		$site_sales    = give_get_total_sales();

		$payment->status = 'pending';
		$payment->save();
		wp_cache_flush();

		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEmpty( $payment->completed_date );

		$customer = new Give_Customer( $payment->customer_id );
		$form     = new Give_Donate_Form( $payment->donations[0]['id'] );

		$this->assertEquals( $customer_earnings, $customer->purchase_value );
		$this->assertEquals( $customer_sales, $customer->purchase_count );

		$this->assertEquals( $form_earnings, $form->earnings );
		$this->assertEquals( $form_sales, $form->sales );

		$this->assertEquals( $site_earnings, give_get_total_earnings() );
		// Store sales are based off 'publish' & 'revoked' status. So it reduces this count
		$this->assertEquals( $site_sales - 1, give_get_total_sales() );

		remove_filter( 'give_decrease_earnings_on_undo', '__return_false' );
		remove_filter( 'give_decrease_sales_on_undo', '__return_false' );
		remove_filter( 'give_decrease_customer_value_on_pending', '__return_false' );
		remove_filter( 'give_decrease_customer_purchase_count_on_pending', '__return_false' );
		remove_filter( 'give_decrease_store_earnings_on_pending', '__return_false ' );
	}

}
