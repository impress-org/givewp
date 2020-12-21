<?php

namespace Give\TestData\Addons\RecurringDonations;

use Exception;

/**
 * Class RecurringDonationRepository
 * @package Give\TestData\RecurringDonations
 */
class RecurringDonationRepository {
	/**
	 * @var RecurringDonationFactory
	 */
	private $donationFactory;

	/**
	 * RecurringDonationRepository constructor.
	 *
	 * @param RecurringDonationFactory $donationFactory
	 */
	public function __construct( RecurringDonationFactory $donationFactory ) {
		$this->donationFactory = $donationFactory;
	}

	/**
	 * @param array $donation
	 */
	public function insertDonation( $donation ) {
		global $wpdb;

		$donation = wp_parse_args(
			apply_filters( 'give-test-data-recurring-donation-definition', $donation ),
			$this->donationFactory->definition()
		);

		// Insert donation
		$wpdb->insert(
			"{$wpdb->prefix}give_subscriptions",
			[
				'customer_id'       => $donation['customer_id'],
				'period'            => $donation['period'],
				'frequency'         => 1,
				'initial_amount'    => $donation['initial_amount'],
				'recurring_amount'  => $donation['recurring_amount'],
				'parent_payment_id' => $donation['parent_payment_id'],
				'product_id'        => $donation['product_id'],
				'created'           => $donation['created'],
				'expiration'        => $donation['expiration'],
				'status'            => $donation['status'],
			]
		);

		do_action( 'give-test-data-insert-recurring-donation', $wpdb->insert_id, $donation );
	}
}
