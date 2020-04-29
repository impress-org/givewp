<?php

namespace Give\Helpers\Frontend;

use Give\Session\DonationSessionAccess;
use function Give\Helpers\Form\Utils\isViewingFormReceipt;

/**
 * Class ConfirmDonation
 *
 * @package Give\Helpers\Frontend
 */
class ConfirmDonation {
	/**
	 * Store posted data to donation session to access it in iframe if we are on payment confirmation page.
	 * This function will return true if data stored successfully in purchase session (session key name "give_purchase" ) otherwise false.
	 *
	 * Note: only for internal use.
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public static function storePostedDataInDonationSession() {
		$isShowingDonationReceipt = ! empty( $_REQUEST['giveDonationAction'] ) && 'showReceipt' === give_clean( $_REQUEST['giveDonationAction'] );

		if ( $isShowingDonationReceipt ) {
			$paymentGatewayId = ucfirst( give_clean( $_GET['payment-confirmation'] ) );

			$session = new DonationSessionAccess();
			$session->store( "postDataFor{$paymentGatewayId}", array_map( 'give_clean', $_POST ) );

			return true;
		}

		return false;
	}

	/**
	 * Remove posted data from donation session just before rendering payment confirmation view because beyond this view this data is not useful.
	 *
	 * Note: Only for internal use.
	 *
	 * @since 2.7.0
	 */
	public static function removePostedDataFromDonationSession() {
		$paymentGatewayId = ucfirst( give_clean( $_GET['payment-confirmation'] ) );

		$session = new DonationSessionAccess();
		$session->delete( "postDataFor{$paymentGatewayId}" );
	}

	/**
	 * Return whether or not we are viewing donation confirmation view or not.
	 *
	 * @since 2.7.0
	 * @return bool
	 */
	public static function isViewingPage() {
		return isViewingFormReceipt() && isset( $_GET['payment-confirmation'] );
	}
}
