<?php
namespace Give\Receipt;

use Give\Helpers\ArrayDataSet;
use stdClass;
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
	 * Receipt additonal information section id.
	 */
	const ADDITIONALINFORMATIONSECTIONID = 'AdditionalInformation';

	/**
	 * Donation id.
	 *
	 * @since 2.7.0
	 * @var int $donationId
	 */
	protected $donationId;

	/**
	 * Receipt constructor.
	 *
	 * @since 2.7.0
	 * @param $donationId
	 */
	public function __construct( $donationId ) {
		$this->donationId = $donationId;

		/* Donor Section */
		$this->addSection( $this->getDonorSection() );
		$this->addLineItem( self::DONORSECTIONID, $this->getDonorNameLineItem() );
		$this->addLineItem( self::DONORSECTIONID, $this->getDonorEmailLineItem() );
		$this->addLineItem( self::DONORSECTIONID, $this->getDonorBillingAddressLineItem() );

		/* Donation Section */
		$this->addSection( $this->getDonationSection() );
		$this->addLineItem( self::DONATIONSECTIONID, $this->getDonationPaymentGatewayLineItem() );
		$this->addLineItem( self::DONATIONSECTIONID, $this->getDonationStatusLineItem() );
		$this->addLineItem( self::DONATIONSECTIONID, $this->getDonationAmountLineItem() );
		$this->addLineItem( self::DONATIONSECTIONID, $this->getDonationTotalAmountLineItem() );

		/* Additional Information Section */
		$this->addSection( $this->getAdditionInformationSection() );
	}

	/**
	 * Get receipt sections.
	 *
	 * @return stdClass[]
	 * @since 2.7.0
	 */
	public function getSections() {
		$sections = $this->sectionList;

		// Filter sections which does not have lineItems.
		foreach ( $sections as $id => $value ) {
			if ( ! array_key_exists( 'lineItems', $value ) ) {
				unset( $sections[ $id ] );
			}
		}

		return ArrayDataSet::convertToObject( $sections );
	}

	/**
	 * Add detail group.
	 *
	 * @param  array $section
	 *
	 * @since 2.7.0
	 */
	public function addSection( $section ) {
		$this->validateSection( $section );

		// Add default label.
		$section = wp_parse_args(
			$section,
			[ 'label' => '' ]
		);

		$this->sectionList[ $section['id'] ] = $section;
	}

	/**
	 * Add detail group.
	 *
	 * @param  string $sectionId
	 * @param  array  $listItem
	 *
	 * @since 2.7.0
	 */
	public function addLineItem( $sectionId, $listItem ) {
		$this->validateLineItem( $listItem );

		// Add default icon.
		$listItem = wp_parse_args(
			$listItem,
			[ 'icon' => '' ]
		);

		$this->sectionList[ $sectionId ]['lineItems'][ $listItem['id'] ] = $listItem;
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
			'icon'  => '<i class="fas fa-envelope"></i>',
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
	 * Get donation status line ite.
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
	 * Get donation amount line ite.
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
	 * Get donation total amount line ite.
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
}
