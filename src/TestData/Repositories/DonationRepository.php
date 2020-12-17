<?php

namespace Give\TestData\Repositories;

use Give_Donor;
use Give\ValueObjects\Money;
use Give\TestData\Framework\MetaRepository;
use Give\TestData\Factories\DonationFactory;

class DonationRepository {
	/**
	 * @var DonationFactory
	 */
	private $donationFactory;
	/**
	 * @var RevenueRepository
	 */
	private $revenueRepository;

	/**
	 * @param  DonationFactory  $donationFactory
	 * @param  RevenueRepository  $revenueRepository
	 */
	public function __construct( DonationFactory $donationFactory, RevenueRepository $revenueRepository ) {
		$this->donationFactory   = $donationFactory;
		$this->revenueRepository = $revenueRepository;
	}

	/**
	 * @param  array  $donation
	 * @param  array  $params
	 */
	public function insertDonation( $donation, $params ) {
		global $wpdb;

		$donation = wp_parse_args(
			apply_filters( 'give-test-data-donation-definition', $donation, $params ),
			$this->donationFactory->definition()
		);

		// Insert donation
		$wpdb->insert(
			"{$wpdb->prefix}posts",
			[
				'post_type'   => 'give_payment',
				'post_date'   => $donation['completed_date'],
				'post_status' => $donation['payment_status'],
			]
		);

		$donationID = $wpdb->insert_id;

		// Insert donation in revenue table
		$this->revenueRepository->insertRevenue(
			[
				'donation_id' => $donationID,
				'form_id'     => $donation['payment_form_id'],
				'amount'      => Money::of( $donation['payment_total'], $donation['payment_currency'] )->getMinorAmount(),
			]
		);

		$metaRepository = new MetaRepository( 'give_donationmeta', 'donation_id' );

		$metaRepository->persist(
			$donationID,
			[
				'_give_payment_form_id'          => $donation['payment_form_id'],
				'_give_payment_form_title'       => $donation['payment_form_title'],
				'_give_payment_donor_id'         => $donation['donor_id'],
				'_give_payment_total'            => $donation['payment_total'],
				'_give_payment_currency'         => $donation['payment_currency'],
				'_give_payment_gateway'          => $donation['payment_gateway'],
				'_give_payment_mode'             => $donation['payment_mode'],
				'_give_payment_purchase_key'     => $donation['payment_purchase_key'],
				'_give_completed_date'           => $donation['completed_date'],
				'_give_donor_billing_first_name' => $donation['donor_name'],
				'_give_donor_billing_last_name'  => '',
				'_give_payment_donor_email'      => $donation['donor_email'],
			]
		);

		// Increase donor donated amount and count
		$donor = new Give_Donor( $donation['donor_id'] );
		$donor->increase_value( floatval( $donation['payment_total'] ) );
		$donor->increase_purchase_count();

		do_action( 'give-test-data-insert-donation', $donationID, $donation, $params );
	}
}
