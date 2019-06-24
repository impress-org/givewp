<?php

/**
 * Class Test_Payments_Query
 */
class Test_Payments_Query extends Give_Unit_Test_Case {
	/**
	 * Donation status list
	 * @var array
	 */
	private $donation_statuses = array(
		'pending',
		'abandoned',
		'cancelled',
		'failed',
		'preapproval',
		'processing',
		'publish',
		'refunded',
		'revoked',
	);

	/**
	 * Set it up
	 *
	 * @since 2.5.1
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Tear it Down
	 * @since 2.5.1
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @cover Give_Payments_Query::get_payment_by_group
	 */
	public function test_get_payment_by_group() {
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
		foreach ( $this->donation_statuses as $donation_status ) {
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
			$this->assertContains( $donation_status, $this->donation_statuses );
			$this->assertInternalType( 'array', $value );
		}
	}
}
