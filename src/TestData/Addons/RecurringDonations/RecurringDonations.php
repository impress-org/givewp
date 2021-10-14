<?php

namespace Give\TestData\Addons\RecurringDonations;

use Exception;
use Give\TestData\Framework\MetaRepository;

/**
 * Class RecurringDonations
 * @package Give\TestData\RecurringDonations
 */
class RecurringDonations {
	/**
	 * @var RecurringDonationFactory
	 */
	private $donationFactory;
	/**
	 * @var RecurringDonationRepository
	 */
	private $donationRepository;

	public function __construct(
		RecurringDonationFactory $donationFactory,
		RecurringDonationRepository $donationRepository
	) {
		$this->donationFactory    = $donationFactory;
		$this->donationRepository = $donationRepository;
	}

	/**
	 * @return string
	 */
	public function getRecurringDonationStatus() {
		return 'give_subscription';
	}


	/**
	 * @param int $donationID
	 * @param array $donation
	 */
	public function insertRecurringDonation( $donationID, $donation ) {
		global $wpdb;

		// Check if donation status is recurring donation status
		if ( $donation['payment_status'] !== $this->getRecurringDonationStatus() ) {
			return;
		}

		// Factory config
		$this->donationFactory->setAmount( $donation['payment_total'] );
		$this->donationFactory->setCustomerId( $donation['donor_id'] );
		$this->donationFactory->setParentDonationId( $donationID );
		$this->donationFactory->setProductId( $donation['payment_form_id'] );

		// Start DB transaction
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Insert recurring donation
			$this->donationRepository->insertDonation(
				$this->donationFactory->definition()
			);

			// Update donation meta
			$metaRepository = new MetaRepository( 'give_donationmeta', 'donation_id' );
			$metaRepository->persist(
				$donationID,
				[
					'_give_subscription_payment'  => 1,
					'_give_is_donation_recurring' => 1,
				]
			);

			$wpdb->query( 'COMMIT' );

		} catch ( Exception $e ) {
			$wpdb->query( 'ROLLBACK' );
		}
	}

}
