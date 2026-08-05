<?php


/**
 * @group give_actions
 */
class Tests_Actions extends Give_Unit_Test_Case {

	/**
	 * Set up tests.
	 */
	public function setUp(): void {

		parent::setUp();

		wp_set_current_user( 0 );

	}

	/**
	 * A standalone registration must not auto-link an unclaimed donor.
	 *
	 * @since TBD
	 */
	public function test_connect_donor_to_wpuser_does_not_link_without_checkout_flag() {

		$donor_id = Give()->donors->add(
			[
				'name'    => 'Unclaimed Donor',
				'email'   => 'unclaimed-donor@example.org',
				'user_id' => 0,
			]
		);

		$user_id = $this->factory->user->create( [ 'user_email' => 'unclaimed-donor@example.org' ] );

		give_connect_donor_to_wpuser(
			$user_id,
			[
				'user_email' => 'unclaimed-donor@example.org',
			]
		);

		$donor = new Give_Donor( $donor_id );
		$this->assertEquals( 0, (int) $donor->user_id );

	}

	/**
	 * The checkout-flow flag must still allow the auto-link.
	 *
	 * @since TBD
	 */
	public function test_connect_donor_to_wpuser_links_with_checkout_flag() {

		$donor_id = Give()->donors->add(
			[
				'name'    => 'Checkout Donor',
				'email'   => 'checkout-donor@example.org',
				'user_id' => 0,
			]
		);

		$user_id = $this->factory->user->create( [ 'user_email' => 'checkout-donor@example.org' ] );

		give_connect_donor_to_wpuser(
			$user_id,
			[
				'user_email'                          => 'checkout-donor@example.org',
				'give_donation_checkout_registration' => true,
			]
		);

		$donor = new Give_Donor( $donor_id );
		$this->assertEquals( $user_id, (int) $donor->user_id );

	}

	/**
	 * An already-linked donor must never be reassigned, even with the checkout flag present.
	 *
	 * @since TBD
	 */
	public function test_connect_donor_to_wpuser_does_not_relink_already_claimed_donor() {

		$existing_owner_id = $this->factory->user->create();

		$donor_id = Give()->donors->add(
			[
				'name'    => 'Already Linked Donor',
				'email'   => 'already-linked-donor@example.org',
				'user_id' => $existing_owner_id,
			]
		);

		$new_user_id = $this->factory->user->create( [ 'user_email' => 'already-linked-donor@example.org' ] );

		give_connect_donor_to_wpuser(
			$new_user_id,
			[
				'user_email'                          => 'already-linked-donor@example.org',
				'give_donation_checkout_registration' => true,
			]
		);

		$donor = new Give_Donor( $donor_id );
		$this->assertEquals( $existing_owner_id, (int) $donor->user_id );

	}

}
