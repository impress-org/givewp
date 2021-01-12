<?php

/**
 * Class Test_Payments_Query
 */
class Test_Payments_Query extends Give_Unit_Test_Case {
	private $_payment_id = 0;

	/**
	 * Set it up
	 *
	 * @since 2.5.1
	 */
	public function setUp() {
		parent::setUp();

		$this->_payment_id = Give_Helper_Payment::create_simple_payment();
	}

	/**
	 * Tear it Down
	 *
	 * @since 2.5.1
	 */
	public function tearDown() {
		parent::tearDown();

		Give_Helper_Payment::delete_payment( $this->_payment_id );
	}

	/**
	 * @cover Give_Payments_Query::get_payment_by_group
	 */
	public function test_get_payment_by_group() {
		$donation_statuses = give_get_payment_status_keys();

		/*
		 * Case 1
		 */
		$query1 = array(
			'group_by' => 'post_type',
		);

		$payment1 = new Give_Payments_Query( $query1 );
		$result   = $payment1->get_payment_by_group();
		$this->assertEquals( array(), $result );

		/*
		 * Case 2
		 */
		$query2 = array(
			'group_by' => 'post_status',
			'count'    => true,
		);

		$payment2 = new Give_Payments_Query( $query2 );
		$result   = $payment2->get_payment_by_group();
		foreach ( $donation_statuses as $donation_status ) {
			$this->assertArrayHasKey( $donation_status, $result );
		}

		/*
		 * Case 3
		 */
		$query3 = array(
			'group_by' => 'post_status',
		);

		$payment3 = new Give_Payments_Query( $query3 );
		$result   = $payment3->get_payment_by_group();
		foreach ( $result as $donation_status => $value ) {
			$this->assertContains( $donation_status, $donation_statuses );
			$this->assertInternalType( 'array', $value );
		}
	}


	/**
	 * @cover Give_Payments_Query::set_filters
	 */
	public function test_set_filters() {
		/**
		 * Case 1
		 * Default query arguments
		 */
		$default_args = array(
			'output'                 => 'payments',
			'post_type'              => array( 'give_payment' ),
			'start_date'             => false,
			'end_date'               => false,
			'page'                   => null,
			'orderby'                => 'ID',
			'order'                  => 'DESC',
			'user'                   => null,
			'donor'                  => null,
			'meta_key'               => null,
			'year'                   => null,
			'month'                  => null,
			'day'                    => null,
			's'                      => null,
			'search_in_notes'        => false,
			'fields'                 => null,
			'gateway'                => null,
			'give_forms'             => null,
			'offset'                 => null,
			'group_by'               => '',
			'count'                  => false,
			'update_post_meta_cache' => false,
			'post_status'            => give_get_payment_status_keys(),
			'posts_per_page'         => 20,
			'post_parent'            => 0,
		);

		$payment = new Give_Payments_Query( array() );
		$payment->get_payments();

		$this->assertEquals( serialize( $default_args ), serialize( $payment->args ) );

		/**
		 * Case 2a
		 * meta query
		 */
		$query1 = array(
			'give_forms' => 44,
		);

		$default_args1               = $default_args;
		$default_args1['meta_query'] = array(
			array(
				'key'     => '_give_payment_form_id',
				'value'   => 44,
				'compare' => '=',
			),
		);

		unset( $default_args1['give_forms'] );

		$payment1 = new Give_Payments_Query( $query1 );
		$payment1->get_payments();

		$this->assertEquals( serialize( $default_args1 ), serialize( $payment1->args ) );

		/**
		 * Case 2b
		 * meta query
		 */
		$query2 = array(
			'give_forms' => array( 44, 33 ),
		);

		$default_args2               = $default_args;
		$default_args2['meta_query'] = array(
			array(
				'key'     => '_give_payment_form_id',
				'value'   => array( 44, 33 ),
				'compare' => 'IN',
			),
		);

		unset( $default_args2['give_forms'] );

		$payment2 = new Give_Payments_Query( $query2 );
		$payment2->get_payments();

		$this->assertEquals( serialize( $default_args2 ), serialize( $payment2->args ) );

		/**
		 * Case 2c
		 * meta query
		 */
		$query3 = array(
			'gateway' => 'paypal',
		);

		$default_args3               = $default_args;
		$default_args3['meta_query'] = array(
			array(
				'key'     => '_give_payment_gateway',
				'value'   => 'paypal',
				'compare' => '=',
			),
		);

		unset( $default_args3['gateway'] );

		$payment3 = new Give_Payments_Query( $query3 );
		$payment3->get_payments();

		$this->assertEquals( serialize( $default_args3 ), serialize( $payment3->args ) );

		/**
		 * Case 2d
		 * meta query
		 */
		$query4 = array(
			'gateway' => array( 'paypal', 'stripe' ),
		);

		$default_args4               = $default_args;
		$default_args4['meta_query'] = array(
			array(
				'key'     => '_give_payment_gateway',
				'value'   => array( 'paypal', 'stripe' ),
				'compare' => 'IN',
			),
		);

		unset( $default_args4['gateway'] );

		$payment4 = new Give_Payments_Query( $query4 );
		$payment4->get_payments();

		$this->assertEquals( serialize( $default_args4 ), serialize( $payment4->args ) );

		/**
		 * Case 2e
		 * meta query
		 */
		$query5 = array(
			'donor' => 11,
		);

		$default_args5               = $default_args;
		$default_args5['donor']      = $query5['donor'];
		$default_args5['meta_query'] = array(
			array(
				'key'   => '_give_payment_donor_id',
				'value' => $default_args5['donor'],
			),
		);

		// reorder post_parent to pass test.
		$tmp = $default_args5['post_parent'];
		unset( $default_args5['post_parent'] );
		$default_args5['post_parent'] = $tmp;

		$payment5 = new Give_Payments_Query( $query5 );
		$payment5->get_payments();

		$this->assertEquals( serialize( $default_args5 ), serialize( $payment5->args ) );

		/**
		 * Case 2f
		 * meta query
		 */
		$query6 = array(
			'donor' => array( 11, 12 ),
		);

		$default_args6          = $default_args;
		$default_args6['donor'] = $query6['donor'];

		$payment6 = new Give_Payments_Query( $query6 );
		$payment6->get_payments();

		$this->assertEquals( serialize( $default_args6 ), serialize( $payment6->args ) );

		/**
		 * Case 2g
		 * meta query
		 */
		$query7 = array(
			'user' => 11,
		);

		$default_args7               = $default_args;
		$default_args7['user']       = $query7['user'];
		$default_args7['meta_query'] = array(
			array(
				'key'   => '_give_payment_donor_id',
				'value' => -1,
			),
		);

		// reorder post_parent to pass test.
		$tmp = $default_args7['post_parent'];
		unset( $default_args7['post_parent'] );
		$default_args7['post_parent'] = $tmp;

		$payment7 = new Give_Payments_Query( $query7 );
		$payment7->get_payments();

		$this->assertEquals( serialize( $default_args7 ), serialize( $payment7->args ) );

		/**
		 * Case 2h
		 * meta query
		 */
		$query8 = array(
			'donor'      => 11,
			'give_forms' => 12,
			'gateway'    => 'paypal',
		);

		$default_args8               = $default_args;
		$default_args8['donor']      = $query8['donor'];
		$default_args8['meta_query'] = array(
			array(
				'key'   => '_give_payment_donor_id',
				'value' => 11,
			),
			array(
				'key'     => '_give_payment_form_id',
				'value'   => 12,
				'compare' => '=',
			),
			array(
				'key'     => '_give_payment_gateway',
				'value'   => 'paypal',
				'compare' => '=',
			),
		);

		// reorder post_parent to pass test.
		$tmp = $default_args8['post_parent'];
		unset( $default_args8['post_parent'], $default_args8['give_forms'], $default_args8['gateway'] );
		$default_args8['post_parent'] = $tmp;

		$payment8 = new Give_Payments_Query( $query8 );
		$payment8->get_payments();

		$this->assertEquals( serialize( $default_args8 ), serialize( $payment8->args ) );

		/**
		 * Case 2i
		 * meta query
		 */
		$query9 = array(
			'mode' => 'test',
		);

		$default_args9               = $default_args;
		$default_args9['meta_query'] = array(
			array(
				'key'   => '_give_payment_mode',
				'value' => $query9['mode'],
			),
		);

		$default_args9 = give_array_insert_after( 'count', $default_args9, 'mode', $query9['mode'] );

		// reorder post_parent to pass test.
		$tmp = $default_args9['post_parent'];
		unset( $default_args9['post_parent'] );
		$default_args9['post_parent'] = $tmp;

		$payment9 = new Give_Payments_Query( $query9 );
		$payment9->get_payments();

		$this->assertEquals( serialize( $default_args9 ), serialize( $payment9->args ) );

		/**
		 * Case 2j
		 * meta query
		 */
		$query10 = array(
			'user' => 'test@gmail.com',
		);

		$default_args10               = $default_args;
		$default_args10['user']       = $query10['user'];
		$default_args10['meta_query'] = array(
			array(
				'key'   => '_give_payment_donor_email',
				'value' => $default_args10['user'],
			),
		);

		// reorder post_parent to pass test.
		$tmp = $default_args10['post_parent'];
		unset( $default_args10['post_parent'] );
		$default_args10['post_parent'] = $tmp;

		$payment10 = new Give_Payments_Query( $query10 );
		$payment10->get_payments();

		$this->assertEquals( serialize( $default_args10 ), serialize( $payment10->args ) );

		/**
		 * Case 3
		 */
		$query11 = array(
			'status' => 'pending',
		);

		$default_args11                = $default_args;
		$default_args11['post_status'] = $query11['status'];

		unset( $default_args11['status'] );

		$payment11 = new Give_Payments_Query( $query11 );
		$payment11->get_payments();

		$this->assertEquals( serialize( $default_args11 ), serialize( $payment11->args ) );

		/**
		 * Case 4a
		 */
		$query12 = array(
			'orderby' => 'amount',
		);

		$default_args12             = $default_args;
		$default_args12['orderby']  = 'meta_value_num';
		$default_args12['meta_key'] = '_give_payment_total';

		$payment12 = new Give_Payments_Query( $query12 );
		$payment12->get_payments();

		$this->assertEquals( serialize( $default_args12 ), serialize( $payment12->args ) );

		/**
		 * Case 4b
		 */
		$query13 = array(
			'orderby' => 'donation_form',
		);

		$default_args13             = $default_args;
		$default_args13['orderby']  = 'meta_value';
		$default_args13['meta_key'] = '_give_payment_form_title';

		$payment13 = new Give_Payments_Query( $query13 );
		$payment13->get_payments();

		$this->assertEquals( serialize( $default_args13 ), serialize( $payment13->args ) );

		/**
		 * Case 4c
		 */
		$query14 = array(
			'orderby' => 'ID',
		);

		$default_args14            = $default_args;
		$default_args14['orderby'] = $query14['orderby'];

		$payment14 = new Give_Payments_Query( $query14 );
		$payment14->get_payments();

		$this->assertEquals( serialize( $default_args14 ), serialize( $payment14->args ) );

		/**
		 * Case 5a
		 */
		$query15 = array(
			'post_parent' => 12,
			'children'    => true,
		);

		$default_args15 = $default_args;
		unset( $default_args15['children'], $default_args15['post_parent'] );

		$default_args15 = give_array_insert_after( 'count', $default_args15, 'post_parent', $query15['post_parent'] );

		$payment15 = new Give_Payments_Query( $query15 );
		$payment15->get_payments();

		$this->assertEquals( serialize( $default_args15 ), serialize( $payment15->args ) );

		/**
		 * Case 5b
		 */
		$query16 = array(
			'post_parent' => 12,
		);

		$default_args16 = $default_args;
		unset( $default_args16['children'], $default_args16['post_parent'] );

		$default_args16 = give_array_insert_after( 'count', $default_args16, 'post_parent', 0 );

		$payment16 = new Give_Payments_Query( $query16 );
		$payment16->get_payments();

		$this->assertEquals( serialize( $default_args16 ), serialize( $payment16->args ) );
	}

	/**
	 * @cover Give_Payments_Query::get_payments
	 */
	function test_get_payments() {
		/*
		 * Case 1
		 */
		$payment = new Give_Payments_Query();
		$result  = $payment->get_payments();

		$this->assertInstanceOf( 'Give_Payment', current( $result ) );

		/*
		 * Case 2
		 */
		$payment = new Give_Payments_Query( array( 'output' => '' ) );
		$result  = $payment->get_payments();

		$this->assertInstanceOf( 'WP_Post', current( $result ) );
	}
}
