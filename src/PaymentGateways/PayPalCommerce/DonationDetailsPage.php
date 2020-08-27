<?php
namespace Give\PaymentGateways\PayPalCommerce;


class DonationDetailsPage {
	/**
	 * Return PayPal Commerce payment details page url.
	 *
	 * @since 2.9.0
	 *
	 * @param string $transactionId donation transaction id.
	 *
	 * @return string
	 */
	public function getPayPalPaymentUrl( $transactionId ) {
		return sprintf(
			'<a href="%1$sactivity/payment/%2$s" title="%3$s" target="_blank">%2$s</a>',
			esc_url( give( PayPalClient::class )->getHomePageUrl() ),
			esc_attr( $transactionId ),
			esc_attr( esc_html__( 'View PayPal Commerce payment', 'give' ) )
		);
	}
}
