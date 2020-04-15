<?php
namespace Give\Helper\Session\DonationConfirmation;

use function Give\Helper\Session\removeDataFromSession;
use function Give\Helper\Session\storeDataIntoSession;
use function Give\Helpers\Form\Utils\isConfirmingDonation;

/**
 * Store posted data to donor session to access it in iframe if we are on payment confirmation page.
 *
 * @return bool
 * @since 2.7.0
 */
function storePostedDataIntoSession() {
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
function removeDonationConfirmationPostedData() {
	$paymentGatewayId = ucfirst( give_clean( $_GET['payment-confirmation'] ) );
	removeDataFromSession( "postDataFor{$paymentGatewayId}" );

	return false;
}
