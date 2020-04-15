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
 * @param mixed  $data    Non null value. Key will be unset if value set to null
 * @param bool   $replace
 *
 * @return array|string
 */
function storeDataIntoSession( $key, $data, $replace = true ) {
	$session = getSession();

	if ( null === $data ) {
		unset( $session[ $key ] );

	} elseif ( $replace ) {
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
 * Remove data from donor session.
 *
 * @since 2.7.0
 * @param $key
 */
function removeDataFromSession( $key ) {
	storeDataIntoSession( $key, null );
}

/**
 * Store posted data to donor session to access it in iframe if we are on payment confirmation page.
 *
 * @since 2.7.0
 * @return bool
 */
function storePostedDataIntoSessionIfConfirmingDonation() {
	if ( isConfirmingDonation() ) {
		$paymentGatewayId = ucfirst( give_clean( $_GET['payment-confirmation'] ) );
		storeDataIntoSession( "postDataFor{$paymentGatewayId}", array_map( 'give_clean', $_POST ) );

		return true;
	}

	return false;
}


/**
 * Remove posted data from donor session just before rendering payment confirmation view because beyond this view this data is not useful.
 *
 * Note: This function is only for internal use and can be used only on payment confirmation view.
 *
 * @since 2.7.0
 * @return bool
 */
function removeDonationConfirmationPostedDataFromSession() {
	$paymentGatewayId = ucfirst( give_clean( $_GET['payment-confirmation'] ) );
	removeDataFromSession( "postDataFor{$paymentGatewayId}" );

	return false;
}
