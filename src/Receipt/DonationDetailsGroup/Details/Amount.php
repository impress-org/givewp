<?php

namespace Give\Receipt\DonationDetailsGroup\Details;

use Give\Receipt\Detail;
use function give_get_payment_meta as getDonationMetaData;
use function give_format_amount as formatAmount;
use function give_currency_filter as filterCurrency;

/**
 * Class Amount
 *
 * @since 2.7.0
 * @package Give\Receipt\DonationDetailsGroup\Details
 */
class Amount extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return esc_html__( 'Donation Amount', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return filterCurrency(
			formatAmount( getDonationMetaData( $this->donationId, '_give_payment_total', true ), [ 'donation_id' => $this->donationId ] ),
			[
				'currency_code'   => getDonationMetaData( $this->donationId, '_give_payment_currency', true ),
				'decode_currency' => true,
				'form_id'         => getDonationMetaData( $this->donationId, '_give_payment_form_id', true ),
			]
		);
	}
}
