<?php
namespace Give\Helper\Donor;

use function Give\Helpers\Form\Utils\isConfirmingDonation;

/**
 * Get donor session.
 *
 * @return array|string
 * @since 2.7.0
 */
function getSession() {
	return Give()->session->get( 'give_purchase' );
}

/**
 * Store data to donor session.
 *
 * @since 2.7.0
 * @param string $key
 * @param mixed  $data
 * @param bool   $replace
 *
 * @return array|string
 */
function storeDataIntoSession( $key, $data, $replace = true ) {
	$session = getSession();

	if ( $replace ) {
		// Replace data.
		$session[ $key ] = $data;

	} elseif ( ! empty( $session[ $key ] ) && is_array( $data ) ) {
		// Merge data.
		$session[ $key ] = array_merge(
			(array) $session[ $key ],
			(array) $data
		);

	} else {
		$session[ $key ] = $data;
	}

	return Give()->session->set( 'give_purchase', $session );
}

/**
 * Store posted data to donor session to access it in iframe if we are on payment confirmation page.
 *
 * @since 2.7.0
 * @return bool
 */
function storePostedDataIntoSessionIfConfirmingDonation() {
	if ( isConfirmingDonation() ) {
		$paymentGatewayId = give_clean( $_GET['payment-confirmation'] );
		storeDataIntoSession( $paymentGatewayId, array_map( 'give_clean', $_POST ) );

		return true;
	}

	return false;
}
