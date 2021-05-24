<?php
namespace Give\Receipt;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use function give_get_payment_meta as getDonationMetaData;
use function give_get_gateway_admin_label as getGatewayLabel;
use function give_get_donation_donor_email as getDonationDonorEmail;
use function give_get_donation_address as getDonationDonorAddress;
use function give_format_amount as formatAmount;
use function give_currency_filter as filterCurrency;

class DonationReceipt extends Receipt {
	/**
	 * Receipt donor section id.
	 */
	const DONORSECTIONID = 'Donor';

	/**
	 * Receipt donation section id.
	 */
	const DONATIONSECTIONID = 'Donation';

	/**
	 * Receipt additional information section id.
	 */
	const ADDITIONALINFORMATIONSECTIONID = 'AdditionalInformation';

	/**
	 * Donation id.
	 *
	 * @since 2.7.0
	 * @var int $donationId
	 */
	public $donationId;

	/**
	 * Receipt constructor.
	 *
	 * @since 2.7.0
	 * @param $donationId
	 */
	public function __construct( $donationId ) {
		$this->donationId = $donationId;

		$this->addDonorSection();
		$this->addDonationSection();
		$this->addSection( $this->getAdditionInformationSection() ); // Additional Information Section
	}

	/**
	 * Add donor section.
	 *
	 * @since 2.7.0
	 */
	private function addDonorSection() {
		$billingAddressLineItem = $this->getDonorBillingAddressLineItem();
		$hasAddress             = (bool) trim( str_replace( ',', '', strip_tags( $billingAddressLineItem['value'] ) ) ); // Remove formatting from address.

		$section = $this->addSection( $this->getDonorSection() );
		$section->addLineItem( $this->getDonorNameLineItem() );
		$section->addLineItem( $this->getDonorEmailLineItem() );

		// Add billing address line item only if donor has billing address.
		if ( $hasAddress ) {
			$section->addLineItem( $this->getDonorBillingAddressLineItem() );
		}
	}

	/**
	 * Add donation section.
	 *
	 * @since 2.7.0
	 */
	private function addDonationSection() {
		$section = $this->addSection( $this->getDonationSection() );
		$section->addLineItem( $this->getDonationPaymentGatewayLineItem() );
		$section->addLineItem( $this->getDonationStatusLineItem() );
		$section->addLineItem( $this->getDonationAmountLineItem() );
		$section->addLineItem( $this->getDonationTotalAmountLineItem() );
	}

	/**
	 * Get donor section.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	private function getDonorSection() {
		return [
			'id'    => self::DONORSECTIONID,
			'label' => esc_html__( 'Donation Details', 'give' ),
		];
	}

	/**
	 * Get donor name line item.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	private function getDonorNameLineItem() {
		$firstName = getDonationMetaData( $this->donationId, '_give_donor_billing_first_name', true );
		$lastName  = getDonationMetaData( $this->donationId, '_give_donor_billing_last_name', true );

		return [
			'id'    => 'fullName',
			'label' => esc_html__( 'Donor Name', 'give' ),
			'value' => trim( "{$firstName} {$lastName}" ),
			'icon'  => '<i class="fas fa-user"></i>',
		];
	}

	/**
	 * Get donor email line item.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	private function getDonorEmailLineItem() {
		return [
			'id'    => 'emailAddress',
			'label' => esc_html__( 'Email Address', 'give' ),
			'value' => getDonationDonorEmail( $this->donationId ),
			'icon'  => '<i class="fas fa-envelope"></i>',
		];
	}

	/**
	 * Get donor address line item.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	private function getDonorBillingAddressLineItem() {
		$address = getDonationDonorAddress( $this->donationId );
		$address = sprintf(
			'%1$s<br>%2$s%3$s,%4$s%5$s<br>%6$s',
			$address['line1'],
			! empty( $address['line2'] ) ? $address['line2'] . '<br>' : '',
			$address['city'],
			$address['state'],
			$address['zip'],
			$address['country']
		);

		return [
			'id'    => 'billingAddress',
			'label' => esc_html__( 'Billing Address', 'give' ),
			'value' => $address,
			'icon'  => '<i class="fas fa-globe-americas"></i>',
		];
	}

	/**
	 * Get donor section.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	private function getDonationSection() {
		return [
			'id' => self::DONATIONSECTIONID,
		];
	}

	/**
	 * Get donation payment gateway line ite.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	private function getDonationPaymentGatewayLineItem() {
		return [
			'id'    => 'paymentMethod',
			'label' => esc_html__( 'Payment Method', 'give' ),
			'value' => getGatewayLabel( getDonationMetaData( $this->donationId, '_give_payment_gateway', true ) ),
		];
	}

	/**
	 * Get donation status line item.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	private function getDonationStatusLineItem() {
		return [
			'id'    => 'paymentStatus',
			'label' => esc_html__( 'Payment Status', 'give' ),
			'value' => give_get_payment_statuses()[ get_post_status( $this->donationId ) ],
		];
	}

	/**
	 * Get donation amount line item.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	private function getDonationAmountLineItem() {
		$value = filterCurrency(
			formatAmount( getDonationMetaData( $this->donationId, '_give_payment_total', true ), [ 'donation_id' => $this->donationId ] ),
			[
				'currency_code'   => getDonationMetaData( $this->donationId, '_give_payment_currency', true ),
				'decode_currency' => true,
				'form_id'         => getDonationMetaData( $this->donationId, '_give_payment_form_id', true ),
			]
		);

		return [
			'id'    => 'amount',
			'label' => esc_html__( 'Donation Amount', 'give' ),
			'value' => $value,
		];
	}

	/**
	 * Get donation total amount line item.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	private function getDonationTotalAmountLineItem() {
		$value = filterCurrency(
			formatAmount( getDonationMetaData( $this->donationId, '_give_payment_total', true ), [ 'donation_id' => $this->donationId ] ),
			[
				'currency_code'   => getDonationMetaData( $this->donationId, '_give_payment_currency', true ),
				'decode_currency' => true,
				'form_id'         => getDonationMetaData( $this->donationId, '_give_payment_form_id', true ),
			]
		);

		return [
			'id'    => 'totalAmount',
			'label' => esc_html__( 'Donation Total', 'give' ),
			'value' => $value,
		];
	}

	/**
	 * Get additional information section.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	private function getAdditionInformationSection() {
		return [
			'id'    => self::ADDITIONALINFORMATIONSECTIONID,
			'label' => esc_html__( 'Additional Information', 'give' ),
		];
	}

	/**
	 * Set iterator position to zero when rewind.
	 *
	 * @since 2.7.0
	 */
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * Validate section.
	 *
	 * @param array $array
	 * @since 2.7.0
	 */
	protected function validateSection( $array ) {
		$required = [ 'id' ];
		$array    = array_filter( $array ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException( esc_html__( 'Invalid receipt section. Please provide valid section id', 'give' ) );
		}
	}
}
