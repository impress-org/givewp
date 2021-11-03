<?php

namespace Give\Views\Form\Templates\Classic;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Receipt\DonationReceipt as GiveDonationReceipt;
use Give\Session\SessionDonation\DonationAccessor;
use Give_Payment as Donation;

class DonationReceipt extends GiveDonationReceipt {
	/**
	 * @var Donation
	 */
	private $donation;

	/**
	 * @var int
	 */
	public $donationId;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->donation   = new Donation( ( new DonationAccessor() )->getDonationId() );
		$this->donationId = $this->donation->ID;

		// Register sections
		$this->registerDonorSection();
		$this->registerDonationSection();
		$this->registerAdditionalInformationSection();

		/**
		 * Fire the action for receipt object.
		 */
		do_action( 'give_new_receipt', $this );
	}

	/**
	 * Replace template tags in given string
	 *
	 * @param  string  $string
	 *
	 * @return string
	 */
	public function replaceTags( $string ) {
		$tags = [
			'{name}'        => sprintf( '%s %s', $this->donation->first_name, $this->donation->last_name ),
			'{donor_email}' => sprintf( '<br /><strong>%s</strong>', $this->donation->email )
		];

		return str_replace(
			array_keys( $tags ),
			array_values( $tags ),
			$string
		);
	}

	/**
	 *  Register Donor Section
	 */
	private function registerDonorSection() {
		$donorSection = $this->addSection( [
			'id'    => parent::DONORSECTIONID,
			'label' => esc_html__( 'Donor Details', 'give' ),
		] );

		$donorSection->addLineItem( [
			'id'    => 'fullName',
			'label' => esc_html__( 'Donor Name', 'give' ),
			'value' => trim( "{$this->donation->first_name} {$this->donation->last_name}" ),
		] );

		$donorSection->addLineItem( [
			'id'    => 'emailAddress',
			'label' => esc_html__( 'Email Address', 'give' ),
			'value' => $this->donation->email,
		] );

		if ( $address = $this->getDonorBillingAddress() ) {
			$donorSection->addLineItem( [
				'id'    => 'billingAddress',
				'label' => esc_html__( 'Billing Address', 'give' ),
				'value' => $address,
			] );
		}
	}

	/**
	 *  Register Donation Section
	 */
	private function registerDonationSection() {
		$donationSection = $this->addSection( [
			'id'    => parent::DONATIONSECTIONID,
			'label' => esc_html__( 'Donation Details', 'give' ),
		] );

		$donationSection->addLineItem( [
			'id'    => 'paymentMethod',
			'label' => esc_html__( 'Payment Method', 'give' ),
			'value' => give_get_gateway_checkout_label( $this->donation->gateway ),
		] );

		$donationSection->addLineItem( [
			'id'    => 'paymentStatus',
			'label' => esc_html__( 'Payment Status', 'give' ),
			'value' => give_get_payment_statuses()[ $this->donation->post_status ],
		] );

		$donationSection->addLineItem( [
			'id'    => 'amount',
			'label' => esc_html__( 'Donation Amount', 'give' ),
			'value' => give_currency_filter(
				give_format_amount( $this->donation->total, [ 'donation_id' => $this->donation->ID ] ),
				[
					'currency_code'   => $this->donation->currency,
					'form_id'         => $this->donation->form_id,
					'decode_currency' => true,
				]
			),
		] );

		$donationSection->addLineItem( [
			'id'    => 'totalAmount',
			'label' => esc_html__( 'Donation Total', 'give' ),
			'value' => give_currency_filter(
				give_format_amount( $this->donation->total, [ 'donation_id' => $this->donation->ID ] ),
				[
					'currency_code'   => $this->donation->currency,
					'form_id'         => $this->donation->form_id,
					'decode_currency' => true,
				]
			),
		] );
	}

	/**
	 *  Register Additional Information Section
	 */
	private function registerAdditionalInformationSection() {
		$this->addSection( [
			'id'    => parent::ADDITIONALINFORMATIONSECTIONID,
			'label' => esc_html__( 'Additional Information', 'give' ),
		] );
	}

	/**
	 * Get donor billing address
	 * Copied from Give\Receipt\DonationReceipt
	 *
	 * @return string|null
	 */
	private function getDonorBillingAddress() {
		$address   = give_get_donation_address( $this->donationId );
		$formatted = sprintf(
			'%1$s%7$s%2$s%3$s, %4$s%5$s%7$s%6$s',
			$address[ 'line1' ],
			! empty( $address[ 'line2' ] ) ? $address[ 'line2' ] . "\r\n" : '',
			$address[ 'city' ],
			$address[ 'state' ],
			$address[ 'zip' ],
			$address[ 'country' ],
			"\r\n"
		);

		$hasAddress = (bool) trim( str_replace( ',', '', strip_tags( $formatted ) ) );

		if ( $hasAddress ) {
			return $formatted;
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * Validate section.
	 *
	 * @param  array  $array
	 */
	protected function validateSection( $array ) {
		$array = array_filter( $array ); // Remove empty values.

		if ( array_diff( [ 'id' ], array_keys( $array ) ) ) {
			throw new InvalidArgumentException(
				esc_html__( 'Invalid receipt section. Please provide valid section id', 'give' )
			);
		}
	}
}
